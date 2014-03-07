#! /bin/bash

set -x

cd ../wiki/tests/phpunit

php phpunit.php --debug --group PropertySuggester
