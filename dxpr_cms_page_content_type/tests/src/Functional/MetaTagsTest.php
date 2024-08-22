<?php

declare(strict_types=1);

namespace Drupal\Tests\dxpr_cms_page\Functional;

use Composer\InstalledVersions;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * @group dxpr_cms_page
 */
class MetaTagsTest extends BrowserTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public function test(): void {
    $this->applyRecipe(InstalledVersions::getInstallPath('drupal/dxpr_cms_page_content_type'));

    // If we create a page, all the expected meta tags should be there.
    $node = $this->createNode([
      'type' => 'page',
      'body' => [
        'summary' => 'Not a random summary...',
        'value' => $this->getRandomGenerator()->paragraphs(1),
      ],
      'moderation_state' => 'published',
    ]);
    $this->drupalGet($node->toUrl());
    $assert_session = $this->assertSession();
    $assert_session->statusCodeEquals(200);
    $assert_session->elementAttributeContains('css', 'meta[name="description"]', 'content', 'Not a random summary...');
    $assert_session->elementAttributeContains('css', 'meta[property="og:description"]', 'content', 'Not a random summary...');
    $assert_session->elementAttributeContains('css', 'meta[property="og:title"]', 'content', $node->getTitle());
    $assert_session->elementAttributeContains('css', 'meta[property="og:type"]', 'content', $node->type->entity->label());
  }

}
