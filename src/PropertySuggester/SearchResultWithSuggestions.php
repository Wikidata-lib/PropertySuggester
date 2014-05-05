<?php

namespace PropertySuggester;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\StoreFactory;
use Wikibase\Term;
use Wikibase\TermIndex;
use Wikibase\DataModel\Entity\EntityId;
use ApiMain;

/**
 * SearchResultsWithSuggestions builds Json-compatible array structure after merging suggestions with search results
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class SearchResultWithSuggestions {

	/** @var $EntityTitleLookup */
	protected $entityTitleLookup;

	/** @var TermIndex */
	protected $termIndex;

	/** @var string */
	protected $searchPattern;

	/** @var array */
	protected $searchResult;

	/**
	 * @param $suggester
	 * @param Params $params
	 * @param $entityType
	 */
	public function __construct( $suggester, $params, $entityType ) {
		$this->searchPattern = '/^' . preg_quote( $params->search, '/' ) . '/i';
		$this->entityTitleLookup = WikibaseRepo::getDefaultInstance()->getEntityTitleLookup();
		$this->termIndex = StoreFactory::getStore()->getTermIndex();

		$filteredSuggestions = new FilteredSuggestions( $suggester, $params );
		$suggestions = $filteredSuggestions->getSuggestions();

		$this->buildEntries( $suggestions, $params->language, $entityType );

		// merge with search result if possible and necessary
		if ( $this->resultSize() < $params->internalResultListSize && $params->search !== '' ) {
			$this->supplementEntries( $params->internalResultListSize, $params->search, $params->language, $entityType, $params->request );
		}

		$this->searchResult = array_slice( $this->searchResult, $params->continue, $params->limit );
	}

	/**
	 * @return int
	 */
	public function resultSize() {
		return count( $this->searchResult );
	}

	/**
	 * @return array
	 */
	public function &getResultDictionary() {
		return $this->searchResult;
	}

	/**
	 * @param Suggestion[] $suggestions
	 * @param $language
	 * @param string $entityType
	 * @return array
	 */
	public function buildEntries( array &$suggestions, $language, $entityType ) {
		$propertyIds = array();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getEntityId();
			$propertyIds[] = $id;
		}
		$terms = $this->termIndex->getTermsOfEntities( $propertyIds, $entityType, $language );
		$clusteredTerms = $this->clusterTerms( $terms );

		$this->searchResult = array();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getEntityId();
			$this->searchResult[] = $this->buildEntry( $id, $clusteredTerms, $suggestion );
		}
	}

	/**
	 * @param EntityId $id
	 * @param array $clusteredTerms
	 * @param Suggestion $suggestion
	 * @return array $entry
	 */
	private function buildEntry( EntityId $id, array $clusteredTerms, Suggestion $suggestion ) {
		$entry = array();
		$entry['id'] = $id->getPrefixedId();
		$entry['url'] = $this->entityTitleLookup->getTitleForId( $id )->getFullUrl();
		$entry['rating'] = $suggestion->getProbability();

		foreach ( $clusteredTerms[$id->getSerialization()] as $term ) {
			/** @var $term Term */
			switch ( $term->getType() ) {
				case Term::TYPE_LABEL:
					$entry['label'] = $term->getText();
					break;
				case Term::TYPE_DESCRIPTION:
					$entry['description'] = $term->getText();
					break;
				case Term::TYPE_ALIAS:
					$this->checkAndSetAlias( $entry, $term );
					break;
			}
		}
		return $entry;
	}

	/**
	 * @param Term[] $terms
	 * @return Term[][]
	 */
	private function clusterTerms( array $terms ) {
		$clusteredTerms = array();

		foreach ( $terms as $term ) {
			$id = $term->getEntityId()->getSerialization();
			if ( !isset( $clusteredTerms[$id] ) ) {
				$clusteredTerms[$id] = array();
			}
			$clusteredTerms[$id][] = $term;
		}
		return $clusteredTerms;
	}

	/**
	 * @param array $entry
	 * @param Term $term
	 */
	private function checkAndSetAlias( array &$entry, Term $term ) {
		if ( preg_match( $this->searchPattern, $term->getText() ) ) {
			if ( !isset( $entry['aliases'] ) ) {
				$entry['aliases'] = array();
			}
			$entry['aliases'][] = $term->getText();
		}
	}

	/**
	 * @param $resultSize
	 * @param $search
	 * @param $language
	 * @param $entityType
	 * @param $baseRequest
	 */
	private function supplementEntries( $resultSize, $search, $language, $entityType, $baseRequest ) {
		if ( $entityType === 'property' ) {
			$type = Property::ENTITY_TYPE;
		} else {
			$type = Item::ENTITY_TYPE;
		}

		$searchEntitiesParameters = new \DerivativeRequest(
			$baseRequest,
			array(
				'limit' => $resultSize + 1,
				'continue' => 0,
				'search' => $search,
				'action' => 'wbsearchentities',
				'language' => $language,
				'type' => $type
			)
		);

		$api = new ApiMain( $searchEntitiesParameters );
		$api->execute();
		$apiResult = $api->getResultData();
		$apiSearchResult = $apiResult['search'];

		$this->mergeWithTraditionalSearchResults( $apiSearchResult, $resultSize );
	}

	/**
	 * @param array $searchApiResult
	 * @param $targetResultSize
	 */
	public function mergeWithTraditionalSearchResults( array &$searchApiResult, $targetResultSize ) {
		// Avoid duplicates
		$existingKeys = array();
		foreach ( $this->searchResult as $entry ) {
			$existingKeys[$entry['id']] = true;
		}

		$distinctCount = $this->resultSize();
		foreach ( $searchApiResult as $result ) {
			if ( !array_key_exists( $result['id'], $existingKeys ) ) {
				$this->searchResult[] = $result;
				$distinctCount++;
				if ( $distinctCount >= $targetResultSize ) {
					break;
				}
			}
		}
	}
}

