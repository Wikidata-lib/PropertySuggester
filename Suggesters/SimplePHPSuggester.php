<?php

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Item;

include "SuggesterEngine.php";

class SimplePHPSuggester implements SuggesterEngine {
	private $deprecatedPropertyIds = [ 107 ];
	private $propertyRelations = array();

	public function getDeprecatedPropertyIds() {
		return $this->deprecatedPropertyIds;
	}

	public function getPropertyRelations() {
		return $this->propertyRelations;
	}

	/**
	 * @param int[] $propertyIds
	 * @param int $limit
	 * @param int $threshold
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	private function getSuggestions( $propertyIds, $limit = -1, $threshold = 0 ) {
		if ( !$propertyIds ) {
			return array();
		}
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit must be an int' );
		}
		if ( !is_int( $threshold ) ) {
			throw new InvalidArgumentException( '$threshold must be an int' );
		}


		$dbr = wfGetDB( DB_SLAVE );
		$excludedIds = array_merge( $propertyIds, $this->getDeprecatedPropertyIds() );
		$count = count( $propertyIds );

		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'cor' => "sum(probability)/$count" ),
			array( 'pid1 IN (' . $dbr->makeList( $propertyIds ) . ')',
				'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')' ),
			__METHOD__,
			array(
				'LIMIT' => 1000,
				'GROUP BY' => 'pid2',
				'HAVING' => "sum(probability)/$count > $threshold",
				'ORDER BY' => 'prob DESC'
			)
		);

		$resultArray = array();
		foreach ( $res as $row ) {
			$pid = PropertyId::newFromNumber( (int)$row->pid );
			$suggestion = new Suggestion( $pid, $row->prob );
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

	/**
	 * @see SuggesterEngine::suggestByPropertyIds
	 *
	 * @param PropertyId[] $propertyIds
	 * @param int $limit
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( $propertyIds, $limit = -1 ) {
		$numericIds = array();
		foreach ( $propertyIds as $id ) {
			$numericIds[] = $id->getNumericId();
		}
		return $this->getSuggestions( $numericIds, $limit );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @param int $limit
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit = -1 ) {
		$snaks = $item->getAllSnaks();
		$numericIds = array();
		foreach ( $snaks as $snak ) {
			$numericIds[] = $snak->getPropertyId()->getNumericId();
		}
		return $this->getSuggestions( $numericIds, $limit );
	}

}
