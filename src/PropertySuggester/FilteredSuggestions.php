<?php

namespace PropertySuggester;

use PropertySuggester\Suggester\EntitySuggester;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityLookup;
use Wikibase\StoreFactory;
use Wikibase\Term;
use Wikibase\TermIndex;

/**
 * Retrieves Suggestions from an Entity Suggester and filters suggestions, which don't match a certain search string
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class FilteredSuggestions {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	/** @var Suggestion[] */
	private $filteredSuggestions;

	/**
	 * @param EntitySuggester $suggester
	 * @param Params $params
	 * @param string $entityType
	 */
	public function __construct( EntitySuggester $suggester, Params $params, $entityType ) {
		$this->entityLookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$this->termIndex = StoreFactory::getStore( 'sqlstore' )->getTermIndex();
		$this->entityType = $entityType;
		$this->filterSuggestions( $suggester->getSuggestions(), $params->search, $params->language, $params->internalResultListSize );
	}

	public function &getSuggestions()
	{
		return $this->filteredSuggestions;
	}

	/**
	 * @param Suggestion[] $allSuggestions
	 * @param $search
	 * @param $language
	 * @param $resultSize
	 */
	private function filterSuggestions( array $allSuggestions, $search, $language, $resultSize ) {
		if ( !$search ) {
			$this->filteredSuggestions = $allSuggestions;
		}
		else
		{
			$ids = $this->getMatchingIDs( $search, $language );

			$id_set = array();
			foreach ( $ids as $id ) {
				$id_set[$id->getSerialization()] = true;
			}

			$this->filteredSuggestions = array();
			$count = 0;
			foreach ( $allSuggestions as $suggestion ) {
				if ( array_key_exists( $suggestion->getEntityId()->getSerialization(), $id_set ) ) {
					$this->filteredSuggestions[] = $suggestion;
					if ( ++$count == $resultSize ) {
						break;
					}
				}
			}
		}
	}

	/**
	 * @param string $search
	 * @param string $language
	 * @return PropertyId[]
	 */
	private function getMatchingIDs( $search, $language ) {
		$ids = $this->termIndex->getMatchingIDs(
			array(
				new Term( array(
					'termType' => Term::TYPE_LABEL,
					'termLanguage' => $language,
					'termText' => $search
				) ),
				new Term( array(
					'termType' => Term::TYPE_ALIAS,
					'termLanguage' => $language,
					'termText' => $search
				) )
			),
			$this->entityType,
			array(
				'caseSensitive' => false,
				'prefixSearch' => true,
			)
		);
		return $ids;
	}

}
