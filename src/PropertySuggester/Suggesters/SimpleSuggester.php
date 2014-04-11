<?php

namespace PropertySuggester\Suggesters;

use LoadBalancer;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class SimpleSuggester
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 */
class SimpleSuggester implements SuggesterEngine {

	/**
	 * @var int[]
	 */
	private $deprecatedPropertyIds = array();

	/**
	 * @var LoadBalancer
	 */
	private $lb;

	/**
	 * @param LoadBalancer $lb
	 */
	public function __construct( LoadBalancer $lb ) {
		$this->lb = $lb;
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds( array $deprecatedPropertyIds ) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @param int[] $idTuples
	 * @param int $limit
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $ids, array $idTuples, $count, $limit ) {
		if ( !$idTuples ) {
			return array();
		}
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException('$limit must be int!');
		}
		$excludedIds = array_merge( $ids, $this->deprecatedPropertyIds );

		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( '(pid1, qid1) IN (' . str_replace( "'", '', $dbr->makeList( $idTuples ) ) . ')',
				   'pid2 NOT IN (' . str_replace( "'", '', $dbr->makeList( $excludedIds ) ) . ')' ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC',
				'LIMIT'	   => $limit
			)
		);
		$this->lb->reuseConnection( $dbr );

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
	public function suggestByPropertyIds( array $propertyIds, $limit ) {
		$ids = array();
		$idTuples = array();
		foreach ( $propertyIds as $id ) {
			$ids[] = $id->getNumericId();
			$idTuples[] = $this->buildTuple($id->getNumericId(), 0);
		}
		return $this->getSuggestions( $ids, $idTuples, count($propertyIds), $limit );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @param int $limit
	 * @return Suggestion[]
	 */

	public function suggestByItem( Item $item, $limit ) {
		$snaks = $item->getAllSnaks();
		$ids = array();
		$idTuples = array();
		foreach ( $snaks as $snak ) {
			$numericId = $snak->getPropertyId()->getNumericId();
			$ids[] = $numericId;
			$idTuples[] = $this->buildTuple($numericId, 0);
			if( $snak->getType() === "value" ){
				if ( $snak->getDataValue()->getType() === "wikibase-entityid" ) {
					$dataValue = $snak->getDataValue();
					$id = (int)substr( $dataValue->getEntityId()->getSerialization(), 1 );
					$idTuples[] = $this->buildTuple($snak->getPropertyId()->getNumericId(), $id);
				}
			}
		}
		return $this->getSuggestions( $ids, $idTuples, count($snaks), $limit );
	}

	public function buildTuple( $a, $b ){
		$tuple = '( '. $a .', '. $b .')';
		return $tuple;
	}



}
