#!/bin/bash
source scripts/prepare-drupal-lint.sh

echo "---- Auto-fixing with Drupal standard... ----"
phpcbf --standard=Drupal \
  --extensions=php,module,inc,install,test,profile,theme,info,txt,md,yml \
  --ignore=node_modules,vendor,.github,web/core,web/libraries,web/modules/contrib,web/themes/contrib,web/sites \
  -v \
  .

echo "---- Auto-fixing with DrupalPractice standard... ----"
phpcbf --standard=DrupalPractice \
  --extensions=php,module,inc,install,test,profile,theme,info,txt,md,yml \
  --ignore=node_modules,vendor,.github,web/core,web/libraries,web/modules/contrib,web/themes/contrib,web/sites \
  -v \
  .