<?php

namespace PropertySuggester\Suggester;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use PropertySuggester\Suggestion;

/**
 * interface for (Property-)Suggester
 *
 * @licence GNU GPL v2+
 */
interface EntitySuggester {
	/**
	* @return Suggestion[]
	*/
	public function &getSuggestions();
}
