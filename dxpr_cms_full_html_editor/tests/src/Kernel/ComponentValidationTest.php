<?php

declare(strict_types=1);

namespace Drupal\Tests\dxpr_cms_full_html_editor\Kernel;

use Composer\InstalledVersions;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @group dxpr_cms_full_html_editor
 */
class ComponentValidationTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    // These two modules are guaranteed to be installed on all sites.
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system', 'user']);
    $this->installEntitySchema('user');

    // Create an administrative user who can import default content in recipes.
    $this->assertSame('1', $this->createUser(admin: TRUE)->id());

    // Ensure the default theme is actually installed, as it would be on a real
    // site.
    $default_theme = $this->config('system.theme')->get('default');
    $this->container->get(ThemeInstallerInterface::class)->install([
      $default_theme,
    ]);
  }

  public function test(): void {
    $dir = InstalledVersions::getInstallPath('drupal/' . explode('\\', __NAMESPACE__)[2]);

    // If the recipe is not valid, an exception should be thrown here.
    $recipe = Recipe::createFromDirectory($dir);

    // The recipe should apply cleanly.
    RecipeRunner::processRecipe($recipe);
    // Apply it again to prove that it is idempotent.
    RecipeRunner::processRecipe($recipe);
  }

}
