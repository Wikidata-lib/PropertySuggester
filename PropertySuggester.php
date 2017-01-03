<?php
/**
 * PropertySuggester extension.
 * License: GNU GPL v2+
 */

if ( defined( 'PropertySuggester_VERSION' ) ) {
	// Do not initialize more than once.
	return;
}

define( 'PropertySuggester_VERSION', '3.1.6' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

global $wgExtensionCredits;
$wgExtensionCredits['wikibase'][] = array(
	'path' => __FILE__,
	'name' => 'PropertySuggester',
	'author' => array( 'Christian Dullweber', 'Moritz Finke', 'Felix Niemeyer', 'Virginia Weidhaas' ),
	'url' => 'https://github.com/Wikidata-lib/PropertySuggester',
	'descriptionmsg' => 'propertysuggester-desc',
	'license-name' => 'GPL-2.0+'
);

global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__ . '/PropertySuggester.alias.php';

global $wgMessagesDirs;
$wgMessagesDirs['PropertySuggester'] = __DIR__ . '/i18n';

global $wgAPIModules;
$wgAPIModules['wbsgetsuggestions'] = 'PropertySuggester\GetSuggestions';

global $wgHooks;
$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggesterHooks::onCreateSchema';

$remoteExtPathParts = explode(
	DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR, __DIR__, 2
);

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
	'scripts'       => array( 'modules/ext.PropertySuggester.EntitySelector.js' ),
	'dependencies'  => array(
		'jquery.wikibase.entityselector',
		'jquery.wikibase.entityview',
		'jquery.wikibase.referenceview',
		'jquery.wikibase.statementview',
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => $remoteExtPathParts[1],
);

global $wgPropertySuggesterDeprecatedIds;
$wgPropertySuggesterDeprecatedIds = array(
	107 // ( DEPRECATED main type )
);

global $wgPropertySuggesterClassifyingPropertyIds;
$wgPropertySuggesterClassifyingPropertyIds = array(
	31 // instance of
);

global $wgPropertySuggesterInitialSuggestions;
$wgPropertySuggesterInitialSuggestions = array(
	31, // instance of
	279 // subclass of
);
global $wgPropertySuggesterMinProbability;
$wgPropertySuggesterMinProbability = 0.05;
