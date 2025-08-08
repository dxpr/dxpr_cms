<?php

namespace Drupal\dxpr_cms_installer\Form;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\RecipeKit\Installer\FormInterface as InstallerFormInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines form for selecting DXPR CMS Multilingual configuration options form.
 */
class ConfigureMultilingualForm extends FormBase implements InstallerFormInterface {

  use StringTranslationTrait;

  /**
   * Name of the installer task for adding languages with batch.
   */
  public const string INSTALL_LANGUAGES_TASK = self::class . '::configureMultilingual';

  /**
   * {@inheritdoc}
   */
  public static function toInstallTask(array $install_state): array {
    // Make user to be able to set up multiple languages if the appropriate
    // recipe is chosen.
    $is_recipe_selected = in_array(
      needle: 'drupal/dxpr_cms_multilingual',
      haystack: (array) ($install_state['parameters']['recipes'] ?? [])
    );

    if (
      // First, we should check if the form was submitted and
      // a default language was set up.
      !empty($install_state['parameters']['dxpr_cms_installer']['default_language']) ||
      // Second, check if the recipe was chosen.
      !$is_recipe_selected
    ) {
      $run = INSTALL_TASK_SKIP;
    }

    return [
      'display_name' => t('Multilingual set-up'),
      'type' => 'form',
      'run' => $run ?? INSTALL_TASK_RUN_IF_REACHED,
      'function' => static::class,
    ];
  }

  /**
   * Batch job to configure multilingual components.
   *
   * @param array $install_state
   *   The current installation state.
   *
   * @return array
   *   The batch job definition.
   */
  public static function configureMultilingual(array &$install_state): array {
    // If selected languages are available, add them and fetch translations.
    $additional_languages = $install_state['parameters']['dxpr_cms_installer']['additional_languages'] ?? [];
    if (!$additional_languages) {
      return [];
    }

    foreach ($additional_languages as $language_code) {
      $batch['operations'][] = [
        ConfigureMultilingualForm::class . '::addLanguage',
        [$language_code],
      ];
    }

    return $batch ?? [];
  }

  /**
   * Batch function to add selected languages then fetch all translations.
   *
   * @param string $language_code
   *   Language code to install and fetch all translations.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function addLanguage(string $language_code): void {
    $exists = ConfigurableLanguage::load($language_code);
    if ($exists) {
      return;
    }

    ConfigurableLanguage::createFromLangcode($language_code)->save();
  }

  /**
   * Alters the installation tasks based on the installation state.
   *
   * @param array $tasks
   *   The array of installation tasks to be processed.
   * @param array $install_state
   *   The current installation state.
   */
  public static function tasksAlter(array &$tasks, array $install_state): void {
    $has_multilingual = $install_state['parameters']['dxpr_cms_installer']['additional_languages'] ?? FALSE;
    if (!$has_multilingual) {
      $tasks[ConfigureMultilingualForm::INSTALL_LANGUAGES_TASK][] = INSTALL_TASK_SKIP;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dxpr_cms_installer_multilingual_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL): array {
    // Native language list building code taken from the core installation step.
    $files = count($install_state['translations']) > 1
      ? $install_state['translations']
      : [];

    $standard_languages = LanguageManager::getStandardLanguageList();

    // Build a select list with language names in the native language for
    // the user to choose from. And build a list of available languages
    // for the browser to select the language default from.
    // Select lists based on all standard languages.
    $options = array_map(function ($language_names) {
      return $language_names[1];
    }, $standard_languages);

    // Add languages based on language files in the translations directory.
    foreach ($files as $langcode => $uri) {
      $options[$langcode] = $standard_languages[$langcode][1] ?? $langcode;
    }
    asort($options);

    $default_langcode = $install_state['parameters']['langcode'] ??
      $this->configFactory()
        ->getEditable('system.site')
        ->get('default_langcode');

    $form['#title'] = $this->t('Multilingual configuration');

    $form['langcode'] = [
      '#type' => 'select',
      '#title' => $this->t('Default language'),
      '#options' => $options,
      // Use the browser-detected language as default or English if nothing
      // found.
      '#default_value' => $default_langcode ?? NULL,
      '#attached' => [
        'library' => [
          'dxpr_cms_installer_theme/choices',
        ],
      ],
      '#attributes' => [
        'style' => 'width:100%;',
        'class' => ['choices-select'],
      ],
    ];

    $form['additional_languages'] = [
      '#type' => 'select',
      '#title' => $this->t('Additional site languages'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#attached' => [
        'library' => [
          'dxpr_cms_installer_theme/choices',
        ],
      ],
      '#attributes' => [
        'style' => 'width:100%;',
        'class' => ['choices-select'],
      ],
    ];

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#button_type' => 'primary',
      ],
      '#type' => 'actions',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    global $install_state;

    $default_language = $form_state->getValue('langcode');

    // Change a default language in the global scope as it might have impact
    // on displayed UI language.
    $install_state['parameters']['langcode'] = $default_language;

    // Get a list of selected additional languages.
    $languages = $form_state->getValue('additional_languages');

    $install_state['parameters']['dxpr_cms_installer']['default_language'] = $default_language;
    $install_state['parameters']['dxpr_cms_installer']['additional_languages'] = array_filter($languages);
  }

}
