#! /bin/bash

set -x

# cd ../wiki/tests/phpunit
# php phpunit.php --debug --group PropertySuggester

cd ../wiki/extensions/PropertySuggester
php vendor/bin/phpunit -c phpunit.xml.dist
