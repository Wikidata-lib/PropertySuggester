<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityLookup;
use Wikibase\TermIndex;

/**
 * API module helper to get property suggestions
 *
 * @licence GNU GPL v2+
 */
class GetSuggestionsHelper {

	/**
	 * @var EntityLookup
	 */
	private $lookup;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	/**
	 * @var SuggesterEngine
	 */
	private $suggester;

	public function __construct( EntityLookup $lookup, TermIndex $termIndex, SuggesterEngine $suggester ) {
		$this->lookup = $lookup;
		$this->suggester = $suggester;
		$this->termIndex = $termIndex;
	}

	/**
	 * Provide either an item id
	 *
	 * @param string $item
	 * @param int $limit
	 * @return array
	 */
	public function generateSuggestionsByItem( $item, $limit ) {
		$id = new  ItemId( $item );
		$item = $this->lookup->getEntity( $id );
		$suggestions = $this->suggester->suggestByItem( $item, $limit );
		return $suggestions;
	}

	/**
	 * Provide comma separated list of property ids
	 *
	 * @param string $propertyList
	 * @param int $limit
	 * @return Suggestion[]
	 */
	public function generateSuggestionsByPropertyList( $propertyList, $limit ) {
		$splitList = explode( ',', $propertyList );
		$properties = array();
		foreach ( $splitList as $id ) {
			$properties[] = PropertyId::newFromNumber( $this->getNumericPropertyId( $id ) );
		}
		$suggestions = $this->suggester->suggestByPropertyIds( $properties, $limit );
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
	 * @param Suggestion[] $suggestions
	 * @param string $search
	 * @param string $language
	 * @param int $resultSize
	 * @return Suggestion[]
	 */
	public function filterSuggestions( $suggestions, $search, $language, $resultSize ) {
		if ( !$search ) {
			return $suggestions;
		}
		$ids = $this->termIndex->getMatchingIDs(
			array(
				new \Wikibase\Term( array(
					'termType' 		=> \Wikibase\Term::TYPE_LABEL,
					'termLanguage' 	=> $language,
					'termText' 		=> $search
				) ),
				new \Wikibase\Term( array(
					'termType' 		=> \Wikibase\Term::TYPE_ALIAS,
					'termLanguage' 	=> $language,
					'termText' 		=> $search
				) )
			),
			Property::ENTITY_TYPE,
			array(
				'caseSensitive' => false,
				'prefixSearch' => true,
			)
		);

		$id_map = array();
		foreach ( $ids as $id ) {
			/** @var PropertyId $id */
			$id_map[$id->getNumericId()] = true;
		}

		$matching_suggestions = array();
		foreach ( $suggestions as $suggestion ) {
			if ( array_key_exists( $suggestion->getPropertyId()->getNumericId(), $id_map ) ) {
				$matching_suggestions[] = $suggestion;
			}
			if ( count( $matching_suggestions ) == $resultSize ) {
				break;
			}
		}
		return $matching_suggestions;
	}

}
