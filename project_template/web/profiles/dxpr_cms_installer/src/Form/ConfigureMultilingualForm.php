<?php

namespace Drupal\dxpr_cms_installer\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines form for selecting DXPR CMS Multilingual configuration options form.
 */
class ConfigureMultilingualForm extends FormBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Name of the installer task for adding the current form page.
   */
  public const string CONFIGURE_MULTILINGUAL_TASK = ConfigureMultilingualForm::class;

  /**
   * Name of the installer task for adding languages with batch.
   */
  public const string INSTALL_LANGUAGES_TASK = self::class . '::configureMultilingual';

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

    $default_langcode = $this->configFactory()
      ->getEditable('system.site')
      ->get('default_langcode');

    // Save the default language name.
    $default_language_name = $options[$default_langcode] ?? NULL;

    // Remove the default language from the list of multilingual languages.
    unset($options[$default_langcode]);

    $form['#title'] = $this->t('Multilingual configuration');

    $form['additional_languages'] = [
      '#type' => 'select',
      '#title' => $this->t('Additional site languages'),
      '#description' => $this->t('<strong>@default_language_name</strong> is the default language.', [
        '@default_language_name' => $default_language_name ?? 'undefined',
      ]),
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

    // Get a list of selected additional languages.
    $languages = $form_state->getValue('additional_languages');
    if (!$languages) {
      // If a user doesn't choose any additional language,
      // we shouldn't do anything.
      return;
    }

    $install_state['dxpr_cms_installer']['additional_languages'] = $languages;
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
    $additional_languages = $install_state['dxpr_cms_installer']['additional_languages'] ?? [];
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
    ConfigurableLanguage::createFromLangcode($language_code)->save();
  }

}
