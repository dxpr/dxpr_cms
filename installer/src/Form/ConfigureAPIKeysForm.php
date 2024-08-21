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
use OpenAI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WpAi\Anthropic\AnthropicAPI;

/**
 * Defines form for entering DXPR Builder product key and Google Cloud Translation API key.
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
  public function __construct($root, InfoParserInterface $info_parser,
  TranslationInterface $translator,
  ConfigFactoryInterface $config_factory,
  DxprBuilderJWTDecoder $jwtDecoder,) {
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
      $container->get('dxpr_builder.jwt_decoder'),
      $container->get('module_installer')
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

    $form['json_web_token'] = [
      '#type' => 'textarea',
      '#title' => $this->t('DXPR Builder product key'),
      '#description' => $this->t('Create a free account at <a href="https://dxpr.com/pricing" target="_blank">DXPR.com</a> and find your key in the <a href="https://app.dxpr.com/getting-started" target="_blank">Get Started dashboard</a>.'),
    ];



    // if (isset($install_state['dxpr_cms_installer']['enable_multilingual']) &&
    // $install_state['dxpr_cms_installer']['enable_multilingual']) {
      $form['google_translation_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Google Cloud Translation API key (optional)'),
        '#description' => $this->t('Get a key from <a href="https://console.cloud.google.com/marketplace/product/google/translate.googleapis.com" target="_blank">cloud.google.com</a>.'),
      ];
    // }

    $form['ai_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Select AI Provider'),
      '#description' => $this->t('If you want to enable AI features like ai powered alt text generation, select the AI provider you want to use for AI features and fill in the API Key.'),
      '#empty_option' => $this->t('-- Please choose --'),
      '#options' => [
        'none' => $this->t('No AI'),
        'openai' => $this->t('OpenAI'),
        'anthropic' => $this->t('Anthropic'),
      ],
    ];

    $form['openai_key'] = [
      '#type' => 'password',
      '#title' => $this->t('OpenAI API key'),
      '#description' => $this->t('Get a key from <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a>.'),
      '#states' => [
        'visible' => [
          ':input[name="ai_provider"]' => ['value' => 'openai'],
        ],
      ],
    ];

    $form['anthropic_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Anthropic API key'),
      '#description' => $this->t('Get a key from <a href="https://console.anthropic.com/settings/keys" target="_blank">console.anthropic.com/settings/keys</a>.'),
      '#states' => [
        'visible' => [
          ':input[name="ai_provider"]' => ['value' => 'anthropic'],
        ],
      ],
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
      $this->configFactory->getEditable('dxpr_builder.settings')->set('json_web_token', $json_web_token)->save();
    }

    $google_translation_key = $form_state->getValue('google_translation_key');
    if (!empty($google_translation_key)) {
      $this->configFactory->getEditable('tmgmt.translator.google')->set('settings.api_key', $google_translation_key)->save();
    }

    // If the AI provider is set, enable the appropriate modules.
    if ($ai_provider = $form_state->getValue('ai_provider')) {
      if ($ai_provider !== 'none') {
        // Setup the key for the provider.
        $key_id = $ai_provider . '_key';
        $key = Key::create([
          'id' => $key_id,
          'label' => ucfirst($ai_provider) . ' API Key',
          'description' => 'API Key for ' . ucfirst($ai_provider),
          'key_type' => 'authentication',
          'key_provider' => 'config',
        ]);
        $key->setKeyValue($form_state->getValue($key_id));
        $key->save();
        // Add the key to the config.
        $this->configFactory->getEditable('provider_' . $ai_provider . '.settings')->set('api_key', $key_id)->save();
        // Set the default provider.
        $this->configFactory->getEditable('ai.settings')->set('default_providers.chat', [
          'provider_id' => $ai_provider,
          'model_id' => $ai_provider == 'openai' ? 'gpt-4o' : 'claude-3-5-sonnet-20240620',
        ])->save();
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
        $form_state->setErrorByName('json_web_token', $this->t('Your DXPR Builder product key can’t be read, please make sure you copy the whole key without any trailing or leading spaces into the form.'));
      }
      elseif ($jwtPayloadData['dxpr_tier'] === NULL) {
        $form_state->setErrorByName('json_web_token', $this->t('Your product key (JWT) is outdated and not compatible with DXPR Builder version 2.0.0 and up. Please follow instructions <a href=":uri">here</a> to get a new product key.', [
          ':uri' => 'https://app.dxpr.com/download/all#token',
        ]));
      }
    }

    // Test the OpenAI key if it is set.
    if ($form_state->getValue('ai_provider') === 'openai') {
      // It has to be set.
      if (empty($form_state->getValue('openai_key'))) {
        $form_state->setErrorByName('openai_key', $this->t('OpenAI API key is required, if you want to enable OpenAI.'));
      }
      // It has to be valid.
      elseif ($errorMessage = $this->testOpenAIKey($form_state->getValue('openai_key'))) {
        $form_state->setErrorByName('openai_key', $this->t('Your OpenAI API key seems to be invalid with message %message.', [
          '%message' => $errorMessage,
        ]));
      }
    }

    // Test the Anthropic key if it is set.
    if ($form_state->getValue('ai_provider') === 'anthropic') {
      // It has to be set.
      if (empty($form_state->getValue('anthropic_key'))) {
        $form_state->setErrorByName('anthropic_key', $this->t('Anthropic API key is required, if you want to enable Anthropic.'));
      }
      // It has to be valid.
      elseif ($errorMessage = $this->testAnthropicKey($form_state->getValue('anthropic_key'))) {
        $form_state->setErrorByName('anthropic_key', $this->t('Your Anthropic API key seems to be invalid with message %message.', [
          '%message' => $errorMessage,
        ]));
      }
    }

    // If its empty do a pseudo require.
    if (empty($form_state->getValue('ai_provider'))) {
      $form_state->setErrorByName($this->t('Please make a choice.'));
    }
  }

  /**
   * Simple test of the OpenAI Key.
   *
   * @param string $openai_key
   *   The OpenAI key.
   *
   * @return string
   *   If the key is valid, the error message.
   */
  protected function testOpenAIKey(string $openai_key): string {
    $client = OpenAI::client($openai_key);
    try {
      $client->models()->list();
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
    return '';
  }

  /**
   * Simple test of the Anthropic Key.
   *
   * @param string $anthropic_key
   *   The Anthropic key.
   *
   * @return string
   *   If the key is valid, the message.
   */
  protected function testAnthropicKey(string $anthropic_key): string {
    $client = new AnthropicAPI($anthropic_key);
    $payload = [
      'model' => 'claude-3-5-sonnet-20240620',
      'messages' => [
        [
          'role' => 'user',
          'content' => 'Say hello!',
        ],
      ],
    ];
    try {
      $client->messages()->maxTokens(10)->create($payload);
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
    return "";
  }

}
