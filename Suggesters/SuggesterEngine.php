<?php

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Item;

include "Suggestion.php"; // TODO use autoload

interface SuggesterEngine {

	/**
	 * Returns suggested attributes
	 *
	 * @param PropertyId[] $propertyIds
	 * @param int $limit
	 *
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( $propertyIds, $limit = -1);

    /**
     * Returns suggested attributes
     *
     * @param Item $item
     * @param int $limit
     *
     * @return Suggestion[]
     */
	public function suggestByItem( Item $item, $limit = -1);
}
