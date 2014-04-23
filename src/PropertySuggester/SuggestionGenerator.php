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
 * API module helper to generate property suggestions.
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class SuggestionGenerator {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	/**
	 * @var SuggesterEngine
	 */
	private $suggester;

	public function __construct( EntityLookup $entityLookup, TermIndex $termIndex, SuggesterEngine $suggester ) {
		$this->entityLookup = $entityLookup;
		$this->suggester = $suggester;
		$this->termIndex = $termIndex;
	}

	/**
	 * @param string $item - An item id
	 * @param int $limit
	 * @param float $minProbability
	 * @return array
	 */
	public function generateSuggestionsByItem( $item, $limit, $minProbability ) {
		$id = new  ItemId( $item );
		$item = $this->entityLookup->getEntity( $id );
		$suggestions = $this->suggester->suggestByItem( $item, $limit, $minProbability );
		return $suggestions;
	}

	/**
	 * @param string[] $propertyList - A list of property ids
	 * @param int $limit
	 * @param float $minProbability
	 * @return Suggestion[]
	 */
	public function generateSuggestionsByPropertyList( array $propertyList, $limit, $minProbability ) {
		$properties = array();
		foreach ( $propertyList as $id ) {
			$properties[] = PropertyId::newFromNumber( $this->getNumericPropertyId( $id ) );
		}
		$suggestions = $this->suggester->suggestByPropertyIds( $properties, $limit, $minProbability );
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
		if ( strlen( $propertyId ) && strtolower( $propertyId[0] ) === 'p' ) {
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
	public function filterSuggestions( array $suggestions, $search, $language, $resultSize ) {
		if ( !$search ) {
			return $suggestions;
		}
		$ids = $this->getMatchingIDs( $search, $language );

		$id_map = array();
		foreach ( $ids as $id ) {
			$id_map[$id->getNumericId()] = true;
		}

		$matching_suggestions = array();
		$count = 0;
		foreach ( $suggestions as $suggestion ) {
			if ( array_key_exists( $suggestion->getPropertyId()->getNumericId(), $id_map ) ) {
				$matching_suggestions[] = $suggestion;
				if ( ++$count == $resultSize ) {
					break;
				}
			}
		}
		return $matching_suggestions;
	}

	/**
	 * @param string $search
	 * @param string $language
	 * @return PropertyId[]
	 */
	private function getMatchingIDs( $search, $language ) {
		$ids = $this->termIndex->getMatchingIDs(
			array(
				new \Wikibase\Term( array(
					'termType' => \Wikibase\Term::TYPE_LABEL,
					'termLanguage' => $language,
					'termText' => $search
				) ),
				new \Wikibase\Term( array(
					'termType' => \Wikibase\Term::TYPE_ALIAS,
					'termLanguage' => $language,
					'termText' => $search
				) )
			),
			Property::ENTITY_TYPE,
			array(
				'caseSensitive' => false,
				'prefixSearch' => true,
			)
		);
		return $ids;
	}

}
