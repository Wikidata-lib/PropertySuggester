<?php

namespace PropertySuggester\ValueSuggester;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\EntityId;

interface ValueSuggesterEngine {

	/**
	 * @param EntityId $itemId
	 * @param PropertyId $itemId
	 * @param $minProbability float
	 * @return ValueSuggestion[]
	 */
	public function getValueSuggestions( $itemId, $propertyId,  $minProbability );

}

