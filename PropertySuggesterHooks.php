<?php

use Wikibase\DataModel\Entity\Item;
use Wikibase\Repo\WikibaseRepo;

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

		$entityNamespaceLookup = WikibaseRepo::getDefaultInstance()->getEntityNamespaceLookup();
		$itemNamespace = $entityNamespaceLookup->getEntityNamespace( Item::ENTITY_TYPE );

		if ( $itemNamespace === false ) {
			// try looking up namespace by content model, for any instances of PropertySuggester
			// running with older Wikibase prior to ef622b1bc.
			$itemNamespace = $entityNamespaceLookup->getEntityNamespace(
				CONTENT_MODEL_WIKIBASE_ITEM
			);
		}

		if ( $out->getTitle()->getNamespace() !== $itemNamespace ) {
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
