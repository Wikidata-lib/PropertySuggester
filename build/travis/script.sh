#! /bin/bash

set -x

cd ../wiki/tests/phpunit
php phpunit.php -c ../../extensions/PropertySuggester/phpunit.xml.dist

# cd ../wiki/extensions/PropertySuggester
# php vendor/bin/phpunit -c phpunit.xml.dist
