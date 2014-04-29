<?php


namespace PropertySuggester\ValueSuggesters;

use PropertySuggester\Suggesters\Suggestion;
use Wikibase\EntityLookup;
use Wikibase\TermIndex;


/**
 * Turns Arrays of ValueSuggestions into other formats
 *
 * Class ValueSuggestionArrayTransformer
 * @licence GNU GPL v2+
 * @package PropertySuggester\ValueSuggester
 */
class ValueSuggestionArrayTransformer {
	/**
	 * @param EntityLookup $lookup
	 * @param TermIndex $termIndex
	 * @param Suggestion[] $suggestions
	 * @return string
	 */
	static function &toJson( EntityLookup &$lookup, TermIndex &$termIndex, array &$suggestions)
	{

	}
} 