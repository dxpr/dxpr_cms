<?php

namespace Drupal\dxpr_cms_installer;

use Drupal\RecipeKit\Installer\Hooks;

/**
 * Provides helper methods for the Dxpr CMS Installer.
 */
class DxprCmsInstallerHelper {

  /**
   * Wraps the StarterKit hooks alter functionality.
   *
   * StarterKit hooks forcefully set "en" as the default language, and
   * this method ensures that the original language preference is preserved
   * and restored after the hook alteration process.
   *
   * @param array $tasks
   *   The array of tasks to be altered by the hooks.
   * @param array $install_state
   *   An associative array representing the installation state.
   */
  public static function wrapStarterKitHooksAlter(array &$tasks, array &$install_state): void {
    // StarterKit hooks force set the "en" as a default language, but we want
    // to allow users to change it.
    // For this we always cache the default language before hook alter
    // execution and then restore the value.
    $default_langcode = $GLOBALS['install_state']['parameters']['langcode'] ?? NULL;
    Hooks::installTasksAlter($tasks, $install_state);
    $GLOBALS['install_state']['parameters']['langcode'] = $default_langcode;
  }

}
