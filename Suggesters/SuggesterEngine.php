<?php

include "Suggestion.php";

interface SuggesterEngine {
	/**
	 * Returns suggested attributes
	 *
	 * @since 0.1
	 *
	 * @return Snak
	 */
	public function suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize );
	public function suggestionsByItem( $entity, $resultSize );
}
