<?php
/**
 * PropertySuggester extension.
 * License: WTFPL 2.0
 */

if ( defined( 'PropertySuggester_VERSION' ) ) {
	// Do not initialize more then once.
	return;
}

define( 'PropertySuggester_VERSION', '0.9' );

global $wgExtensionCredits;
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'PropertySuggester',
	'author' => array( 'BP2013N2' ),
	'url' => 'https://mediawiki.org/wiki/Extension:PropertySuggester',
	'descriptionmsg' => 'propertysuggester-desc',
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

global $wgSpecialPages;
$wgSpecialPages['PropertySuggester']			= 'PropertySuggester\SpecialSuggester';

global $wgSpecialPagesGroups;
$wgSpecialPageGroups['PropertySuggester']		= 'wikibaserepo';

global $wgAPIModules;
$wgAPIModules['wbsgetsuggestions']				= 'PropertySuggester\GetSuggestions';

global $wgHooks;
$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['UnitTestsList'][] = 'PropertySuggesterHooks::onUnitTestsList';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggesterHooks::onCreateSchema';

global $wgResourceModules;
$wgResourceModules['ext.PropertySuggester'] = array(
		'scripts'		=> array( 'modules/ext.PropertySuggester.js' ),
		'styles'		=> 'modules/ext.PropertySuggester.css',
		'messages'		=> array(),
		'dependencies'	=> array( 'ext.PropertySuggester.EntitySelector' ),
		'localBasePath'	=> __DIR__,
		'remoteExtPath'	=> 'PropertySuggester',
);

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
		'scripts'		=> array( 'modules/ext.PropertySuggester.EntitySelector.js' ),
		'styles'		=> array(),
		'messages'		=> array(),
		'dependencies'	=> array( 'jquery.wikibase.entityselector' ),
		'localBasePath'	=> __DIR__,
		'remoteExtPath'	=> 'PropertySuggester',
);

