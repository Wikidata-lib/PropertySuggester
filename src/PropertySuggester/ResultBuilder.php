<?php

namespace PropertySuggester;

use ApiResult;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\Term;
use Wikibase\TermIndex;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Lib\Store\EntityTitleLookup;

/**
 * ResultBuilder builds Json-compatible array structure from suggestions
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class ResultBuilder {

	/**
	 * @var $EntityTitleLookup
	 */
	private $entityTitleLookup;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	/**
	 * @var ApiResult
	 */
	private $result;

	/**
	 * @var string
	 */
	private $searchPattern;

	/**
	 * @param ApiResult $result
	 * @param TermIndex $termIndex
	 * @param EntityTitleLookup $entityTitleLookup
	 * @param string $search
	 */
	public function __construct( ApiResult $result, TermIndex $termIndex, EntityTitleLookup $entityTitleLookup, $search ) {
		$this->entityTitleLookup = $entityTitleLookup;
		$this->termIndex = $termIndex;
		$this->result = $result;
		$this->searchPattern = '/^' . preg_quote( $search, '/' ) . '/i';
	}

	/**
	 * @param Suggestion[] $suggestions
	 * @param string $language
	 * @return array[]
	 */
	public function createResultArray( array $suggestions, $language ) {
		$entries = array();
		$ids = array();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$ids[] = $id;
		}
		//See SearchEntities
		$terms = $this->termIndex->getTermsOfEntities(
			$ids,
			null,
			array( $language )
		);
		$clusteredTerms = $this->clusterTerms( $terms );

		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$entries[] = $this->buildEntry( $id, $clusteredTerms, $suggestion );
		}
		return $entries;
	}

	/**
	 * @param EntityId $id
	 * @param array[] $clusteredTerms
	 * @param Suggestion $suggestion
	 * @return array
	 */
	private function buildEntry( EntityId $id, array $clusteredTerms, Suggestion $suggestion ) {
		$entry = array();
		$entry['id'] = $id->getSerialization();
		$entry['url'] = $this->entityTitleLookup->getTitleForId( $id )->getFullUrl();
		$entry['rating'] = $suggestion->getProbability();

		if ( isset( $clusteredTerms[$id->getSerialization()] ) ) {
			$matchingTerms = $clusteredTerms[$id->getSerialization()];
		} else {
			$matchingTerms = array();
		}
		foreach ( $matchingTerms as $term ) {
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
		if ( !isset($entry['label'] ) ) {
			$entry['label'] = $id->getSerialization();
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
				$this->result->setIndexedTagName( $entry['aliases'], 'alias' );
			}
			$entry['aliases'][] = $term->getText();
		}
	}

	/**
	 * @param array[] $entries
	 * @param array[] $searchResults
	 * @param int $resultSize
	 * @return array[] representing Json
	 */
	public function mergeWithTraditionalSearchResults( array &$entries, array $searchResults, $resultSize ) {
		// Avoid duplicates
		$existingKeys = array();
		foreach ( $entries as $entry ) {
			$existingKeys[$entry['id']] = true;
		}

		$distinctCount = count( $entries );
		foreach ( $searchResults as $result ) {
			if ( !array_key_exists( $result['id'], $existingKeys ) ) {
				$entries[] = $result;
				$distinctCount++;
				if ( $distinctCount >= $resultSize ) {
					break;
				}
			}
		}
		return $entries;
	}

}
