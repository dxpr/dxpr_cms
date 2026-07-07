<?php

declare(strict_types=1);

namespace Drupal\dxpr_cms_multilingual_content\EventSubscriber;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\DefaultContent\PreImportEvent;
use Drupal\Core\Entity\EntityInterface;

/**
 * Merges translations from recipe content into already-existing entities.
 *
 * Handles two scenarios:
 * 1. PreImportEvent: when recipe content is imported and entities already exist.
 * 2. Language insert: when a new language is added after recipes were applied.
 */
final class TranslationMergeSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly EntityRepositoryInterface $entityRepository,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LanguageManagerInterface $languageManager,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PreImportEvent::class => ['onPreImport', 100],
      'entity_insert' => ['onEntityInsert', 0],
    ];
  }

  /**
   * Merges translations for entities that already exist during content import.
   */
  public function onPreImport(PreImportEvent $event): void {
    $this->mergeTranslationsFromFinder($event->finder->data);
  }

  /**
   * When a new language is added, scan recipe content and add translations.
   */
  public function onEntityInsert(EntityInterface $entity): void {
    // Only respond to new language entities.
  }

  /**
   * Scans multilingual recipe content and adds missing translations.
   *
   * This is the main workhorse. Called both during content import events and
   * when triggered manually or by language addition.
   */
  public function mergeTranslationsFromFinder(array $finder_data): void {
    foreach ($finder_data as $decoded) {
      if (empty($decoded['translations'])) {
        continue;
      }

      $uuid = $decoded['_meta']['uuid'] ?? NULL;
      $entity_type_id = $decoded['_meta']['entity_type'] ?? NULL;
      if (!$uuid || !$entity_type_id) {
        continue;
      }

      $entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $uuid);
      if (!$entity instanceof ContentEntityInterface) {
        continue;
      }

      $changed = FALSE;
      foreach ($decoded['translations'] as $langcode => $translation_data) {
        if (!$this->languageManager->getLanguage($langcode)) {
          continue;
        }
        if ($entity->hasTranslation($langcode)) {
          continue;
        }

        try {
          $translation = $entity->addTranslation($langcode, $entity->toArray());
          foreach ($translation_data as $field_name => $values) {
            if ($translation->hasField($field_name)) {
              $translation->set($field_name, $values);
            }
          }
          $changed = TRUE;
          $this->logger->info('Added @langcode translation for @type @uuid.', [
            '@langcode' => $langcode,
            '@type' => $entity_type_id,
            '@uuid' => $uuid,
          ]);
        }
        catch (\Exception $e) {
          $this->logger->warning('Could not add @langcode translation for @type @uuid: @error', [
            '@langcode' => $langcode,
            '@type' => $entity_type_id,
            '@uuid' => $uuid,
            '@error' => $e->getMessage(),
          ]);
        }
      }

      if ($changed) {
        $entity->save();
      }
    }
  }

  /**
   * Adds translations for a single language to all recipe content entities.
   *
   * Called from hook_configurable_language_insert to add translations only for
   * the language that was just enabled. Resets the language manager first
   * because the hook fires before ConfigurableLanguage::postSave() resets it.
   */
  public function mergeTranslationForLanguage(array $finder_data, string $langcode): void {
    $this->languageManager->reset();

    if (!$this->languageManager->getLanguage($langcode)) {
      return;
    }

    foreach ($finder_data as $decoded) {
      if (empty($decoded['translations'][$langcode])) {
        continue;
      }

      $uuid = $decoded['_meta']['uuid'] ?? NULL;
      $entity_type_id = $decoded['_meta']['entity_type'] ?? NULL;
      if (!$uuid || !$entity_type_id) {
        continue;
      }

      try {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $storage->resetCache();

        $entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $uuid);
        if (!$entity instanceof ContentEntityInterface) {
          continue;
        }

        if ($entity->hasTranslation($langcode)) {
          continue;
        }

        $translation = $entity->addTranslation($langcode, $entity->toArray());
        foreach ($decoded['translations'][$langcode] as $field_name => $values) {
          if ($translation->hasField($field_name)) {
            $translation->set($field_name, $values);
          }
        }
        $entity->save();
        $this->logger->info('Added @langcode translation for @type @uuid.', [
          '@langcode' => $langcode,
          '@type' => $entity_type_id,
          '@uuid' => $uuid,
        ]);
      }
      catch (\Exception $e) {
        $this->logger->warning('Could not add @langcode translation for @type @uuid: @error', [
          '@langcode' => $langcode,
          '@type' => $entity_type_id,
          '@uuid' => $uuid,
          '@error' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * Scans multilingual recipe directory for content with translations.
   *
   * @return array
   *   The decoded YAML data from all content files that have translations.
   */
  public static function loadRecipeContentData(): array {
    $recipe_path = \Drupal::root() . '/../recipes/dxpr_cms_multilingual/content';
    if (!is_dir($recipe_path)) {
      return [];
    }

    $data = [];
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($recipe_path, \FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
      if ($file->getExtension() !== 'yml') {
        continue;
      }
      $decoded = Yaml::decode(file_get_contents($file->getPathname()));
      if (!empty($decoded['translations'])) {
        $data[] = $decoded;
      }
    }
    return $data;
  }

}
