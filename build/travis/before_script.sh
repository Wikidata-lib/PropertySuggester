#! /bin/bash

set -x

originalDirectory=$(pwd)

cd ..

# checkout mediawiki
wget https://github.com/wikimedia/mediawiki-core/archive/$MW.tar.gz
tar -zxf $MW.tar.gz
rm $MW.tar.gz
mv mediawiki-core-$MW wiki

# checkout wikibase
wget https://github.com/wikimedia/mediawiki-extensions-Wikibase/archive/master.tar.gz
tar -zxf master.tar.gz
rm master.tar.gz
mv mediawiki-extensions-Wikibase-master wiki/extensions/Wikibase

cd wiki

mysql -e 'create database its_a_mw;'
php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin

cd extensions

cp -r $originalDirectory PropertySuggester

cd Wikibase
composer install --prefer-source

cd ../..

echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
echo 'ini_set("display_errors", 1);' >> LocalSettings.php
echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
echo '$wgLanguageCode = "'$LANG'";' >> LocalSettings.php

echo "define( 'WB_EXPERIMENTAL_FEATURES', true );" >> LocalSettings.php
echo 'require_once __DIR__ . "/extensions/Wikibase/repo/Wikibase.php";' >> LocalSettings.php
echo 'require_once __DIR__ . "/extensions/Wikibase/repo/ExampleSettings.php";' >> LocalSettings.php
echo 'require_once __DIR__ . "/extensions/Wikibase/client/WikibaseClient.php";' >> LocalSettings.php
echo 'require_once __DIR__ . "/extensions/PropertySuggester/PropertySuggester.php";' >> LocalSettings.php
echo '$wgWBClientSettings["siteGlobalID"] = "enwiki";' >> LocalSettings.php

php maintenance/update.php --quick
