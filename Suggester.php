<?php
/**
 * Suggester extension.
 * License: WTFPL 2.0
 */
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Suggester',
	'author' => array( 'BP2013N2' ),
	'url' => 'https://mediawiki.org/wiki/Extension:Suggester',
	'descriptionmsg' => 'suggester-desc',
);

$dir = __DIR__ . '/';

$wgExtensionMessagesFiles['Suggester'] = $dir . 'Suggester.i18n.php';
$wgExtensionMessagesFiles['SuggesterAlias']  = $dir . 'Suggester.alias.php';

$wgAutoloadClasses['SpecialSuggester'] = $dir . 'SpecialSuggester.php';

$wgSpecialPages['Suggester'] = 'SpecialSuggester';
$wgSpecialPageGroups['Suggester'] = 'wikibaserepo';

$wgResourceModules['ext.Suggester'] = array(
	'scripts' => array('modules/ext.Suggester.js'),
	'styles' => 'modules/ext.Suggester.css',
        'messages' => array(),
        'dependencies' => array('jquery.wikibase.entityselector'),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'property-suggester',
);

//$wgHooks['APIGetDescription'][] = 'efASAPIGetDescription';
