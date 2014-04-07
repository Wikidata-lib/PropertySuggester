<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityLookup;

/**
 * API module helper to get property suggestions
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */
class GetSuggestionsHelper {

	/**
	 * @var EntityLookup
	 */
	private $lookup;

	/**
	 * @var SuggesterEngine
	 */
	private $suggester;

	public function __construct( EntityLookup $lookup, SuggesterEngine $suggester ) {
		$this->lookup = $lookup;
		$this->suggester = $suggester;
	}

    /**
     * Provide either an item id
     *
     * @param string $item
     * @return array
     */
    public function generateSuggestionsByItem( $item ) {
        $id = new  ItemId( $item );
        $item = $this->lookup->getEntity( $id );
        $suggestions = $this->suggester->suggestByItem( $item );
        return $suggestions;
    }

    /**
     * Provide comma separated list of property ids
     *
     * @param string $propertyList
     * @return Suggestion[]
     */
    public function generateSuggestionsByPropertyList( $propertyList ) {
        $splitList = explode( ',', $propertyList );
        $properties = array();
        foreach ( $splitList as $id ) {
            $properties[] = PropertyId::newFromNumber( $this->getNumericPropertyId( $id ) );
        }
        $suggestions = $this->suggester->suggestByPropertyIds( $properties );
        return $suggestions;
    }

	/**
	 * Accepts strings of the format "P123" or "123" and returns
	 * the id as int. Returns 0 if the string is not of the specified format.
	 *
	 * @param string $propertyId
	 * @return int
	 */
	protected function getNumericPropertyId( $propertyId ) {
		if ( $propertyId[0] === 'P' ) {
			return (int)substr( $propertyId, 1 );
		}
		return (int)$propertyId;
	}

}
