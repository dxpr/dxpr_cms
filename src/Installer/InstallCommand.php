<?php

declare(strict_types=1);

namespace DxprCms\Installer;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Interactive TUI installer for DXPR CMS.
 *
 * Provides a step-by-step wizard that mirrors the GUI installer,
 * plus a non-interactive mode for CI/scripting. Translates inputs
 * to drush site-install form key syntax and executes end-to-end.
 */
#[AsCommand(name: 'install', description: 'Install DXPR CMS with an interactive wizard or CLI options')]
class InstallCommand extends Command {

  /**
   * Optional recipes available in the DXPR CMS installer.
   */
  private const AVAILABLE_RECIPES = [
    'Case Studies',
    'Events',
    'Forms',
    'Google Analytics',
    'News',
    'SEO Tools',
    'Multilingual',
  ];

  /**
   * Common languages for the multilingual selection step.
   *
   * Keys are ISO 639-1 codes matching Drupal's LanguageManager.
   */
  private const LANGUAGE_OPTIONS = [
    'ar' => 'Arabic',
    'bg' => 'Bulgarian',
    'ca' => 'Catalan',
    'cs' => 'Czech',
    'da' => 'Danish',
    'de' => 'German',
    'el' => 'Greek',
    'es' => 'Spanish',
    'fa' => 'Persian',
    'fi' => 'Finnish',
    'fr' => 'French',
    'he' => 'Hebrew',
    'hi' => 'Hindi',
    'hr' => 'Croatian',
    'hu' => 'Hungarian',
    'id' => 'Indonesian',
    'it' => 'Italian',
    'ja' => 'Japanese',
    'ko' => 'Korean',
    'nb' => 'Norwegian Bokmal',
    'nl' => 'Dutch',
    'pl' => 'Polish',
    'pt-br' => 'Portuguese, Brazil',
    'pt-pt' => 'Portuguese, Portugal',
    'ro' => 'Romanian',
    'ru' => 'Russian',
    'sk' => 'Slovak',
    'sv' => 'Swedish',
    'th' => 'Thai',
    'tr' => 'Turkish',
    'uk' => 'Ukrainian',
    'vi' => 'Vietnamese',
    'zh-hans' => 'Chinese, Simplified',
    'zh-hant' => 'Chinese, Traditional',
  ];

  private string $projectRoot;
  private string $siteName = 'DXPR CMS';
  private ?string $apiKey = null;
  private array $selectedRecipes = [];
  private bool $allRecipes = false;
  private array $languages = [];
  private ?string $adminEmail = null;
  private ?string $adminPass = null;
  private ?string $dbUrl = null;

  protected function configure(): void {
    $this
      ->addOption('recipes', null, InputOption::VALUE_REQUIRED, 'Comma-separated recipe names (e.g. "Case Studies,Events,News")')
      ->addOption('all-recipes', null, InputOption::VALUE_NONE, 'Install all optional recipes')
      ->addOption('site-name', null, InputOption::VALUE_REQUIRED, 'Site name', 'DXPR CMS')
      ->addOption('api-key', null, InputOption::VALUE_REQUIRED, 'DXPR API key (JWT token)')
      ->addOption('skip-api-key', null, InputOption::VALUE_NONE, 'Skip API key step (dev mode)')
      ->addOption('languages', null, InputOption::VALUE_REQUIRED, 'Comma-separated language codes (e.g. "nl,de,fr")')
      ->addOption('admin-email', null, InputOption::VALUE_REQUIRED, 'Admin email address', 'admin@example.com')
      ->addOption('admin-pass', null, InputOption::VALUE_REQUIRED, 'Admin password (auto-generated if omitted)')
      ->addOption('db-url', null, InputOption::VALUE_REQUIRED, 'Database URL (auto-detected in DDEV)')
      ->addOption('multisite', null, InputOption::VALUE_NONE, 'Install as a Drupal multisite on an existing codebase')
      ->addOption('sites-subdir', null, InputOption::VALUE_REQUIRED, 'Sites subdirectory for multisite (e.g. "site2")')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Print the drush command without executing')
      ->setHelp(<<<'HELP'
        The <info>dxpr-install</info> command installs DXPR CMS end-to-end.

        <comment>Interactive wizard (default when run in a terminal):</comment>
          <info>bin/dxpr-install</info>

        <comment>Non-interactive with all recipes:</comment>
          <info>bin/dxpr-install --api-key=eyJ... --all-recipes --no-interaction</info>

        <comment>Non-interactive with specific recipes and languages:</comment>
          <info>bin/dxpr-install --api-key=eyJ... --recipes="News,Multilingual" --languages="nl,de" --no-interaction</info>

        <comment>Dry run (print drush command without executing):</comment>
          <info>bin/dxpr-install --api-key=eyJ... --all-recipes --dry-run</info>

        Get your free API key at https://app.dxpr.com/getting-started
        HELP);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $this->projectRoot = $this->findProjectRoot();

    $io->title('DXPR CMS Installer');

    // Phase 1: Pre-flight checks.
    try {
      $this->runPreflightChecks($io);
    }
    catch (\RuntimeException $e) {
      $io->error($e->getMessage());
      return Command::FAILURE;
    }

    // Phase 2: Resolve database URL.
    $this->resolveDbUrl($input, $io);
    if ($this->dbUrl === null && !$input->getOption('dry-run')) {
      $io->error('Database URL is required. Use --db-url or run inside DDEV.');
      return Command::FAILURE;
    }

    // Phase 3: Gather inputs (wizard or CLI options).
    try {
      $this->gatherInputs($input, $io);
    }
    catch (\RuntimeException $e) {
      $io->error($e->getMessage());
      return Command::FAILURE;
    }

    // Phase 4: Validate API key.
    if (!$input->getOption('skip-api-key') && $this->apiKey !== null) {
      try {
        $payload = $this->decodeJwtPayload($this->apiKey);
        $io->text(sprintf('  API key validated (tier: %s)', $payload['dxpr_tier'] ?? 'unknown'));
      }
      catch (\InvalidArgumentException $e) {
        $io->error('API key validation failed: ' . $e->getMessage());
        return Command::FAILURE;
      }
    }

    // Phase 5: Build drush command.
    $cmd = $this->buildDrushCommand($input);

    // Phase 6: Dry run — just print the command.
    if ($input->getOption('dry-run')) {
      $io->section('Dry run — would execute');
      $io->text($this->formatCommandForDisplay($cmd));
      return Command::SUCCESS;
    }

    // Phase 7: Execute installation.
    $io->section('Installing DXPR CMS');
    $exitCode = $this->executeDrush($cmd, $io);

    // Phase 8: Post-install verification.
    // Run verification regardless of exit code — drush may return non-zero
    // for non-fatal warnings (e.g. missing translation files).
    $verified = $this->runPostInstallVerification($io);

    if ($exitCode !== 0 && !$verified) {
      $io->error('Installation failed (exit code: ' . $exitCode . ')');
      return Command::FAILURE;
    }

    if ($exitCode !== 0 && $verified) {
      $io->warning('Drush reported warnings (exit code: ' . $exitCode . ') but the site is functional.');
    }

    // Phase 9: Summary.
    $this->printSummary($io);

    return Command::SUCCESS;
  }

  /**
   * Validates the environment before installation.
   */
  private function runPreflightChecks(SymfonyStyle $io): void {
    $io->section('Pre-flight checks');
    $checks = [];

    // PHP version.
    if (PHP_VERSION_ID < 80200) {
      throw new \RuntimeException(sprintf('PHP 8.2+ required, found %s.', PHP_VERSION));
    }
    $checks[] = sprintf('PHP %s', PHP_VERSION);

    // Composer dependencies.
    if (!file_exists($this->projectRoot . '/vendor/autoload.php')) {
      throw new \RuntimeException("Composer dependencies not installed. Run 'composer install' first.");
    }
    $checks[] = 'Composer dependencies installed';

    // Drush.
    $drush = $this->findDrush();
    if ($drush === null) {
      throw new \RuntimeException('Cannot find drush executable.');
    }
    $checks[] = 'Drush found: ' . $drush;

    // Drupal codebase.
    if (!file_exists($this->projectRoot . '/web/core/lib/Drupal.php')) {
      throw new \RuntimeException('Drupal codebase not found at web/core/.');
    }
    $checks[] = 'Drupal codebase present';

    // Install profile.
    if (!file_exists($this->projectRoot . '/web/profiles/dxpr_cms_installer/dxpr_cms_installer.info.yml')) {
      throw new \RuntimeException('DXPR CMS installer profile not found.');
    }
    $checks[] = 'DXPR CMS installer profile present';

    foreach ($checks as $check) {
      $io->text('  [OK] ' . $check);
    }
  }

  /**
   * Resolves the database URL from options or DDEV environment.
   */
  private function resolveDbUrl(InputInterface $input, SymfonyStyle $io): void {
    if ($input->getOption('db-url')) {
      $this->dbUrl = $input->getOption('db-url');
      return;
    }

    if (getenv('IS_DDEV_PROJECT')) {
      $this->dbUrl = 'mysql://db:db@db:3306/db';
      $io->text('  [OK] DDEV detected — using default database');
      return;
    }

    // In interactive mode, prompt for it.
    if ($input->isInteractive()) {
      $helper = $this->getHelper('question');
      $question = new Question('Database URL (e.g. mysql://user:pass@host/dbname): ');
      $this->dbUrl = $helper->ask($input, $io, $question);
    }
  }

  /**
   * Collects all installation parameters via wizard or CLI options.
   */
  private function gatherInputs(InputInterface $input, SymfonyStyle $io): void {
    $isInteractive = $input->isInteractive() && !$input->getOption('dry-run');

    // --- Recipes ---
    if ($input->getOption('all-recipes')) {
      $this->allRecipes = true;
      $this->selectedRecipes = self::AVAILABLE_RECIPES;
    }
    elseif ($input->getOption('recipes')) {
      $this->selectedRecipes = array_map('trim', explode(',', $input->getOption('recipes')));
      // Validate recipe names.
      $invalid = array_diff($this->selectedRecipes, self::AVAILABLE_RECIPES);
      if (!empty($invalid)) {
        throw new \RuntimeException(sprintf(
          "Unknown recipes: %s\nAvailable: %s",
          implode(', ', $invalid),
          implode(', ', self::AVAILABLE_RECIPES)
        ));
      }
    }
    elseif ($isInteractive) {
      $this->selectedRecipes = $this->wizardRecipes($input, $io);
    }

    // --- Site name ---
    $this->siteName = $input->getOption('site-name');
    if ($isInteractive && $this->siteName === 'DXPR CMS') {
      $this->siteName = $io->ask('Site name', 'DXPR CMS') ?? 'DXPR CMS';
    }

    // --- API key ---
    $skipApiKey = $input->getOption('skip-api-key');
    if ($input->getOption('api-key')) {
      $this->apiKey = $input->getOption('api-key');
    }
    elseif (!$skipApiKey && $isInteractive) {
      $this->apiKey = $this->wizardApiKey($input, $io);
    }
    elseif (!$skipApiKey && !$isInteractive) {
      throw new \RuntimeException('--api-key is required (or use --skip-api-key for dev installs).');
    }

    // --- Languages ---
    if ($input->getOption('languages')) {
      $this->languages = array_map('trim', explode(',', $input->getOption('languages')));
    }
    elseif ($isInteractive && in_array('Multilingual', $this->selectedRecipes, true)) {
      $this->languages = $this->wizardLanguages($input, $io);
    }

    // Auto-add Multilingual recipe if languages specified.
    if (!empty($this->languages) && !in_array('Multilingual', $this->selectedRecipes, true)) {
      $this->selectedRecipes[] = 'Multilingual';
      $io->note('Automatically including Multilingual recipe because languages were specified.');
    }

    // --- Admin credentials ---
    $this->adminEmail = $input->getOption('admin-email');
    $this->adminPass = $input->getOption('admin-pass');
    if ($isInteractive && $this->adminEmail === 'admin@example.com') {
      $this->adminEmail = $io->ask('Admin email', 'admin@example.com') ?? 'admin@example.com';
    }
    if ($isInteractive && $this->adminPass === null) {
      $this->adminPass = $this->wizardPassword($input, $io);
    }

    // Print summary of collected inputs.
    $io->section('Installation configuration');
    $io->definitionList(
      ['Site name' => $this->siteName],
      ['Recipes' => empty($this->selectedRecipes) ? '(none — base install)' : implode(', ', $this->selectedRecipes)],
      ['Languages' => empty($this->languages) ? 'English only' : implode(', ', $this->languages)],
      ['API key' => $this->apiKey ? 'Provided' : ($skipApiKey ? 'Skipped' : 'Not set')],
      ['Admin email' => $this->adminEmail],
      ['Database' => $this->dbUrl ?? '(not set)'],
    );
  }

  /**
   * Interactive recipe selection wizard step.
   */
  private function wizardRecipes(InputInterface $input, SymfonyStyle $io): array {
    $io->section('Step 1 of 5: Select optional add-ons');
    $io->text('Choose which optional features to install (press Enter to skip):');

    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion(
      'Select add-ons (comma-separated numbers, or Enter for none)',
      self::AVAILABLE_RECIPES,
    );
    $question->setMultiselect(true);
    $question->setErrorMessage('Invalid selection: %s');

    // Allow empty selection (just Enter).
    try {
      $selected = $helper->ask($input, $io, $question);
    }
    catch (\Exception) {
      $selected = [];
    }

    if (empty($selected)) {
      $io->text('  No add-ons selected (base install only)');
    }
    else {
      $io->text('  Selected: ' . implode(', ', $selected));
    }

    return $selected;
  }

  /**
   * Interactive API key wizard step with validation and retry.
   */
  private function wizardApiKey(InputInterface $input, SymfonyStyle $io): ?string {
    $io->section('Step 3 of 5: DXPR API Key');
    $io->text([
      'Enter your DXPR API key for AI features (OpenAI, Claude, Gemini, etc.).',
      'Get your free key at: https://app.dxpr.com/getting-started',
      '',
    ]);

    $helper = $this->getHelper('question');
    $attempts = 0;
    $maxAttempts = 3;

    while ($attempts < $maxAttempts) {
      $question = new Question('DXPR API key (or press Enter to skip): ');
      $question->setHidden(true);
      $question->setHiddenFallback(false);
      $key = $helper->ask($input, $io, $question);

      if (empty($key)) {
        $io->text('  API key skipped — AI features will not be configured.');
        return null;
      }

      try {
        $this->decodeJwtPayload($key);
        $io->text('  API key validated successfully.');
        return $key;
      }
      catch (\InvalidArgumentException $e) {
        $attempts++;
        if ($attempts < $maxAttempts) {
          $io->warning($e->getMessage() . " (attempt $attempts/$maxAttempts)");
        }
        else {
          $io->error($e->getMessage());
          throw new \RuntimeException('API key validation failed after ' . $maxAttempts . ' attempts.');
        }
      }
    }

    return null;
  }

  /**
   * Interactive language selection wizard step.
   */
  private function wizardLanguages(InputInterface $input, SymfonyStyle $io): array {
    $io->section('Step 4 of 5: Additional languages');
    $io->text('Select languages to install (in addition to English):');

    // Build display list: "nl — Dutch", "de — German", etc.
    $choices = [];
    foreach (self::LANGUAGE_OPTIONS as $code => $name) {
      $choices[$code] = "$code — $name";
    }

    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion(
      'Select languages (comma-separated numbers, or Enter for none)',
      array_values($choices),
    );
    $question->setMultiselect(true);

    try {
      $selected = $helper->ask($input, $io, $question);
    }
    catch (\Exception) {
      return [];
    }

    // Map display names back to language codes.
    $codes = [];
    $codesByDisplay = array_flip($choices);
    foreach ($selected as $display) {
      if (isset($codesByDisplay[$display])) {
        $codes[] = $codesByDisplay[$display];
      }
    }

    if (!empty($codes)) {
      $io->text('  Languages: ' . implode(', ', $codes));
    }

    return $codes;
  }

  /**
   * Interactive password wizard with confirmation.
   */
  private function wizardPassword(InputInterface $input, SymfonyStyle $io): ?string {
    $io->section('Step 5 of 5: Admin account');

    $helper = $this->getHelper('question');

    $question = new Question('Admin password (or Enter for auto-generated): ');
    $question->setHidden(true);
    $question->setHiddenFallback(false);
    $pass = $helper->ask($input, $io, $question);

    if (empty($pass)) {
      $io->text('  Password will be auto-generated by drush.');
      return null;
    }

    // Confirm password.
    $confirm = new Question('Confirm password: ');
    $confirm->setHidden(true);
    $confirm->setHiddenFallback(false);
    $pass2 = $helper->ask($input, $io, $confirm);

    if ($pass !== $pass2) {
      throw new \RuntimeException('Passwords do not match.');
    }

    return $pass;
  }

  /**
   * Validates a JWT token without Drupal services.
   *
   * Mirrors the logic from DxprBuilderJWTDecoder::decodeJwt().
   *
   * @return array
   *   The decoded JWT payload.
   *
   * @throws \InvalidArgumentException
   *   If the JWT is invalid or missing required claims.
   */
  private function decodeJwtPayload(string $jwt): array {
    $jwt = trim($jwt);
    $parts = explode('.', $jwt);

    if (count($parts) < 2) {
      throw new \InvalidArgumentException('Invalid JWT format — expected header.payload.signature');
    }

    $payload = json_decode(
      base64_decode(strtr($parts[1], '-_', '+/')),
      true,
    );

    if (!is_array($payload)) {
      throw new \InvalidArgumentException('Cannot decode JWT payload.');
    }

    if (empty($payload['sub']) || empty($payload['scope'])) {
      throw new \InvalidArgumentException(
        'Invalid DXPR key. Get your free key at https://dxpr.com/user/free-registration'
      );
    }

    if (empty($payload['dxpr_tier'])) {
      throw new \InvalidArgumentException(
        'Your key is outdated (missing dxpr_tier). Get a new key at https://app.dxpr.com/getting-started'
      );
    }

    return $payload;
  }

  /**
   * Builds the complete drush site-install command array.
   */
  private function buildDrushCommand(InputInterface $input): array {
    $drush = $this->findDrush();
    $cmd = [
      $drush,
      'site:install',
      'dxpr_cms_installer',
      '--yes',
    ];

    // Site name.
    $cmd[] = '--site-name=' . $this->siteName;

    // Admin credentials.
    if ($this->adminEmail) {
      $cmd[] = '--account-mail=' . $this->adminEmail;
    }
    if ($this->adminPass) {
      $cmd[] = '--account-pass=' . $this->adminPass;
    }

    // Database.
    if ($this->dbUrl) {
      $cmd[] = '--db-url=' . $this->dbUrl;
    }

    // Multisite.
    if ($input->getOption('sites-subdir')) {
      $cmd[] = '--sites-subdir=' . $input->getOption('sites-subdir');
    }

    // Recipes — positional args with form key syntax.
    if ($this->allRecipes) {
      $cmd[] = 'installer_recipes_form.add_ons=*';
    }
    elseif (!empty($this->selectedRecipes)) {
      $cmd[] = 'installer_recipes_form.add_ons=' . implode('|', $this->selectedRecipes);
    }

    // API key.
    if ($this->apiKey) {
      $cmd[] = 'dxpr_cms_installer_keys.dxpr_key=' . $this->apiKey;
    }

    // Languages — one form key arg per language.
    foreach ($this->languages as $langcode) {
      $cmd[] = sprintf(
        'dxpr_cms_installer_multilingual_configuration.additional_languages.%s=%s',
        $langcode,
        $langcode,
      );
    }

    return $cmd;
  }

  /**
   * Executes the drush installation command.
   */
  private function executeDrush(array $cmd, SymfonyStyle $io): int {
    $process = new Process($cmd, $this->projectRoot);
    $process->setTimeout(600);

    $process->run(function (string $type, string $buffer) use ($io): void {
      // Stream output in real time.
      $io->write($buffer);
    });

    return $process->getExitCode();
  }

  /**
   * Runs post-installation health checks.
   */
  private function runPostInstallVerification(SymfonyStyle $io): bool {
    $io->section('Post-install verification');
    $drush = $this->findDrush();
    $siteWorks = false;

    // 1. Drush status.
    $status = new Process([$drush, 'status', '--format=json'], $this->projectRoot);
    $status->setTimeout(30);
    $status->run();
    if ($status->isSuccessful()) {
      $data = json_decode($status->getOutput(), true);
      $io->text(sprintf('  [OK] Drupal %s installed', $data['drupal-version'] ?? '?'));
      $io->text(sprintf('  [OK] Database: %s', $data['db-name'] ?? '?'));
      $io->text(sprintf('  [OK] Default theme: %s', $data['theme'] ?? '?'));
      $siteWorks = !empty($data['drupal-version']);
    }
    else {
      $io->warning('  Could not verify drush status.');
    }

    // 2. Check key modules.
    $pml = new Process([$drush, 'pm:list', '--status=enabled', '--no-core', '--format=json'], $this->projectRoot);
    $pml->setTimeout(30);
    $pml->run();
    if ($pml->isSuccessful()) {
      $modules = json_decode($pml->getOutput(), true) ?: [];
      $expected = ['dxpr_builder', 'dxpr_theme_helper'];
      foreach ($expected as $module) {
        if (isset($modules[$module])) {
          $io->text("  [OK] $module enabled");
        }
        else {
          $io->text("  [WARN] $module not found");
        }
      }
    }

    // 3. Admin theme.
    $theme = new Process([$drush, 'config:get', 'system.theme', 'admin', '--format=json'], $this->projectRoot);
    $theme->setTimeout(15);
    $theme->run();
    if ($theme->isSuccessful()) {
      $val = json_decode($theme->getOutput(), true);
      $adminTheme = $val['system.theme:admin'] ?? 'unknown';
      $io->text("  [OK] Admin theme: $adminTheme");
    }

    // 4. No install profile (should be uninstalled after install).
    $profile = new Process([$drush, 'core:status', '--field=install-profile'], $this->projectRoot);
    $profile->setTimeout(15);
    $profile->run();
    $profileName = trim($profile->getOutput());
    if (empty($profileName)) {
      $io->text('  [OK] Install profile uninstalled (expected)');
    }
    else {
      $io->text("  [INFO] Install profile: $profileName");
    }

    return $siteWorks;
  }

  /**
   * Prints the installation summary.
   */
  private function printSummary(SymfonyStyle $io): void {
    $io->newLine();
    $io->success('DXPR CMS installed successfully!');

    $rows = [
      ['Site name', $this->siteName],
      ['Recipes', $this->allRecipes ? 'All (' . count(self::AVAILABLE_RECIPES) . ')' : (empty($this->selectedRecipes) ? 'None (base install)' : implode(', ', $this->selectedRecipes))],
      ['Languages', empty($this->languages) ? 'English only' : implode(', ', $this->languages)],
      ['Admin email', $this->adminEmail ?: 'admin@example.com'],
      ['API key', $this->apiKey ? 'Configured' : 'Skipped'],
    ];
    $io->table(['Setting', 'Value'], $rows);

    // Next steps.
    $io->section('Next steps');
    $url = getenv('DDEV_PRIMARY_URL') ?: 'http://your-site.ddev.site';
    $io->listing([
      "Open your site: $url",
      'Theme settings:  drush dxt:config:list',
      'Page templates:  drush dxb:template:list',
      'Color palette:   drush dxt:palette:get',
    ]);
  }

  /**
   * Finds the project root directory.
   */
  private function findProjectRoot(): string {
    // Start from the script location and walk up to find composer.json.
    $dir = dirname(__DIR__, 2);
    if (file_exists($dir . '/composer.json')) {
      return $dir;
    }
    // Fallback: try from the entry script.
    $scriptDir = realpath($_SERVER['SCRIPT_FILENAME'] ?? __FILE__);
    if ($scriptDir) {
      $dir = dirname($scriptDir, 2);
      if (file_exists($dir . '/composer.json')) {
        return $dir;
      }
    }
    // Last resort: current working directory.
    return getcwd() ?: '.';
  }

  /**
   * Finds the drush executable.
   */
  private function findDrush(): ?string {
    // Inside DDEV, drush is on the PATH.
    if (getenv('IS_DDEV_PROJECT')) {
      return 'drush';
    }

    $vendorDrush = $this->projectRoot . '/vendor/bin/drush';
    if (file_exists($vendorDrush) && is_executable($vendorDrush)) {
      return $vendorDrush;
    }

    // Try php vendor/bin/drush as fallback.
    if (file_exists($vendorDrush)) {
      return $vendorDrush;
    }

    return null;
  }

  /**
   * Formats a command array for display (with line breaks for readability).
   */
  private function formatCommandForDisplay(array $cmd): string {
    $lines = [];
    foreach ($cmd as $i => $arg) {
      if ($i === 0) {
        $lines[] = $arg . ' \\';
      }
      elseif ($i === count($cmd) - 1) {
        $lines[] = '  ' . escapeshellarg($arg);
      }
      else {
        $lines[] = '  ' . escapeshellarg($arg) . ' \\';
      }
    }
    return implode("\n", $lines);
  }

}
