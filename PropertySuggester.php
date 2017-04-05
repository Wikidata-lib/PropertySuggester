<?php
/**
 * PropertySuggester extension.
 * License: GNU GPL v2+
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PropertySuggester', __DIR__ . '/extension.json' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PropertySuggester'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__ . '/PropertySuggester.alias.php';
	/*wfWarn(
		'Deprecated PHP entry point used for PropertySuggester extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the PropertySuggester extension requires MediaWiki 1.25+' );
}
