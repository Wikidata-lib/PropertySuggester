<?php

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;

interface SuggesterEngine {

	/**
	 * Returns suggested attributes
	 *
	 * @param PropertyId[] $propertyIds
	 *
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds );

	/**
	 * Returns suggested attributes
	 *
	 * @param Item $item
	 *
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item);
}
