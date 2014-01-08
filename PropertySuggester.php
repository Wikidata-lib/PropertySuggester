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
	'descriptionmsg' => 'suggester-desc',
);

$dir = __DIR__ . '/';

$wgExtensionMessagesFiles['PropertySuggester']      = $dir . 'PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = $dir . 'PropertySuggester.alias.php';

$wgAutoloadClasses['SpecialSuggester']		= $dir . 'SpecialSuggester.php';
$wgAutoloadClasses['GetSuggestions']		= $dir . 'GetSuggestions.php';
$wgAutoloadClasses['GetMisfits']		= $dir . 'GetMisfits.php';

$wgSpecialPages['PropertySuggester']		= 'SpecialSuggester';
$wgSpecialPageGroups['PropertySuggester']	= 'wikibaserepo';

$wgAPIModules['wbsgetsuggestions'] 		= 'GetSuggestions';
$wgAPIModules['wbsgetmisfits']                  = 'GetMisfits';


$wgResourceModules['ext.PropertySuggester'] = array(
	'scripts' => array('modules/ext.PropertySuggester.js'),
	'styles' => 'modules/ext.PropertySuggester.css',
        'messages' => array(),
        'dependencies' => array('jquery.wikibase.entityselector'),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'PropertySuggester',
);

//$wgHooks['APIGetDescription'][] = 'efASAPIGetDescription';
