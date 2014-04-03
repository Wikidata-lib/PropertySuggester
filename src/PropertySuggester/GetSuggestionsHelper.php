<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SimplePHPSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityLookup;
use Wikibase\StoreFactory;


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

	/**
	 * Filters for entries whose label or alias starts with $search
	 * An entry needs to have a field 'label' and an array 'aliases'.
	 *
	 * @param array $entries
	 * @param string $search
	 * @return array representing Json
	 */
	public function filterByPrefix( array &$entries, $search ) {
		$matchingEntries = array();
		foreach ( $entries as $entry ) {
			if ( $this->isMatch( $entry, $search ) ) {
				$matchingEntries[] = $entry;
			}
		}
		return $matchingEntries;
	}

	/**
	 * Checks if entry['label'] or entry['aliases'] starts with $search
	 *
	 * @param array $entry in Json representation
	 * @param string $search
	 * @return bool
	 */
	protected function isMatch( array $entry, $search ) {
		if ( $this->startsWith( $entry['label'], $search )) {
			return true;
		}
		if ( $entry['aliases'] ) {
			foreach ( $entry['aliases'] as $alias ) {
				if ( $this->startsWith( $alias, $search ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param string $string
	 * @param string $search
	 * @return bool
	 */
	public function startsWith( $string, $search ) {
		return stripos( $string, $search ) === 0;
	}

	/**
	 * @param array $entries
	 * @param array $searchResult
	 * @param int $resultSize
	 * @return array representing Json
	 */
	public function mergeWithTraditionalSearchResults( array &$entries, $searchResult, $resultSize ) {

		// Avoid duplicates
		$existingKeys = array();
		foreach ( $entries as $entry ) {
			$existingKeys[$entry['id']] = true;
		}

		$distinctCount = count( $entries );
		foreach ( $searchResult as $sr ) {
			if ( !array_key_exists( $sr['id'], $existingKeys ) ) {
				$entries[] = $sr;
				$distinctCount++;
				if ( $distinctCount >= $resultSize ) {
					break;
				}
			}
		}
		return $entries;
	}
}
