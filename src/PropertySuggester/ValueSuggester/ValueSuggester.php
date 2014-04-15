<?php

namespace PropertySuggester\ValueSuggester;

use LoadBalancer;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\ItemId;
use ResultWrapper;

/**
 * Class SimpleSuggester
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 */
class ValueSuggester implements ValueSuggesterEngine {


	/**
	 * @param LoadBalancer $lb
	 */
	public function __construct( LoadBalancer $lb ) {
		$this->lb = $lb;
	}

	/**
	 * @param ItemId $itemId
	 * @param PropertyId $itemId
	 * @param $minProbability float
	 * @return ValueSuggestion[]
	 */
	public function getValueSuggestions( $itemId, $propertyId,  $minProbability )
	{
		$dbr = $this->lb->getConnection( DB_SLAVE );
		$statements = array( "(31,5)");
		$res = $dbr->select(
			array( //FROM
				"rules" => "vs_statement_pair_occurrences",
				"property_value_occurrences" => "vs_statement_occurrences",
				"property_property_occurrences" => "vs_statement_occurrences"),
			array( //SELECT
				"propertyId" => "rule.s2Id",
				"value" => "rule.s2ValueId",
				"( 1 - EXP(SUM(LOG(1- ( (rules.occurrences*1.0/property_value_occurrences.occurrences) / (property_property_occurrences.probability ) ))) ) ) " => "pr"),
			array( //WHERE
				"property_property_occurrences.pid1 = s1Id",
				"property_property_occurrences.pid2 = s2Id",
				"property_value_occurrences.propertyId = s1Id",
				"property_value_occurrences.valueEntityId = s1ValueId",
				"(s1Id, s1ValueId) IN " . $dbr->makeList( $statements ) . " "),
			__METHOD__,
			array(
				"GROUP BY" =>  $dbr->makeList( array("rules.s2Id", "rules.s2ValueId")),
				"HAVING" => "pr > $minProbability",
				"ORDER BY" => "pr DESC"));
		$this->lb->reuseConnection($dbr);

		return $this->buildResult($res);
	}

	/**
	 * @param ResultWrapper $res
	 * @return ValueSuggestion[]
	 */
	protected function &buildResult( ResultWrapper &$res ) {
		$resultArray = array();
		foreach ( $res as $row ) {
			$propertyId = PropertyId::newFromNumber( (int) $row->propertyId);
			$valueId = ItemId::newFromNumber( ( int ) $row->value );
			$suggestion = new ValueSuggestion($propertyId, $valueId, $row->pr);
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

}
