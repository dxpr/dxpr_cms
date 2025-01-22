<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Render\Element\Password;
use Drupal\Component\Utility\Random;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Installer\Form\SiteConfigureForm;
use Drupal\Core\Installer\Form\SiteSettingsForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Recipe\InputCollector;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\dxpr_cms_installer\Form\RecipesForm;
use Drupal\dxpr_cms_installer\Form\SiteNameForm;
use Drupal\dxpr_cms_installer\Form\ConfigureAPIKeysForm;
use Symfony\Component\Process\ExecutableFinder;
use Drupal\dxpr_cms_installer\MessageInterceptor;
use Drupal\dxpr_cms_installer\RecipeLoader;

const SQLITE_DRIVER = 'Drupal\sqlite\Driver\Database\sqlite';

/**
 * Implements hook_install_tasks().
 */
function dxpr_cms_installer_install_tasks(): array {
  // Ensure our forms are loadable in all situations, even if the installer is
  // not a Composer-managed package.
  \Drupal::service('class_loader')
    ->addPsr4('Drupal\\dxpr_cms_installer\\', __DIR__ . '/src');

  // If the container can be altered, wrap the messenger service to suppress
  // certain messages.
  $container = \Drupal::getContainer();
#   if ($container instanceof ContainerBuilder) {
#     $container->set('messenger', new MessageInterceptor(
#       \Drupal::messenger(),
#     ));
#   }

  return [
    'dxpr_cms_installer_module_keys' => [
      'display_name' => t('Enter API keys'),
       'type' => 'form',
       'function' => ConfigureAPIKeysForm::class,
    ],
    'dxpr_cms_installer_prepare_trial' => [
      'run' => getenv('DXPR_CMS_TRIAL') ? INSTALL_TASK_RUN_IF_REACHED : INSTALL_TASK_SKIP,
    ],
    'dxpr_cms_uninstall_unused_ai_modules' => [
      // Uninstall the unused AI provider module.
    ],
    'dxpr_cms_installer_uninstall_myself' => [
      // As a final task, this profile should uninstall itself.
    ],
    'dxpr_cms_installer_rebuild_theme' => [
      // Rebuild theme CSS.
    ],
  ];
}

/**
 * Implements hook_install_tasks_alter().
 */
function dxpr_cms_installer_install_tasks_alter(array &$tasks, array $install_state): void {
  $insert_before = function (string $key, array $additions) use (&$tasks): void {
    $key = array_search($key, array_keys($tasks), TRUE);
    if ($key === FALSE) {
      return;
    }
    // This isn't very clean, but it's the only way to positionally splice into
    // an associative (and therefore by definition unordered) array.
    $tasks_before = array_slice($tasks, 0, $key, TRUE);
    $tasks_after = array_slice($tasks, $key, NULL, TRUE);
    $tasks = $tasks_before + $additions + $tasks_after;
  };
  $insert_before('install_settings_form', [
    'dxpr_cms_installer_choose_recipes' => [
      'display_name' => t('Choose add-ons'),
      'type' => 'form',
      'run' => array_key_exists('recipes', $install_state['parameters']) ? INSTALL_TASK_SKIP : INSTALL_TASK_RUN_IF_REACHED,
      'function' => RecipesForm::class,
    ],
    'dxpr_cms_installer_site_name_form' => [
      'display_name' => t('Name your site'),
      'type' => 'form',
      'run' => array_key_exists('site_name', $install_state['parameters']) ? INSTALL_TASK_SKIP : INSTALL_TASK_RUN_IF_REACHED,
      'function' => SiteNameForm::class,
    ],
  ]);

  $configure_form_task = $tasks['install_configure_form'];
  unset(
    $tasks['install_install_profile'],
    $tasks['install_configure_form'],
  );
  $insert_before('install_profile_modules', [
    'install_install_profile' => [
      'function' => 'dxpr_cms_installer_install_myself',
    ],
    'install_configure_form' => $configure_form_task,
  ]);

  // Set English as the default language; it can be changed mid-stream. We can't
  // use the passed-in $install_state because it's not passed by reference.
  $GLOBALS['install_state']['parameters'] += ['langcode' => 'en'];

  // Wrap the install_profile_modules() function, which returns a batch job, and
  // add all the necessary operations to apply the chosen template recipe.
  $tasks['install_profile_modules']['function'] = 'dxpr_cms_installer_apply_recipes';
}

/**
 * Installs the User module, and this profile.
 *
 * @param array $install_state
 *   The current installation state.
 */
function dxpr_cms_installer_install_myself(array &$install_state): void {
  // We'll need User installed for the next step, which is configuring the site
  // and administrator account.
  \Drupal::service(ModuleInstallerInterface::class)->install([
    'user',
  ]);
  // Officially install this profile so that its behaviors and visual overrides
  // will be in effect for the remainder of the install process. This also
  // ensures that the administrator role is created and assigned to user 1 in
  // the next step.
  install_install_profile($install_state);
}

/**
 * Implements hook_form_alter() for install_settings_form.
 *
 * @see \Drupal\Core\Installer\Form\SiteSettingsForm
 */
function dxpr_cms_installer_form_install_settings_form_alter(array &$form): void {
  // Default to SQLite, if available, because it doesn't require any additional
  // configuration.
  if (extension_loaded('pdo_sqlite') && array_key_exists(SQLITE_DRIVER, $form['driver']['#options'])) {
    $form['driver']['#default_value'] = SQLITE_DRIVER;

    // The database file path has a sensible default value, so move it into the
    // advanced options.
    $form['settings'][SQLITE_DRIVER]['advanced_options']['database'] = $form['settings'][SQLITE_DRIVER]['database'];
    unset($form['settings'][SQLITE_DRIVER]['database']);
  }
}

/**
 * Implements hook_form_alter() for install_configure_form.
 *
 * @see \Drupal\Core\Installer\Form\SiteConfigureForm
 */
function dxpr_cms_installer_form_install_configure_form_alter(array &$form, FormStateInterface $form_state): void {
  global $install_state;

  $form['#title'] = t('Create your account');

  $form['help'] = [
    '#prefix' => '<p class="cms-installer__subhead">',
    '#markup' => t('Creating an account allows you to log in to your site.'),
    '#suffix' => '</p>',
    '#weight' => -40,
  ];

  $form['site_information']['#type'] = 'container';
  // We collected the site name in a previous step.
  $form['site_information']['site_name'] = [
    '#type' => 'hidden',
    '#default_value' => $GLOBALS['install_state']['parameters']['site_name'],
  ];
  // Automatically generate the site email address.
  $form['site_information']['site_mail'] = [
    '#type' => 'hidden',
    '#default_value' => 'no-reply@' . \Drupal::request()->getHost(),
  ];

  $form['admin_account']['#type'] = 'container';
  // `admin` is a sensible name for user 1.
  $form['admin_account']['account']['name'] = [
    '#type' => 'hidden',
    '#default_value' => 'admin',
  ];
  $form['admin_account']['account']['mail'] = [
    '#prefix' => '<div class="cms-installer__form-group">',
    '#suffix' => '</div>',
    '#type' => 'email',
    '#title' => t('Email'),
    '#required' => TRUE,
    '#default_value' => $install_state['forms']['install_configure_form']['account']['mail'] ?? '',
    '#weight' => 10,
  ];
  $form['admin_account']['account']['pass'] = [
    '#prefix' => '<div class="cms-installer__form-group">',
    '#suffix' => '</div>',
    '#type' => 'password',
    '#title' => t('Password'),
    '#description' => t('At least 10 characters, but more is better.'),
    '#required' => TRUE,
    '#default_value' => $install_state['forms']['install_configure_form']['account']['pass']['pass1'] ?? '',
    '#weight' => 20,
    '#value_callback' => '_dxpr_cms_installer_password_value',
  ];

  // Hide parts of the form we don't care about.
  $form['regional_settings']['#access'] = FALSE;
  $form['update_notifications']['#access'] = FALSE;

  $form['actions']['submit']['#value'] = t('Finish');
}

function _dxpr_cms_installer_password_value(&$element, $input, FormStateInterface $form_state): mixed {
  // Work around this fact that Drush and `drupal install`, which submit this
  // form programmatically, assume the password is a password_confirm element.
  if (is_array($input) && $form_state->isProgrammed()) {
    $input = $input['pass1'];
  }
  return Password::valueCallback($element, $input, $form_state);
}

/**
 * Runs a batch job that applies the template and add-on recipes.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 *
 * @return array
 *   The batch job definition.
 */
function dxpr_cms_installer_apply_recipes(array &$install_state): array {
  $batch = install_profile_modules($install_state);
  $batch['title'] = t('Setting up your site');

  ['install_path' => $cookbook_path] = InstalledVersions::getRootPackage();
  $cookbook_path .= '/recipes';

  foreach ($install_state['parameters']['recipes'] as $recipe) {
    $recipe = RecipeLoader::load(
      $cookbook_path . '/' . $recipe,
      // Only save a cached copy of the recipe if this environment variable is
      // set. This allows us to ship a pre-primed cache of recipes to improve
      // installer performance for first-time users.
      (bool) getenv('DXPR_CMS_INSTALLER_WRITE_CACHE'),
    );

    foreach (RecipeRunner::toBatchOperations($recipe) as $operation) {
      $batch['operations'][] = $operation;
    }
  }
  return $batch;
}

/**
 * Implements hook_library_info_alter().
 */
function dxpr_cms_installer_library_info_alter(array &$libraries, string $extension): void {
  global $install_state;
  // If a library file's path starts with `/`, the library collection system
  // treats it as relative to the base path.
  // @see \Drupal\Core\Asset\LibraryDiscoveryParser::buildByExtension()
  $base_path = '/' . $install_state['profiles']['dxpr_cms_installer']->getPath();

  if ($extension === 'claro') {
    $libraries['maintenance-page']['css']['theme']["$base_path/css/gin-variables.css"] = [];
    $libraries['maintenance-page']['css']['theme']["$base_path/css/fonts.css"] = [];
    $libraries['maintenance-page']['css']['theme']["$base_path/css/installer-styles.css"] = [];
    $libraries['maintenance-page']['css']['theme']["$base_path/css/add-ons.css"] = [];
    $libraries['maintenance-page']['css']['theme']["$base_path/css/language-dropdown.css"] = [];
    $libraries['maintenance-page']['js']["$base_path/js/language-dropdown.js"] = [];
    $libraries['maintenance-page']['dependencies'][] = 'core/once';
  }
  if ($extension === 'core') {
    $libraries['drupal.progress']['js']["$base_path/js/progress.js"] = [];
  }
}


/**
 * Uninstalls the unused AI provider module.
 */
function dxpr_cms_uninstall_unused_ai_modules(): void {
  $providers = ['anthropic', 'openai'];
  $provider_plugin = \Drupal::service('ai.provider');
  $unusable_providers = [];
  foreach ($providers as $provider) {
    // Create an instance and check if its usable (setup).
    $plugin = $provider_plugin->createInstance($provider);
    if (!$plugin->isUsable()) {
      $unusable_providers[] = $plugin;
    }
  }

  // If one of the providers worked, uninstall the unused one(s), but if all
  // failed, don't do anything.
  if (count($unusable_providers) < count($providers)) {
    foreach ($unusable_providers as $plugin) {
      \Drupal::service(ModuleInstallerInterface::class)->uninstall([
        $plugin->getModuleDataName(),
      ]);
    }
  }
}

/**
 * Uninstalls this install profile, as a final step.
 *
 * @see drupal_install_system()
 */
function dxpr_cms_installer_uninstall_myself(): void {
  \Drupal::service(ModuleInstallerInterface::class)->uninstall([
    'dxpr_cms_installer',
  ]);

  // Clear all previous status messages to avoid clutter.
  \Drupal::messenger()->deleteByType(MessengerInterface::TYPE_STATUS);
}

/**
 * Rebuild theme CSS.
 */
function dxpr_cms_installer_rebuild_theme(): void {
  require_once \Drupal::service('extension.list.theme')->getPath('dxpr_theme') . '/dxpr_theme_callbacks.inc';
  dxpr_theme_css_cache_build('dxpr_theme');
}

/**
 * Implements hook_theme_registry_alter().
 */
function dxpr_cms_installer_theme_registry_alter(array &$hooks): void {
  global $install_state;
  
  // Check if install_state and required keys exist before accessing
  if (!empty($install_state['profiles']['dxpr_cms_installer'])) {
    $installer_path = $install_state['profiles']['dxpr_cms_installer']->getPath();
    $hooks['install_page']['path'] = $installer_path . '/templates';
  }
}

/**
 * Preprocess function for all pages in the installer.
 */
function dxpr_cms_installer_preprocess_install_page(array &$variables): void {
  // Don't show the task list or the version of Drupal.
  unset($variables['page']['sidebar_first'], $variables['site_version']);

  global $install_state;
  $images_path = $install_state['profiles']['dxpr_cms_installer']->getPath() . '/images';
  $images_path = \Drupal::service(FileUrlGeneratorInterface::class)
    ->generateString($images_path);
  $variables['images_path'] = $images_path;
}
