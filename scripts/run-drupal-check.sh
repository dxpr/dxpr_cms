#!/bin/bash
set -vo pipefail

DRUPAL_RECOMMENDED_PROJECT=${DRUPAL_RECOMMENDED_PROJECT:-11.2.x-dev}
PHP_EXTENSIONS="gd"
DRUPAL_CHECK_TOOL="mglaman/drupal-check"

# Install required PHP extensions
for ext in $PHP_EXTENSIONS; do
  if ! php -m | grep -q $ext; then
    apk update && apk add --no-cache ${ext}-dev
    docker-php-ext-install $ext
  fi
done

# Create Drupal project if it doesn't exist
if [ ! -d "/drupal" ]; then
  composer create-project drupal/recommended-project=$DRUPAL_RECOMMENDED_PROJECT drupal --no-interaction --stability=dev
fi

cd drupal
mkdir -p web/modules/custom web/profiles/custom web/themes/custom

# Symlink custom code directories if they exist
if [ -d "/src/web/modules/custom" ]; then
  ln -sfn /src/web/modules/custom/* web/modules/custom/
fi

if [ -d "/src/web/profiles/custom" ]; then
  ln -sfn /src/web/profiles/custom/* web/profiles/custom/
fi

if [ -d "/src/web/themes/custom" ]; then
  ln -sfn /src/web/themes/custom/* web/themes/custom/
fi

if [ -d "/src/recipes" ]; then
  ln -sfn /src/recipes .
fi

# Install drupal-check
composer require $DRUPAL_CHECK_TOOL --dev

# Run drupal-check on custom code
if [ -d "web/modules/custom" ] && [ "$(ls -A web/modules/custom)" ]; then
  echo "---- Checking custom modules ----"
  ./vendor/bin/drupal-check --drupal-root . -ad web/modules/custom
fi

if [ -d "web/profiles/custom" ] && [ "$(ls -A web/profiles/custom)" ]; then
  echo "---- Checking custom profiles ----"
  ./vendor/bin/drupal-check --drupal-root . -ad web/profiles/custom
fi

if [ -d "web/themes/custom" ] && [ "$(ls -A web/themes/custom)" ]; then
  echo "---- Checking custom themes ----"
  ./vendor/bin/drupal-check --drupal-root . -ad web/themes/custom
fi

if [ -d "recipes" ] && [ "$(ls -A recipes)" ]; then
  echo "---- Checking recipes ----"
  ./vendor/bin/drupal-check --drupal-root . -ad recipes
fi