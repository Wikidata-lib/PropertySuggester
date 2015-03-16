<?php

namespace PropertySuggester\Suggesters;

use BagOStuff;
use DatabaseBase;
use LoadBalancer;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use ResultWrapper;
use RuntimeException;

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
	private $classifyingPropertyIds = array();

	/**
	 * @var LoadBalancer
	 */
	private $lb;

	/**
	 * @var BagOStuff
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $cacheKeyPrefix;

	/**
	 * @var int
	 */
	private $cacheDuration;

	/**
	 * @param LoadBalancer $lb
	 * @param BagOStuff $cache
	 * @param string $cacheKeyPrefix
	 * @param int $cacheDuration
	 */
	public function __construct( LoadBalancer $lb, BagOStuff $cache, $cacheKeyPrefix,
		$cacheDuration
	) {
		if ( !is_string( $cacheKeyPrefix ) ) {
			throw new InvalidArgumentException( '$cacheKeyPrefix must be a string.' );
		}

		if ( !is_int( $cacheDuration ) ) {
			throw new InvalidArgumentException( '$cacheDuration must be an int.' );
		}

		$this->lb = $lb;
		$this->cache = $cache;
		$this->cacheKeyPrefix = $cacheKeyPrefix;
		$this->cacheDuration = $cacheDuration;
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds( array $deprecatedPropertyIds ) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @param int[] $classifyingPropertyIds
	 */
	public function setClassifyingPropertyIds( array $classifyingPropertyIds ) {
		$this->classifyingPropertyIds = array_flip( $classifyingPropertyIds );
	}

	/**
	 * @param int[] $propertyIds
	 * @param string[] $idTuples
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $propertyIds, array $idTuples, $limit, $minProbability, $context ) {
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit must be int!' );
		}
		if ( !is_float( $minProbability ) ) {
			throw new InvalidArgumentException( '$minProbability must be float!' );
		}

		$dbr = $this->lb->getConnection( DB_SLAVE );
		$count = $this->getPropertyCount( $dbr, $propertyIds );

		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			$this->makeGetSuggestionsConditions( $dbr, $propertyIds, $idTuples, $context ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC',
				'LIMIT'	=> $limit,
				'HAVING'   => 'prob > ' . floatval( $minProbability )
			)
		);

		$this->lb->reuseConnection( $dbr );

		return $this->buildResult( $res );
	}

	/**
	 * @param DatabaseBase $dbr
	 * @param int[] $propertyIds
	 *
	 * @throws RuntimeException
	 * @return int
	 */
	private function getPropertyCount( DatabaseBase $dbr, array $propertyIds ) {
		$count = count( $propertyIds );

		if ( $count === 0 ) {
			$cacheKey = $this->cacheKeyPrefix . ':' . 'Suggester:PropertyCount';
			$count = $this->cache->get( $cacheKey );

			if ( $count === false ) {
				$res = $dbr->selectRow(
					'wbs_propertypairs',
					'count(distinct(pid1)) as count',
					array(),
					__METHOD__
				);

				if ( !$res ) {
					throw new RuntimeException( 'Failed to obtain property count.' );
				}

				$count = (int)$res->count;
				$this->cache->set( $cacheKey, $count, $this->cacheDuration );
			}
		}

		return $count;
	}

	/**
	 * @param DatabaseBase $dbr
	 * @param int[] $propertyIds used property ids in an item.
	 * @param string[] $idTuples numericPropertyId => numericEntityId pairs used in an item.
	 * @param string $context
	 *
	 * @return string[]
	 */
	private function makeGetSuggestionsConditions( DatabaseBase $dbr, array $propertyIds,
		array $idTuples, $context
	) {
		$conditions = array(
			'context' => $context
		);

		$excludedIds = array_merge( $propertyIds, $this->deprecatedPropertyIds );

		if ( !empty( $excludedIds ) ) {
			array_unshift( $conditions, 'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')' );
		}

		if ( !empty( $propertyIds ) ) {
			if ( empty( $idTuples ) ) {
				array_unshift( $conditions,  'pid1 IN (' . $dbr->makeList( $propertyIds ) . ')' );
			}
			else{
				array_unshift( $conditions, $dbr->makeList( $idTuples, LIST_OR ) );
			}
		}

		return $conditions;
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
		if ( empty( $propertyIds ) ) {
			return array();
		}

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
		$statements = $item->getStatements()->toArray();
		$ids = array();
		$idTuples = array();
		foreach ( $statements as $statement ) {
			$numericPropertyId = $this->getNumericIdFromPropertyId( $statement->getMainSnak()->getPropertyId() );
			$ids[] = $numericPropertyId;
			if ( !isset( $this->classifyingPropertyIds[$numericPropertyId] ) ) {
				$idTuples[] = $this->buildTupleCondition( $numericPropertyId, '0' );
			}
			else {
				if ( $statement->getMainSnak()->getType() === "value" ) {
					$dataValue = $statement->getMainSnak()->getDataValue();
					$numericEntityId = ( int )substr( $dataValue->getEntityId()->getSerialization(), 1 );
					$idTuples[] = $this->buildTupleCondition( $numericPropertyId, $numericEntityId );
				}
			}
		}
		return $this->getSuggestions( $ids, $idTuples, $limit, $minProbability, $context );
	}

	/**
	 * @param int $a
	 * @param int $b
	 * @return string
	 */
	private function buildTupleCondition( $pid, $qid ){
		$tuple = '(pid1 = '. ( int )$pid .' AND qid1 = '. ( int )$qid .')';
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

}
