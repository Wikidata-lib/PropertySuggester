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

$dir = __DIR__ . '/';

$wgExtensionMessagesFiles['PropertySuggester']      = $dir . 'PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = $dir . 'PropertySuggester.alias.php';

$wgAutoloadClasses['SpecialSuggester']          = $dir . 'SpecialSuggester.php';
$wgAutoloadClasses['GetSuggestions']            = $dir . 'GetSuggestions.php';
$wgAutoloadClasses['GetMisfits']                = $dir . 'GetMisfits.php';
$wgAutoloadClasses['PropertySuggesterHooks']    = $dir . 'PropertySuggesterHooks.php';


$wgSpecialPages['PropertySuggester']            = 'SpecialSuggester';
$wgSpecialPageGroups['PropertySuggester']       = 'wikibaserepo';

$wgAPIModules['wbsgetsuggestions']              = 'GetSuggestions';
$wgAPIModules['wbsgetmisfits']                  = 'GetMisfits';

$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';

$wgResourceModules['ext.PropertySuggester'] = array(
        'scripts' => array('modules/ext.PropertySuggester.js'),
        'styles' => 'modules/ext.PropertySuggester.css',
        'messages' => array(),
        'dependencies' => array('ext.PropertySuggester.EntitySelector'),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'PropertySuggester',
);

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
        'scripts' => array('modules/ext.PropertySuggester.EntitySelector.js'),
        'styles' => array(),
        'messages' => array(),
        'dependencies' => array('jquery.wikibase.entityselector'),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'PropertySuggester',
);

//$wgHooks['APIGetDescription'][] = 'efASAPIGetDescription';
