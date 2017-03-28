<?php

namespace PropertySuggester;

use InvalidArgumentException;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\Lib\Store\TermIndexSearchCriteria;
use Wikibase\TermIndex;
use Wikibase\TermIndexEntry;

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

	public function __construct(
		EntityLookup $entityLookup,
		TermIndex $termIndex,
		SuggesterEngine $suggester
	) {
		$this->entityLookup = $entityLookup;
		$this->suggester = $suggester;
		$this->termIndex = $termIndex;
	}

	/**
	 * @param string $itemIdString
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	public function generateSuggestionsByItem( $itemIdString, $limit, $minProbability, $context ) {
		$itemId = new ItemId( $itemIdString );
		/** @var Item $item */
		$item = $this->entityLookup->getEntity( $itemId );

		if ( $item === null ) {
			throw new InvalidArgumentException( 'Item ' . $itemIdString . ' could not be found' );
		}

		return $this->suggester->suggestByItem( $item, $limit, $minProbability, $context );
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
		$termIndexEntries = $this->termIndex->getTopMatchingTerms(
			array(
				new TermIndexSearchCriteria( array(
					'termLanguage' => $language,
					'termText' => $search
				) )
			),
			array(
				TermIndexEntry::TYPE_LABEL,
				TermIndexEntry::TYPE_ALIAS,
			),
			Property::ENTITY_TYPE,
			array(
				'caseSensitive' => false,
				'prefixSearch' => true,
			)
		);

		$ids = array();
		foreach ( $termIndexEntries as $entry ) {
			$ids[] = $entry->getEntityId();
		}

		return $ids;
	}

}
