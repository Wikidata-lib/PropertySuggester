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

}