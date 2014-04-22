<?php

namespace PropertySuggester\ValueSuggester;

abstract class ValueSuggesterEngine {

	/**
	 * @param $statements
	 * @param $propertyId
	 * @param $minProbability
	 * @return mixed|\PropertySuggester\Suggesters\Suggestion[]
	 */
	abstract protected function &getValueSuggestionsByStatements( &$statements, $propertyId,  $minProbability );


	public function getValueSuggestionsByItem( $itemId, $propertyId,  $minProbability )
	{
		$statements = array( "(31,5)", "(106, 12204)");
		return $this->getValueSuggestionsByStatements($statements, $propertyId, $minProbability );
	}
}

