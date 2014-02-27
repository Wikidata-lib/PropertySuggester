<?php
/**
 * PropertySuggester extension.
 * License: WTFPL 2.0
 */
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'PropertySuggester',
	'author' => array( 'BP2013N2' ),
	'url' => 'https://mediawiki.org/wiki/Extension:PropertySuggester',
	'descriptionmsg' => 'propertysuggester-desc',
);


$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__  . '/PropertySuggester.alias.php';

// TODO use composer for autoloading
$wgAutoloadClasses['PropertySuggesterHooks'] = __DIR__ . '/PropertySuggesterHooks.php';

$src = __DIR__ . '/src/PropertySuggester';
$wgAutoloadClasses['PropertySuggester\SpecialSuggester'] = $src . '/SpecialSuggester.php';
$wgAutoloadClasses['PropertySuggester\GetSuggestions'] = $src . '/GetSuggestions.php';

$wgAutoloadClasses['PropertySuggester\Suggesters\Suggestion'] = $src . '/Suggesters/Suggestion.php';
$wgAutoloadClasses['PropertySuggester\Suggesters\SuggesterEngine'] = $src . '/Suggesters/SuggesterEngine.php';
$wgAutoloadClasses['PropertySuggester\Suggesters\SimplePHPSuggester'] = $src . '/Suggesters/SimplePHPSuggester.php';

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
		'remoteExtPath' => 'PropertySuggester',
);

