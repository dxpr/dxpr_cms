<?php

declare(strict_types=1);

namespace Drupal\Tests\dxpr_cms_multilingual\Functional;

use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * @group dxpr_cms_multilingual
 */
class ComponentValidationTest extends BrowserTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter_test', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $dir = realpath(__DIR__ . '/../../..');

    // The recipe should apply cleanly.
    $this->applyRecipe($dir);
    // Apply it again to prove that it is idempotent.
    $this->applyRecipe($dir);

    $this->drupalCreateContentType(['type' => 'page']);
  }

  /**
   *
   */
  public function testHreflangAddedToTranslatedContent(): void {
    ConfigurableLanguage::createFromLangcode('fr')->save();

    $node = $this->drupalCreateNode(['type' => 'page']);
    $translation = $node->addTranslation('fr')->setTitle('Le traduction');
    $translation->save();

    $this->drupalGet($node->toUrl());
    // Although there are two languages enabled, there is also an "x-default"
    // alternate link. We don't need to inspect these links more closely; we can
    // trust the Hreflang module to do its job.
    $this->assertSession()->elementsCount('css', 'link[rel="alternate"][hreflang]', 3);
  }

}
