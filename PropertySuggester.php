<?php
/**
 * PropertySuggester extension.
 * License: GNU GPL v2+
 */

if ( defined( 'PropertySuggester_VERSION' ) ) {
	// Do not initialize more then once.
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

spl_autoload_register( function ( $className ) {
	static $classes = false;
	if ( $classes === false ) {
		$classes = include( __DIR__ . '/' . 'PropertySuggester.classes.php' );
	}
	if ( array_key_exists( $className, $classes ) ) {
		include_once __DIR__ . '/' . $classes[$className];
	}
});

global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__  . '/PropertySuggester.alias.php';

global $wgMessagesDirs;
$wgMessagesDirs['PropertySuggester'] = __DIR__ . '/i18n';

global $wgSpecialPages;
$wgSpecialPages['PropertySuggester'] = 'PropertySuggester\SpecialEditItemSuggester';

global $wgSpecialPagesGroups;
$wgSpecialPageGroups['PropertySuggester'] = 'wikibaserepo';

global $wgAPIModules;
$wgAPIModules['wbsgetsuggestions'] = 'PropertySuggester\GetSuggestions';

global $wgHooks;
$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['UnitTestsList'][] = 'PropertySuggesterHooks::onUnitTestsList';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggesterHooks::onCreateSchema';

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
		'scripts'       => array( 'modules/ext.PropertySuggester.EntitySelector.js' ),
		'dependencies'  => array( 'jquery.wikibase.entityselector' ),
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'PropertySuggester',
);

$wgResourceModules['ext.PropertySuggester'] = array(
	'scripts'		=> array( 'modules/ext.PropertySuggester.js' ),
//	'styles'		=> array( 'modules/ext.PropertySuggester.css' ),
//	'dependencies'	=> array( 'ext.PropertySuggester.EntitySelector' ),
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'PropertySuggester',
);

global $wgPropertySuggesterDeprecatedIds;
$wgPropertySuggesterDeprecatedIds = array(
	107  // ( DEPRECATED main type )
);
