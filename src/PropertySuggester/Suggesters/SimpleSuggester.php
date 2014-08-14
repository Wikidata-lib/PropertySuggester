<?php

namespace PropertySuggester\Suggesters;

use LoadBalancer;
use ProfileSection;
use InvalidArgumentException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use ResultWrapper;
use Wikibase\DataModel\Snak\Snak;

/**
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 *
 * @licence GNU GPL v2+
 */
class SimpleSuggester implements SuggesterEngine {

	/**
	 * @var int[]
	 */
	private $deprecatedPropertyIds = array();

	/**
	 * @var int[]
	 */
	private $classifyingProperties = array(31);

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
	 * @param int[] $propertyIds
	 * @param string[] $idTuples
	 * @param int $limit
	 * @param int $count
	 * @param float $minProbability
	 * @param string $context
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $propertyIds, array $idTuples, $limit, $minProbability, $context ) {
		$profiler = new ProfileSection( __METHOD__ );
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit must be int!' );
		}
		if ( !is_float( $minProbability ) ) {
			throw new InvalidArgumentException( '$minProbability must be float!' );
		}
		if ( !$propertyIds ) {
			return array();
		}

		$excludedIds = array_merge( $propertyIds, $this->deprecatedPropertyIds );
		$count = count( $propertyIds );

		$dbr = $this->lb->getConnection( DB_SLAVE );
		if (context == 'item'){
			$condition = '(pid1, qid1) IN (' . str_replace( "'", '', $dbr->makeList( $idTuples ) ) . ')';
		}
		else{
			$condition = 'pid1 IN (' . $dbr->makeList( $propertyIds ) . ')';
		}
		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( $condition,
				   'qid1' => 0,
				   'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')',
				   'context' => $context ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC',
				'LIMIT'    => $limit,
				'HAVING'   => 'prob > ' . floatval( $minProbability )
				)
			);
		$this->lb->reuseConnection( $dbr );

		return $this->buildResult( $res );
	}

	/**
	 * @see SuggesterEngine::suggestByPropertyIds
	 *
	 * @param PropertyId[] $propertyIds
	 * @param int $limit
 	 * @param float $minProbability
	 * @param string $context
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds, $limit, $minProbability, $context ) {
		$numericIds = array_map( array( $this, 'getNumericIdFromPropertyId' ), $propertyIds );
		return $this->getSuggestions( $numericIds, array(), $limit, $minProbability, $context );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit, $minProbability, $context ) {
		$snaks = $item->getAllSnaks();
		$ids = array();
		$idTuples = array();
		foreach ( $snaks as $snak ) {
			$numericId = $snak->getPropertyId()->getNumericId();
			$ids[] = $numericId;
			if (! in_array( $numericId, $this->classifyingProperties ) ) {
				$idTuples[] = $this->buildTuple( $numericId, 0 );
			}
			else {
				if ( $snak->getDataValue()->getType() === "wikibase-entityid" ) {
					$dataValue = $snak->getDataValue();
					$id = ( int )substr( $dataValue->getEntityId()->getSerialization(), 1 );
					$idTuples[] = $this->buildTuple( $snak->getPropertyId()->getNumericId(), $id );
				}
			}
		}
		return $this->getSuggestions( $ids, $idTuples, $limit, $minProbability, $context );
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return string
	 */
	public function buildTuple( $a, $b ){
		$tuple = '('. $a .', '. $b .')';
		return $tuple;
	}

	/**
	 * Converts the rows of the SQL result to Suggestion objects
	 *
	 * @param ResultWrapper $res
	 * @return Suggestion[]
	 */
	protected function buildResult( ResultWrapper $res ) {
		$resultArray = array();
		foreach ( $res as $row ) {
			$pid = PropertyId::newFromNumber( ( int )$row->pid );
			$suggestion = new Suggestion( $pid, $row->prob );
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

	private function getNumericIdFromPropertyId( PropertyId $propertyId ) {
		return $propertyId->getNumericId();
	}

	private function getNumericIdFromClaim( Claim $claim ) {
		return $claim->getMainSnak()->getPropertyId()->getNumericId();
	}

}
