<?php

namespace PropertySuggester\Suggester;

use LoadBalancer;
use PropertySuggester\Suggestion;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Item;
use ResultWrapper;

/**
 * Class SimpleSuggester
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 */
class ItemSuggester implements EntitySuggester {

	/** @var array */
	private $statements;

	/** @var int */
	private $numericPropertyId;

	/** @var float */
	private $minProbability;

	/**
	 * @param LoadBalancer $lb
	 */
	public function __construct( LoadBalancer $lb ) {
		$this->lb = $lb;
		$this->minProbability = 0.025;
	}

	public function setItem( Item $item ) {
		$this->statements = array();
		$snaks = $item->getAllSnaks();
		foreach ( $snaks as $snak ) {
			if ( $snak->getType() === 'value' ) {
				$propertyId = (string)$snak->getPropertyId()->getNumericId();
				if ( $snak->getDataValue()->getType() == 'wikibase-entityid' ) {
					$valueItemId = substr( $snak->getDataValue()->getEntityId()->getSerialization(), 1 );
					$this->statements[] = "($propertyId,$valueItemId)";
				}
			}
		}
	}

	public function setNumericPropertyId( $numericPropertyId ) {
		$this->numericPropertyId = $numericPropertyId;
	}

	public function setMinProbability( $minProbability ) {
		$this->minProbability = $minProbability;
	}

	/**
	 * @return \PropertySuggester\Suggestion[]
	 */
	public function &getSuggestions() {
		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			array( //FROM
				"vs_statement_pair_occurrences as rules",
				"vs_statement_occurrences as property_value_occurrences",
				"wbs_propertypairs as property_property_occurrences" ),
			array( //SELECT
				"propertyId" => "rules.s2Id",
				"value" => "rules.s2ValueId",
				"pr" => "( 1 - EXP(SUM(LOG(1- ( (rules.occurrences*1.0/property_value_occurrences.occurrences) / (property_property_occurrences.probability ) ))) ) ) " ),
			array( //WHERE
				"rules.s2Id = $this->numericPropertyId",
				"property_property_occurrences.pid1 = s1Id",
				"property_property_occurrences.pid2 = s2Id",
				"property_value_occurrences.propertyId = s1Id",
				"property_value_occurrences.valueEntityId = s1ValueId",
				"(s1Id, s1ValueId) IN (" . join( ",", $this->statements ) . ")" ),
			__METHOD__,
			array(
				"GROUP BY" => "rules.s2Id, rules.s2ValueId",
				"HAVING" => "pr > $this->minProbability",
				"ORDER BY" => "pr DESC" ) );
		$this->lb->reuseConnection( $dbr );

		return $this->buildResult( $res );
	}

	/**
	 * @param ResultWrapper $res
	 * @return Suggestion[]
	 */
	protected function &buildResult( ResultWrapper &$res ) {
		$resultArray = array();
		foreach ( $res as $row ) {
			$valueId = ItemId::newFromNumber( (int)$row->value );
			$suggestion = new Suggestion( $valueId, $row->pr );
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

}
