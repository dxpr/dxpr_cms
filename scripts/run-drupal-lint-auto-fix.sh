#!/bin/bash
source scripts/prepare-drupal-lint.sh

echo "---- Auto-fixing with Drupal standard... ----"
/tmp/.composer/vendor/bin/phpcbf --standard=Drupal \
  --extensions=php,module,inc,install,test,profile,theme,info,txt,md,yml \
  --ignore=node_modules,vendor,.github,web/core,web/libraries,web/modules/contrib,web/themes/contrib,web/sites \
  -v \
  .

echo "---- Auto-fixing with DrupalPractice standard... ----"
/tmp/.composer/vendor/bin/phpcbf --standard=DrupalPractice \
  --extensions=php,module,inc,install,test,profile,theme,info,txt,md,yml \
  --ignore=node_modules,vendor,.github,web/core,web/libraries,web/modules/contrib,web/themes/contrib,web/sites \
  -v \
  .