<?php

namespace PropertySuggester\Suggester;

use LoadBalancer;
use InvalidArgumentException;
use PropertySuggester\Suggestion;
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
class PropertySuggester implements EntitySuggester {

	/** @var int[] */
	private $deprecatedPropertyIds = array();

	/** @var int[] */
	private $numericPropertyIds;

	/** @var int */
	private $limit;

	/** @var float */
	private $minProbability;

	/** @var LoadBalancer */
	private $lb;

	/**
	 * @param LoadBalancer $lb
	 * @param $limit
	 * @param $minProbability
	 */
	public function __construct( LoadBalancer $lb, $limit, $minProbability ) {
		$this->lb = $lb;
		$this->limit = $limit;
		$this->minProbability = $minProbability;
	}

	/**
	 * @param array $propertyIds
	 */
	public function setPropertyIds( array $propertyIds )
	{
		$this->$numericPropertyIds = array_map( array( $this, 'getNumericIdFromPropertyId' ), $propertyIds);
	}

	/**
	 * @param Item $item
	 */
	public function setItem( Item $item )
	{
		$snaks = $item->getAllSnaks();
		$this->$numericPropertyIds = array_map( array( $this, 'getNumericIdFromSnak' ), $snaks);
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds( array $deprecatedPropertyIds ) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @return array|\PropertySuggester\Suggestion[]
	 * @throws \InvalidArgumentException
	 */
	public function getSuggestions() {
		if ( !$this->$numericPropertyIds ) {
			return array();
		}
		if ( !is_int( $this->limit ) ) {
			throw new InvalidArgumentException('$limit must be int!');
		}
		if ( !is_float( $this->minProbability ) ) {
			throw new InvalidArgumentException('$minProbability must be float!');
		}
		$excludedIds = array_merge( $this->$numericPropertyIds, $this->deprecatedPropertyIds );
		$count = count( $this->$numericPropertyIds );

		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( 'pid1 IN (' . $dbr->makeList( $this->$numericPropertyIds ) . ')',
				   'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')' ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC',
				'LIMIT'    => $this->limit,
				'HAVING'   => "prob > $this->minProbability"
			)
		);
		$this->lb->reuseConnection( $dbr );

		return $this->buildResult($res);
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
			$pid = PropertyId::newFromNumber( ( int ) $row->pid );
			$suggestion = new Suggestion( $pid, $row->prob );
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

	private function getNumericIdFromSnak( Snak $snak ) {
		return $snak->getPropertyId()->getNumericId();
	}

	private function getNumericIdFromPropertyId( PropertyId $propertyId ) {
		return $propertyId->getNumericId();
	}

}
