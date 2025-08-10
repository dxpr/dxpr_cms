<?php

declare(strict_types=1);

namespace Drupal\Tests\dxpr_cms_starter\Functional;

use Composer\InstalledVersions;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\BrowserTestBase;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @group dxpr_cms_starter
 */
class ComponentValidationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A version of RecipeTestTrait::applyRecipe() that doesn't time out.
   */
  private function applyRecipe(string $path, array $options = []): void {
    $arguments = [
      (new PhpExecutableFinder())->find(),
      'core/scripts/drupal',
      'recipe',
      // Never apply recipes interactively.
      '--no-interaction',
      ...$options,
      $path,
    ];
    $process = (new Process($arguments))
      ->setWorkingDirectory($this->getDrupalRoot())
      ->setEnv([
        'DRUPAL_DEV_SITE_PATH' => $this->siteDirectory,
        // Ensure that the command boots Drupal into a state where it knows it's
        // a test site.
        // @see drupal_valid_test_ua()
        'HTTP_USER_AGENT' => drupal_generate_test_ua($this->databasePrefix),
      ])
      ->setTimeout(0);

    $process->run();
    $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    // Applying a recipe:
    // - creates new checkpoints, hence the "state" service in the test runner
    //   is outdated
    // - may install modules, which would cause the entire container in the test
    //   runner to be outdated.
    // Hence the entire environment must be rebuilt for assertions to target the
    // actual post-recipe-application result.
    // @see \Drupal\Core\Config\Checkpoint\LinearHistory::__construct()
    $this->rebuildAll();
  }

  public function test(): void {
    $dir = dirname(__DIR__, 3);

    // Apply this recipe once. It is a site starter kit and therefore unlikely
    // to be applied again in the real world.
    ['install_path' => $project_root] = InstalledVersions::getRootPackage();
    $this->applyRecipe($project_root . '/recipes/' . basename($dir));

    // Read our `composer.json` file to get the list of optional recipes.
    $composer = file_get_contents($dir . '/composer.json');
    $composer = json_decode($composer, TRUE, flags: JSON_THROW_ON_ERROR);

    // Test that all the optional recipes can be applied on top of this one.
    $cookbook_dir = dirname($dir);
    $optional_recipes = $composer['suggest'] ?? [];
    foreach (array_keys($optional_recipes) as $name) {
      $this->applyRecipe($cookbook_dir . '/' . basename($name), [
        '--input=dxpr_cms_google_analytics.property_id=GTM-123456',
      ]);
    }

    $editor = $this->drupalCreateUser();
    $editor->addRole('content_editor')->save();
    $this->drupalLogin($editor);

    // The navigation should have a link to the dashboard.
    $this->drupalGet('<front>');
    $assert_session = $this->assertSession();
    $assert_session->elementAttributeContains('named', ['link', 'Dashboard'], 'class', 'toolbar-button--icon--navigation-dashboard');

    $node_types = $this->container->get(EntityTypeManagerInterface::class)
      ->getStorage('node_type')
      ->getQuery()
      ->execute();
    // There should be at least one content type.
    $this->assertNotEmpty($node_types);
    // All content types:
    // - Should not show a summary field on their edit form.
    // - Should only have one text format to choose from, so there should not
    //   be any choice.
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type);
      $assert_session->fieldNotExists('Summary');
      $assert_session->fieldNotExists('Text format');

      if ($node_type === 'page') {
        $page = $this->getSession()->getPage();
        $page->fillField('Title', 'Test page');
        $page->fillField('Description', "I'll do this later.");
        $assert_session->elementExists('css', '#edit-actions')->pressButton('Save');
        // Pages should have the expected path aliases.
        $assert_session->addressMatches('/\/test-page$/');
      }
    }
  }

}
