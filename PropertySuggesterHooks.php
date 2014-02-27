<?php

final class PropertySuggesterHooks {
	/**
	 * Handler for the BeforePageDisplay hook, injects special behaviour
	 * for PropertySuggestions in the EntitySuggester
	 *
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if ( isset( $_GET['nosuggestions'] ) ) {
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
		$files = array_merge( $files, glob( __DIR__ . '/tests/phpunit/*Test.php' ) );
		return true;
	}

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function onCreateSchema( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'wbs_propertypairs',
			dirname( __FILE__ ) . '/createtable.sql', true );
		return true;
	}

}