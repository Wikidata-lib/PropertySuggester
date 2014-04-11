<?php

namespace PropertySuggester\Suggesters;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;

interface SuggesterEngine {

	/**
	 * Returns suggested attributes
	 *
	 * @param PropertyId[] $propertyIds
	 * @param $limit int
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds, $limit );

	/**
	 * Returns suggested attributes
	 *
	 * @param Item $item
	 * @param int $limit
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit );

	/**
	 * @param int[] $numericIds
	 * @return void
	 */
	public function setDeprecatedPropertyIds( array $numericIds );

}
