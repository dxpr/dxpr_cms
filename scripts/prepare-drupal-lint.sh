#!/bin/bash
set -vo pipefail

TARGET_DRUPAL_CORE_VERSION=${TARGET_DRUPAL_CORE_VERSION:-11}

# Install PHP CodeSniffer and Drupal coding standards
composer global require drupal/coder:8.3.* squizlabs/php_codesniffer:3.* phpcompatibility/php-compatibility:^9

# Register Drupal coding standards
phpcs --config-set installed_paths /tmp/vendor/drupal/coder/coder_sniffer,/tmp/vendor/phpcompatibility/php-compatibility

# Verify installed standards
phpcs -i