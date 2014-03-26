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

$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__  . '/PropertySuggester.alias.php';

$wgSpecialPages['PropertySuggester']			= 'PropertySuggester\SpecialSuggester';
$wgSpecialPageGroups['PropertySuggester']		= 'wikibaserepo';

$wgAPIModules['wbsgetsuggestions']				= 'PropertySuggester\GetSuggestions';

$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['UnitTestsList'][] = 'PropertySuggesterHooks::onUnitTestsList';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggesterHooks::onCreateSchema';

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

