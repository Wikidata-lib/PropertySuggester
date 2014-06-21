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
	 * @param int $limit
	 * @param float $minProbability
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $propertyIds, $limit, $minProbability ) {
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
		$context = 'item'; // only 'item' is supported at the moment

		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( 'pid1 IN (' . $dbr->makeList( $propertyIds ) . ')',
				   'qid1' => null,
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
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds, $limit, $minProbability ) {
		$numericIds = array_map( array( $this, 'getNumericIdFromPropertyId' ), $propertyIds );

		return $this->getSuggestions( $numericIds, $limit, $minProbability );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @param int $limit
	 * @param float $minProbability
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit, $minProbability ) {
		$claims = $item->getClaims();
		$numericIds = array_unique( array_map( array( $this, 'getNumericIdFromClaim' ), $claims ) );
		return $this->getSuggestions( $numericIds, $limit, $minProbability );
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

	private function getNumericIdFromClaim( Claim $claim ) {
		return $claim->getMainSnak()->getPropertyId()->getNumericId();
	}

	private function getNumericIdFromPropertyId( PropertyId $propertyId ) {
		return $propertyId->getNumericId();
	}

}
