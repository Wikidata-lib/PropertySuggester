#! /bin/bash

set -x

cd ../wiki/tests/phpunit

# php phpunit.php --group PropertySuggester
# groups don't work?!

php phpunit.php --debug --group PropertySuggester
