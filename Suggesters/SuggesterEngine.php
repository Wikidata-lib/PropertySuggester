<?php

include "Suggestion.php";

interface SuggesterEngine {

	/**
	 * Returns suggested attributes
	 *
	 * @since 0.1
	 *
	 * @param array $attributeValuePairs
	 * @param $resultSize
	 *
	 * @return Suggestion[]
	 */
	public function suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize );
	public function suggestionsByItem( $entity, $resultSize );
}
