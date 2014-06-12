<?php

use Wikibase\NamespaceUtils;

final class PropertySuggesterHooks {

	/**
	 * Handler for the BeforePageDisplay hook, injects special behaviour
	 * for PropertySuggestions in the EntitySuggester (if page is in EntityNamespace)
	 *
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if ( $out->getRequest()->getCheck( 'nosuggestions' ) ) {
			return true;
		}
		$itemNs = NamespaceUtils::getEntityNamespace( CONTENT_MODEL_WIKIBASE_ITEM );
		$namespace = $out->getTitle()->getNamespace();
		if ( $namespace != $itemNs ) {
			return true;
		}

		$out->addModules( 'ext.PropertySuggester.EntitySelector' );
		return true;
	}

	/**
	 * @param $files
	 * @return bool
	 */
	public static function onUnitTestsList( &$files ) {
		// @codeCoverageIgnoreStart
		$directoryIterator = new RecursiveDirectoryIterator( __DIR__ . '/tests/phpunit/' );

		/* @var SplFileInfo $fileInfo */
		foreach ( new RecursiveIteratorIterator( $directoryIterator ) as $fileInfo ) {
			if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
				$files[] = $fileInfo->getPathname();
			}
		}
		return true;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function onCreateSchema( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'wbs_propertypairs',
			__DIR__ . '/sql/create_propertypairs.sql', true );
		return true;
	}

}
