<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SimplePHPSuggester;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\StoreFactory;


/**
 * API module helper to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */
class GetSuggestionsHelper {
    
	public function __construct() {
            
        }
    
    
        /**
        * @param string $entity
        * @param string $propertyList
        * @param string $search
        * @param string $language
        * @return array
        */
        public function generateSuggestions( $entity, $propertyList ) {
		$suggester = new SimplePHPSuggester( wfGetDB( DB_SLAVE ) );
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		if ( $entity !== null ) {
                        $id = new  ItemId( $entity );
			$entity = $lookup->getEntity( $id );
			$suggestions = $suggester->suggestByItem( $entity );
                        return $suggestions;
		} else {
			$splitList = explode( ',', $propertyList );
			$properties = array();
			foreach ( $splitList as $id ) {
				$properties[] = PropertyId::newFromNumber( $this->cleanPropertyId( $id ) );
			}
			$suggestions = $suggester->suggestByPropertyIds( $properties );
                        return $suggestions;
		}
        }
    
        /**
        * accepts strings of the format "P123" or "123" and returns
        * the id as int. returns 0 if the string is not of the specified format
        *
        * @param string $propertyId
        * @return int
        */
        protected function cleanPropertyId( $propertyId ) {
                if ( $propertyId[0] === 'P' ) {
                        return (int)substr( $propertyId, 1 );
                }
		return (int)$propertyId;
        }
        
        /**
	 * Filter for entries whose label or alias starts with $search
	 * @param array $entries
	 * @param string $search
	 * @return array
	 */
	protected function filterByPrefix( array &$entries, $search ) {
		$matchingEntries = array();
		foreach ( $entries as $entry ) {
			if ( $this->isMatch( $entry, $search ) ) {
				$matchingEntries[] = $entry;
			}
		}
		return $matchingEntries;
	}

	/**
	 * Check if entry['label'] or entry['aliases'] starts with $search
	 *
	 * @param array $entry
	 * @param string $search
	 * @return bool
	 */
	protected function isMatch( array $entry, $search ) {
		if ( stripos( $entry['label'], $search ) === 0 ) {
			return true;
		}
		if ( $entry['aliases'] ) {
			foreach ( $entry['aliases'] as $alias ) {
				if ( stripos( $alias, $search ) === 0 ) {
					return true;
				}
			}
		}
		return false;
	}
    
}
