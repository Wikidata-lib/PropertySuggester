<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\Suggestion;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\StoreFactory;
use Wikibase\Term;

/**
 * ResultBuilder builds Json-compatible array structure from suggestions
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */
class ResultBuilder {

	/**
	 * @var $EntityTitleLookup
	 */
	private $entityTitleLookup;

	public function __construct() {
		$this->entityTitleLookup = WikibaseRepo::getDefaultInstance()->getEntityTitleLookup();
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
		$terms = StoreFactory::getStore()->getTermIndex()->getTermsOfEntities( $ids, 'property', $language );
		$clusteredTerms = $this->clusterTerms( $terms );

		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$entries[] = $this->buildEntry( $id, $clusteredTerms, $suggestion, $search );
		}
		return $entries;
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
	 * Filters for entries whose label or alias starts with $search
	 * An entry needs to have a field 'label' and an array 'aliases'.
	 *
	 * @param array $entries
	 * @param string $search
	 * @return array representing Json
	 */
	public function filterByPrefix( array &$entries, $search ) {
		$matchingEntries = array();
		foreach ( $entries as $entry ) {
			if ( $this->isMatch( $entry, $search ) ) {
				$matchingEntries[] = $entry;
			}
		}
		return $matchingEntries;
	}

	/**
	 * Checks if entry['label'] or entry['aliases'] starts with $search
	 *
	 * @param array $entry in Json representation
	 * @param string $search
	 * @return bool
	 */
	private function isMatch( array $entry, $search ) {
		if ( $this->startsWith( $entry['label'], $search )) {
			return true;
		}
		if ( $entry['aliases'] ) {
			foreach ( $entry['aliases'] as $alias ) {
				if ( $this->startsWith( $alias, $search ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param string $string
	 * @param string $search
	 * @return bool
	 */
	private function startsWith( $string, $search ) {
		return strncasecmp( $string, $search, strlen( $search ) ) === 0;
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

}