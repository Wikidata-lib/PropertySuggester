#! /bin/bash

cd ../wiki/extensions/PropertySuggester

ls build/logs

php vendor/bin/coveralls -v
