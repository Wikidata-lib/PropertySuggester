<?php

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Item;


class SimplePHPSuggester implements SuggesterEngine {
	private $deprecatedPropertyIds = [ 107 ];
	private $propertyRelations = array();

	public function __construct( DatabaseBase $dbr ) {
		$this->dbr = $dbr;
	}

	public function getDeprecatedPropertyIds() {
		return $this->deprecatedPropertyIds;
	}

	public function getPropertyRelations() {
		return $this->propertyRelations;
	}

	/**
	 * @param int[] $propertyIds
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	private function getSuggestions( array $propertyIds ) {
		if ( !$propertyIds ) {
			return array();
		}
		$excludedIds = array_merge( $propertyIds, $this->getDeprecatedPropertyIds() );
		$count = count( $propertyIds );

		$res = $this->dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( 'pid1 IN (' . $this->dbr->makeList( $propertyIds ) . ')',
				   'pid2 NOT IN (' . $this->dbr->makeList( $excludedIds ) . ')' ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				//'HAVING' => "sum(probability)/$count > $threshold",
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
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds ) {
		$numericIds = array();
		foreach ( $propertyIds as $id ) {
			$numericIds[] = $id->getNumericId();
		}
		return $this->getSuggestions( $numericIds );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item ) {
		$snaks = $item->getAllSnaks();
		$numericIds = array();
		foreach ( $snaks as $snak ) {
			$numericIds[] = $snak->getPropertyId()->getNumericId();
		}
		return $this->getSuggestions( $numericIds );
	}

}
