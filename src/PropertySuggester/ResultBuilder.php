<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\Suggestion;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\StoreFactory;
use Wikibase\Term;


class ResultBuilder {

	/**
	 * @param Suggestion[] $suggestions
	 * @param string $language
	 * @param string $search
	 * @return array
	 */
	public function createJSON( $suggestions, $language, $search ) {
		$entries = array();
		$ids = array();
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$ids[] = $id;
		}
		//See SearchEntities
		$terms = StoreFactory::getStore()->getTermIndex()->getTermsOfEntities( $ids, 'property', $language );
		$clusteredTerms = $this->clusterTerms( $terms );

		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$entry = array();
			$entry['id'] = $id->getPrefixedId();
			$entry['url'] = $entityContentFactory->getTitleForId( $id )->getFullUrl();
			$entry['rating'] = $suggestion->getProbability();

			foreach ( $clusteredTerms[$id->getSerialization()] as &$term ) {
				/** @var $term Term */
				switch ( $term->getType() ) {
					case Term::TYPE_LABEL:
						$entry['label'] = $term->getText();
						break;
					case Term::TYPE_DESCRIPTION:
						$entry['description'] = $term->getText();
						break;
					case Term::TYPE_ALIAS:
						// Only include matching aliases
						if ( $this->startsWith( $term->getText(), $search ) ) {
							if ( !isset( $entry['aliases'] ) ) {
								$entry['aliases'] = array();
							}
							$entry['aliases'][] = $term->getText();
						}
						break;
				}
			}

			$entries[] = $entry;
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
	protected function isMatch( array $entry, $search ) {
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
	public function startsWith( $string, $search ) {
		return strncasecmp( $string, $search, strlen( $search ) ) === 0;
	}

	/**
	 * @param $terms Term[]
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

}