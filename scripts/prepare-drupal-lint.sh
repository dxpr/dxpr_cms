#!/bin/bash
set -vo pipefail

TARGET_DRUPAL_CORE_VERSION=${TARGET_DRUPAL_CORE_VERSION:-11}

# Allow required plugins before installing
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# Install PHP CodeSniffer first
composer global require squizlabs/php_codesniffer:3.*

# Then install other packages
composer global require drupal/coder:8.3.* phpcompatibility/php-compatibility:^9

# Add composer global bin to PATH
export PATH="$PATH:/tmp/.composer/vendor/bin"

# Register Drupal coding standards
/tmp/.composer/vendor/bin/phpcs --config-set installed_paths /tmp/.composer/vendor/drupal/coder/coder_sniffer,/tmp/.composer/vendor/phpcompatibility/php-compatibility

# Verify installed standards
/tmp/.composer/vendor/bin/phpcs -i