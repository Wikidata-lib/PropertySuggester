<?php

namespace PropertySuggester\Suggesters;

use DatabaseBase;
use InvalidArgumentException;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class SimplePHPSuggester
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 */
class SimplePHPSuggester implements SuggesterEngine {

	/**
	 * @var int[]
	 */
	private $deprecatedPropertyIds = array( 107 );

	/**
	 * @param DatabaseBase $dbr
	 */
	public function __construct( DatabaseBase $dbr ) {
		$this->dbr = $dbr;
	}

	/**
	 * default is 107 (DEPRECATED main type)
	 * @return int[]
	 */
	public function getDeprecatedPropertyIds() {
		return $this->deprecatedPropertyIds;
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds(array $deprecatedPropertyIds) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @param int[] $idTuples
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $idTuples, $count ) {
		if ( !$idTuples ) {
			return array();
		}

		$excludedIds = array_merge( /*$idTuples,*/ $this->getDeprecatedPropertyIds() );

		$res = $this->dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( '(pid1, qid1) IN (' . str_replace( "'", '', $this->dbr->makeList( $idTuples ) ) . ')',
				   'pid2 NOT IN (' . str_replace( "'", '', $this->dbr->makeList( $excludedIds ) ) . ')' ),
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
		$idTuples = array();
		foreach ( $propertyIds as $id ) {
			$idTuples[] = $this->buildTuple($id->getNumericId(), 0);
		}
		return $this->getSuggestions( $idTuples, count($propertyIds) );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @return Suggestion[]
	 */

	public function suggestByItem( Item $item ) {
		$snaks = $item->getAllSnaks();
		$idTuples = array();
		foreach ( $snaks as $snak ) {
			$idTuples[] = $this->buildTuple($snak->getPropertyId()->getNumericId(), 0);
			if( $snak->getType() === "value" ){
				if ( $snak->getDataValue()->getType() === "wikibase-entityid" ) {
					$dataValue = $snak->getDataValue();
					$id = (int)substr( $dataValue->getEntityId()->getSerialization(), 1 );
					$idTuples[] = $this->buildTuple($snak->getPropertyId()->getNumericId(), $id);
				}
			}
		}
		return $this->getSuggestions( $idTuples, count($snaks) );
	}

	public function buildTuple( $a, $b ){
		$tuple = '( '. $a .', '. $b .')';
		return $tuple;
	}



}
