<?php

namespace PropertySuggester;

use ApiResult;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\StoreFactory;
use Wikibase\Term;
use Wikibase\TermIndex;

/**
 * ResultBuilder builds Json-compatible array structure from suggestions
 *
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

	public function __construct( $result, $search ) {
		$this->entityTitleLookup = WikibaseRepo::getDefaultInstance()->getEntityTitleLookup();
		$this->termIndex = StoreFactory::getStore()->getTermIndex();
		$this->result = $result;
		$this->searchPattern = '/^' . preg_quote( $search, '/' ) . '/i';
	}

	/**
	 * @param Suggestion[] $suggestions
	 * @param string $language
	 * @return array
	 */
	public function createJSON( $suggestions, $language ) {
		$entries = array();
		$ids = array();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$ids[] = $id;
		}
		//See SearchEntities
		$terms = $this->termIndex->getTermsOfEntities( $ids, 'property', $language );
		$clusteredTerms = $this->clusterTerms( $terms );

		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$entries[] = $this->buildEntry( $id, $clusteredTerms, $suggestion );
		}
		return $entries;
	}

	/**
	 * @param \Wikibase\EntityId $id
	 * @param array $clusteredTerms
	 * @param Suggestion $suggestion
	 * @return array $entry
	 */
	private function buildEntry( $id, $clusteredTerms, $suggestion ){
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
	private function clusterTerms( $terms ) {
		$clusteredTerms = array();

		foreach ( $terms as $term ) {
			$id = $term->getEntityId()->getSerialization();
			if ( !$clusteredTerms[$id] ) {
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
	private function checkAndSetAlias( &$entry, $term ) {
		if ( preg_match( $this->searchPattern, $term->getText() ) ) {
			if ( !isset( $entry['aliases'] ) ) {
				$entry['aliases'] = array();
				$this->result->setIndexedTagName( $entry['aliases'], 'alias' );
			}
			$entry['aliases'][] = $term->getText();
		}
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