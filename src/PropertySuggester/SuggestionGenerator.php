<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\Store\EntityLookup;
use Wikibase\TermIndex;
use InvalidArgumentException;

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
	 * @param string $itemIdString - An item id
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @throws \InvalidArgumentException
	 * @return array
	 */

	public function generateSuggestionsByItem( $itemIdString, $limit, $minProbability, $context ) {
		$id = new  ItemId( $itemIdString );
		$item = $this->entityLookup->getEntity( $id );
		if( $item == null ){
			throw new InvalidArgumentException( 'Item ' . $id . ' could not be found' );
		}
		$suggestions = $this->suggester->suggestByItem( $item, $limit, $minProbability, $context );
		return $suggestions;
	}

	/**
	 * @param string[] $propertyIdList - A list of property-id-strings
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @return Suggestion[]
	 */
	public function generateSuggestionsByPropertyList( array $propertyIdList, $limit, $minProbability, $context ) {
		$propertyIds = array();
		foreach ( $propertyIdList as $stringId ) {
			$propertyIds[] = new PropertyId( $stringId );
		}

		$suggestions = $this->suggester->suggestByPropertyIds( $propertyIds, $limit, $minProbability, $context );
		return $suggestions;
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
			return array_slice( $suggestions, 0, $resultSize );
		}
		$ids = $this->getMatchingIDs( $search, $language );

		$id_set = array();
		foreach ( $ids as $id ) {
			$id_set[$id->getNumericId()] = true;
		}

		$matching_suggestions = array();
		$count = 0;
		foreach ( $suggestions as $suggestion ) {
			if ( array_key_exists( $suggestion->getPropertyId()->getNumericId(), $id_set ) ) {
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
