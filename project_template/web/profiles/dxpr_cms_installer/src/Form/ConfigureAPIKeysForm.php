<?php

namespace Drupal\dxpr_cms_installer\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\dxpr_builder\Service\DxprBuilderJWTDecoder;
use Drupal\key\Entity\Key;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines form for entering DXPR API key and Google Cloud Translation API key.
 */
class ConfigureAPIKeysForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $root;

  /**
   * The info parser service.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The form helper.
   *
   * @var \Drupal\dxpr_cms_installer\FormHelper
   */
  protected $formHelper;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * JWT service to manipulate the DXPR JSON token.
   *
   * @var \Drupal\dxpr_builder\Service\DxprBuilderJWTDecoder
   */
  protected $jwtDecoder;


  /**
   * Configure API Keys Form constructor.
   *
   * @param string $root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\dxpr_builder\Service\DxprBuilderJWTDecoder $jwtDecoder
   *   Parsing DXPR JWT token.
   */
  public function __construct(
    $root,
    InfoParserInterface $info_parser,
    TranslationInterface $translator,
    ConfigFactoryInterface $config_factory,
    DxprBuilderJWTDecoder $jwtDecoder
  ) {
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
    $this->configFactory = $config_factory;
    $this->jwtDecoder = $jwtDecoder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->getParameter('app.root'),
      $container->get('info_parser'),
      $container->get('string_translation'),
      $container->get('config.factory'),
      $container->get('dxpr_builder.jwt_decoder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dxpr_cms_installer_api_keys_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('API Keys Configuration');

    $form['help'] = [
      '#prefix' => '<p class="cms-installer__subhead">',
      '#markup' => $this->t('Enter your DXPR API Key to unlock premium features AND get FREE access to DXPR AI - including models from OpenAI, Claude, Gemini, MistralAI, XAI, and Perplexity at no additional cost.'),
      '#suffix' => '</p>',
    ];

    $form['json_web_token'] = [
      '#type' => 'textarea',
      '#title' => $this->t('DXPR API Key'),
      '#description' => $this->t('Sign up free at <a href="https://dxpr.com/user/free-registration" target="_blank">DXPR.com</a> (takes 30 seconds) and grab your key from the <a href="https://app.dxpr.com/getting-started" target="_blank">Get Started dashboard</a>. Unlock enterprise-grade AI access included with your free account.'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#button_type' => 'primary',
      ],
      '#type' => 'actions',
      '#weight' => 5,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $json_web_token = $form_state->getValue('json_web_token');
    if (!empty($json_web_token)) {
      // Create a key entity for DXPR Builder
      try {
        $key = Key::create([
          'id' => 'dxpr_builder_key',
          'label' => 'DXPR Builder API Key',
          'description' => 'API Key for DXPR Builder',
          'key_type' => 'authentication',
          'key_provider' => 'config',
        ]);
        $key->setKeyValue($json_web_token);
        $key->save();

        // Update DXPR Builder settings to use the key
        $this->configFactory->getEditable('dxpr_builder.settings')
          ->set('api_key_storage', 'key')
          ->set('key_provider', 'dxpr_builder_key')
          ->set('json_web_token', NULL)
          ->save();

        // Update CKEditor AI Agent settings to use the same key
        $this->configFactory->getEditable('ckeditor_ai_agent.settings')
          ->set('key_provider', 'dxpr_builder_key')
          ->set('model', 'dxai:kavya-m1')
          ->save();
      }
      catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while saving the DXPR Builder key: @error', ['@error' => $e->getMessage()]));
      }
    }

    // Configure DXPR AI provider using the DXPR Builder key
    if (!empty($json_web_token)) {
      try {
        // Configure DXPR AI provider to use the same key
        $this->configFactory->getEditable('ai_provider_dxpr.settings')
          ->set('api_key', 'dxpr_builder_key')
          ->save();

        // Set DXPR as default provider for all AI operations
        $this->configFactory->getEditable('ai.settings')
          ->set('default_providers.chat', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1',
          ])
          ->set('default_providers.chat_with_image_vision', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1',
          ])
          ->set('default_providers.chat_with_complex_json', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1',
          ])
          ->set('default_providers.chat_with_tools', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1',
          ])
          ->set('default_providers.chat_with_structured_response', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1',
          ])
          ->set('default_providers.translate_text', [
            'provider_id' => 'dxpr',
            'model_id' => 'kavya-m1-fast',
          ])
          ->save();
      }
      catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while configuring DXPR AI: @error', ['@error' => $e->getMessage()]));
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<string, mixed> $form
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue('json_web_token')) {
      $jwtPayloadData = $this->jwtDecoder->decodeJwt($form_state->getValue('json_web_token'));
      if ($jwtPayloadData['sub'] === NULL || $jwtPayloadData['scope'] === NULL) {
        $form_state->setErrorByName('json_web_token', $this->t('Invalid DXPR Key. Get your free key at https://dxpr.com/user/free-registration'));
      }
      elseif ($jwtPayloadData['dxpr_tier'] === NULL) {
        $form_state->setErrorByName('json_web_token', $this->t('Your product key (JWT) is outdated and not compatible with DXPR Builder version 2.0.0 and up. Please follow instructions <a href=":uri">here</a> to get a new product key.', [
          ':uri' => 'https://app.dxpr.com/download/all#token',
        ]));
      }
    }

    // DXPR AI requires the DXPR API key (same as Builder key)
    if (empty($form_state->getValue('json_web_token'))) {
      $form_state->setErrorByName('json_web_token', $this->t('DXPR API Key is required for AI features. Get yours for free at https://dxpr.com/user/free-registration'));
    }
  }


}
