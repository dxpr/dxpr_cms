<?php

namespace Drupal\dxpr_cms_installer\Form;

use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatMessage;
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
   * The AI provider plugin manager.
   *
   * @var \Drupal\ai\AiProviderPluginManager
   */
  protected $aiProviderPluginManager;

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
   * @param \Drupal\ai\AiProviderPluginManager $aiProviderPluginManager
   *   The AI provider plugin manager.
   */
  public function __construct(
    $root,
    InfoParserInterface $info_parser,
    TranslationInterface $translator,
    ConfigFactoryInterface $config_factory,
    DxprBuilderJWTDecoder $jwtDecoder,
    AiProviderPluginManager $aiProviderPluginManager
  ) {
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
    $this->configFactory = $config_factory;
    $this->jwtDecoder = $jwtDecoder;
    $this->aiProviderPluginManager = $aiProviderPluginManager;
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
      $container->get('ai.provider')
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
      '#empty_option' => $this->t('No AI'),
      '#options' => [
        'openai' => $this->t('OpenAI'),
        'anthropic' => $this->t('Anthropic'),
      ],
    ];

    $form['openai_key'] = [
      '#type' => 'password',
      '#title' => $this->t('OpenAI API key'),
      '#description' => $this->t('Get a key from <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a>.'),
      '#maxlength' => 255,
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
      '#maxlength' => 255,
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
      try {
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
        // Set the default chat and chat_with_image_vision provider.
        $this->configFactory->getEditable('ai.settings')->set('default_providers.chat', [
          'provider_id' => $ai_provider,
          'model_id' => $ai_provider == 'openai' ? 'gpt-4o' : $this->getFirstAiModelId($ai_provider),
        ])->set('default_providers.chat_with_image_vision', [
          'provider_id' => $ai_provider,
          'model_id' => $ai_provider == 'openai' ? 'gpt-4o' : $this->getFirstAiModelId($ai_provider),
        ])->save();
      }
      catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred while saving the AI provider key: @error', ['@error' => $e->getMessage()]));
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

    // If a provider is set we test it.
    if ($provider = $form_state->getValue('ai_provider')) {
      $key = $provider . '_key';
      // It has to be set.
      if (empty($form_state->getValue($key))) {
        $form_state->setErrorByName($key, $this->t('API key is required, if you want to enable this provider.'));
      }
      else {
        // Try to send a message.
        try {
          $this->validateAiProvider($provider, $form_state->getValue($key));
        } catch (\Exception $e) {
          $form_state->setErrorByName($key, $this->t('Your API key seems to be invalid with message %message', [
            '%message' => $e->getMessage(),
          ]));
        }
      }
    }
  }

  /**
   * Test the provider. Will throw an exception if it is invalid.
   *
   * @param string $provider_id
   *   The provider ID.
   * @param string $api_key
   *   The API key.
   */
  protected function validateAiProvider(string $provider_id, string $api_key) {
    /** @var \Drupal\ai\AiProviderInterface|\Drupal\ai\OperationType\Chat\ChatInterface */
    $provider = $this->aiProviderPluginManager->createInstance($provider_id);
    // Try to send a chat message.
    $provider->setAuthentication($api_key);
    $models = $provider->getConfiguredModels('chat');
    $input = new ChatInput([
      new ChatMessage('user', 'Hello'),
    ]);
    // We use the first model to test.
    $provider->chat($input, key($models));
  }

  /**
   * Get the latest model, will also work on future providers.
   *
   * @param string $provider_id
   *   The provider ID.
   *
   * @return string
   *   The model ID.
   */
  protected function getFirstAiModelId(string $provider_id): string {
    // We can assume this works, since validation works.
    $provider = $this->aiProviderPluginManager->createInstance($provider_id);
    // @todo Change to chat_with_image_vision when it is available.
    $models = $provider->getConfiguredModels('chat');
    return key($models);
  }

}
