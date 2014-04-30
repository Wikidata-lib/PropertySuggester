<?php

namespace PropertySuggester\Suggesters;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * interface for (Property-)Suggester
 *
 * @licence GNU GPL v2+
 */
interface SuggesterEngine {

	/**
	 * Returns suggested attributes
	 *
	 * @param PropertyId[] $propertyIds
	 * @param int $limit
	 * @param float $minProbability
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds, $limit, $minProbability );

	/**
	 * Returns suggested attributes
	 *
	 * @param Item $item
	 * @param int $limit
	 * @param float $minProbability
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit, $minProbability );

	/**
	 * @param int[] $numericIds - blacklist used to filter suggestions
	 * @return void
	 */
	public function setDeprecatedPropertyIds( array $numericIds );

}
