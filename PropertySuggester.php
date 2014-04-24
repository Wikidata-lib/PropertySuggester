<?php
/**
 * PropertySuggester extension.
 * License: GNU GPL v2+
 */

require_once __DIR__ . '/vendor/autoload.php';

if ( defined( 'PropertySuggester_VERSION' ) ) {
	// Do not initialize more than once.
	return;
}

define( 'PropertySuggester_VERSION', '0.1' );

global $wgExtensionCredits;
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'PropertySuggester',
	'author' => array( 'Christian Dullweber', 'Moritz Finke', 'Felix Niemeyer', 'Virginia Weidhaas' ),
	'url' => 'https://github.com/Wikidata-lib/PropertySuggester',
	'descriptionmsg' => 'propertysuggester-desc'
);

global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__ . '/PropertySuggester.alias.php';

global $wgMessagesDirs;
$wgMessagesDirs['PropertySuggester'] = __DIR__ . '/i18n';

global $wgAPIModules;
$wgAPIModules['wbsgetsuggestions'] = 'PropertySuggester\GetSuggestions';

global $wgHooks;
$wgHooks['BeforePageDisplay'][] = 'PropertySuggester\PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['UnitTestsList'][] = 'PropertySuggester\PropertySuggesterHooks::onUnitTestsList';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggester\PropertySuggesterHooks::onCreateSchema';

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
	'scripts'       => array( 'modules/ext.PropertySuggester.EntitySelector.js' ),
	'dependencies'  => array( 'jquery.wikibase.entityselector' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'PropertySuggester',
);


global $wgPropertySuggesterDeprecatedIds;
$wgPropertySuggesterDeprecatedIds = array(
	107 // ( DEPRECATED main type )
);
global $wgPropertySuggesterMinProbability;
$wgPropertySuggesterMinProbability = 0.05;
