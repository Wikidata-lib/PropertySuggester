<?php

namespace PropertySuggester;

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

	public function __construct() {
		$this->entityTitleLookup = WikibaseRepo::getDefaultInstance()->getEntityTitleLookup();
		$this->termIndex = StoreFactory::getStore()->getTermIndex();
	}

	/**
	 * @param Suggestion[] $suggestions
	 * @param string $language
	 * @param string $search
	 * @return array
	 */
	public function createJSON( $suggestions, $language, $search ) {
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
			$entries[] = $this->buildEntry( $id, $clusteredTerms, $suggestion, $search );
		}
		return $entries;
	}

	/**
	 * @param \Wikibase\EntityId $id
	 * @param array $clusteredTerms
	 * @param Suggestion $suggestion
	 * @param string $search
	 * @return array $entry
	 */
	private function buildEntry( $id, $clusteredTerms, $suggestion, $search ){
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
					$this->checkAndSetAlias( $entry, $term, $search );
					break;
			}
		}
		return $entry;
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
	 * @param string $search
	 */
	private function checkAndSetAlias( &$entry, $term, $search ) {
		if ( $this->startsWith( $term->getText(), $search ) ) {
			if ( !isset( $entry['aliases'] ) ) {
				$entry['aliases'] = array();
			}
			$entry['aliases'][] = $term->getText();
		}
	}

	/**
	 * @param string $string
	 * @param string $search
	 * @return bool
	 */
	private function startsWith( $string, $search ) {
		return strncasecmp( $string, $search, strlen( $search ) ) === 0;
	}
}