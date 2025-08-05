<?php

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\dxpr_cms_installer\Form\ConfigureAPIKeysForm;
use Drupal\dxpr_cms_installer\Form\ConfigureMultilingualForm;
use Drupal\RecipeKit\Installer\Hooks;
use Drupal\RecipeKit\Installer\Messenger;

/**
 * Implements hook_install_tasks().
 */
function dxpr_cms_installer_install_tasks(): array {
  $tasks = Hooks::installTasks();

  if (getenv('IS_DDEV_PROJECT')) {
    Messenger::reject(
      'All necessary changes to %dir and %file have been made, so you should remove write permissions to them now in order to avoid security risks. If you are unsure how to do so, consult the <a href=":handbook_url">online handbook</a>.',
    );
  }

  // Ensure our forms are loadable in all situations, even if the installer is
  // not a Composer-managed package.
  \Drupal::service('class_loader')
    ->addPsr4('Drupal\\dxpr_cms_installer\\', __DIR__ . '/src');

  $additional_tasks = [
    'dxpr_cms_installer_module_keys' => [
      'display_name' => t('Enter API keys'),
      'type' => 'form',
      'function' => ConfigureAPIKeysForm::class,
    ],
    'dxpr_cms_uninstall_unused_ai_modules' => [
      // Uninstall the unused AI provider module.
    ],
    'dxpr_cms_installer_rebuild_theme' => [
      // Rebuild theme CSS.
    ],
    ConfigureMultilingualForm::CONFIGURE_MULTILINGUAL_TASK => [
      'display_name' => t('Multilingual set-up'),
      'type' => 'form',
      'function' => ConfigureMultilingualForm::class,
    ],
    ConfigureMultilingualForm::INSTALL_LANGUAGES_TASK => [
      'display_name' => t('Multilingual imports'),
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
  ];

  return array_merge($tasks, $additional_tasks);
}

/**
 * Implements hook_install_tasks_alter().
 */
function dxpr_cms_installer_install_tasks_alter(array &$tasks, array $install_state): void {
  Hooks::installTasksAlter($tasks, $install_state);

  // The recipe kit doesn't change the title of the batch job that applies all
  // the recipes, so to override it, we use core's custom string overrides.
  // We can't use the passed-in $install_state here, because it isn't passed by
  // reference.
  $langcode = $GLOBALS['install_state']['parameters']['langcode'];
  $settings = Settings::getAll();
  // @see install_profile_modules()
  $settings["locale_custom_strings_$langcode"]['']['Installing @drupal'] = 'Setting up your site';
  new Settings($settings);

  // Make user to be able to set up multiple languages if the recipe is chosen.
  if (!in_array('drupal/dxpr_cms_multilingual', (array) ($install_state['parameters']['recipes'] ?? []))) {
    $tasks[ConfigureMultilingualForm::CONFIGURE_MULTILINGUAL_TASK]['run'] = INSTALL_TASK_SKIP;
  }

  $is_multilingual = $install_state['dxpr_cms_installer']['additional_languages'] ?? FALSE;
  if (!$is_multilingual) {
    $tasks[ConfigureMultilingualForm::INSTALL_LANGUAGES_TASK][] = INSTALL_TASK_SKIP;
  }
}

/**
 * Implements hook_form_alter().
 */
function dxpr_cms_installer_form_alter(array &$form, FormStateInterface $form_state, string $form_id): void {
  Hooks::formAlter($form, $form_state, $form_id);
}

/**
 * Implements hook_form_alter() for install_configure_form.
 */
function dxpr_cms_installer_form_install_configure_form_alter(array &$form): void {
  // We always install Automatic Updates, so we don't need to expose the update
  // notification settings.
  $form['update_notifications']['#access'] = FALSE;
}

/**
 * No longer needed - DXPR CMS now uses only DXPR AI provider.
 */
function dxpr_cms_uninstall_unused_ai_modules(): void {
  // This function is no longer needed since we only use DXPR AI provider.
  // Kept for compatibility with existing installation tasks.
}

/**
 * Rebuild theme CSS.
 */
function dxpr_cms_installer_rebuild_theme(): void {
  require_once \Drupal::service('extension.list.theme')->getPath('dxpr_theme') . '/dxpr_theme_callbacks.inc';
  dxpr_theme_css_cache_build('dxpr_theme');
}
