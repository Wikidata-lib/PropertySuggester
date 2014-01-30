<?php

use Wikibase\DataModel\Entity\PropertyId;

include "SuggesterEngine.php";

class SimplePHPSuggester implements SuggesterEngine {
	private $deprecatedPropertyIds = "107";
	private $propertyRelations = array();

		public function getDeprecatedPropertyIds() {
		return $this->deprecatedPropertyIds;
	}

	public function getPropertyRelations() {
		return $this->propertyRelations;
	}

	// this function is not part of SuggesterEngine.php?!
	public function suggestionsByAttributeList( $attributeList, $resultSize=-1, $threshold=0 ) {
		if ( !$attributeList ) {
			return array();
		}		
		$suggestionIds = implode( ", ", $attributeList );
		$excludedIds = $suggestionIds . ", " . $this->getDeprecatedPropertyIds();
		$count = count( $attributeList );
		$dbr = wfGetDB( DB_SLAVE );
		$query =  
		  "SELECT pid2 AS pid, sum(correlation)/" . count( $attributeList ) . " AS cor
			FROM wbs_propertypairs
			WHERE pid1 IN ($suggestionIds) AND pid2 NOT IN ($excludedIds)
			GROUP BY pid2
			HAVING sum(correlation)/$count > $threshold
			ORDER BY cor DESC";
		if ( ((int)$resultSize) >= 0 ) {
			$query = $query . " LIMIT $resultSize";
		}
		$res = $dbr->query($query);
		$resultArray = array();
		foreach ( $res as $i => $row ) {
			$pid = PropertyId::newFromNumber( (int)$row->pid );
			$suggestion = new Suggestion( $pid, $row->cor, null, null );
			$resultArray[] =  $suggestion;
		}
		return $resultArray;
	}

	public function suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize = -1, $threshold = 0 ) {
		$attributeList = array();
		foreach ( $attributeValuePairs as $key => $value )	{
			$attributeList[] = $value->getPropertyId()->getNumericId();
		}
		return $this->suggestionsByAttributeList( $attributeList, $resultSize, $threshold );
	}

	public function suggestionsByItem( $entity, $resultSize = -1, $threshold = 0 ) {
		$attributeValuePairs = $entity->getAllSnaks();
		return $this->suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize, $threshold );
	}

}
