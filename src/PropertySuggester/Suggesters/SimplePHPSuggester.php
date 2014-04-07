<?php

namespace PropertySuggester\Suggesters;

use LoadBalancer;
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
	 * default is 107 ( DEPRECATED main type )
	 * @return int[]
	 */
	public function getDeprecatedPropertyIds() {
		return $this->deprecatedPropertyIds;
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds( array $deprecatedPropertyIds ) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @param int[] $propertyIds
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	protected function getSuggestions( array $propertyIds ) {
		if ( !$propertyIds ) {
			return array();
		}
		$excludedIds = array_merge( $propertyIds, $this->getDeprecatedPropertyIds() );
		$count = count( $propertyIds );

		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => "sum(probability)/$count" ),
			array( 'pid1 IN (' . $dbr->makeList( $propertyIds ) . ')',
				   'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')' ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC'
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
