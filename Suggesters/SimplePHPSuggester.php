<?php

use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;

include "SuggesterEngine.php";

function compare_pairs($a, $b){ 
	return $a->getCorrelation() > $b->getCorrelation() ? -1 : 1;
}

class SimplePHPSuggester implements SuggesterEngine {
	private $propertyRelations = array();
	
	public function getPropertyRelations(){
		return $this->propertyRelations;
	}
	
	public function suggestionsByAttributes( $attributeValuePairs, $resultSize, $threshold = 0 ) {
		$attributeList = array();
		foreach($attributeValuePairs as $key => $value)	{
			$attributeList[$key] = (int)(substr($value->getPropertyId(),1));
		}
		$list = implode(", ", $attributeList);
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->query("
			SELECT pid2, sum(correlation) AS cor
			FROM wbs_PropertyPairs
			WHERE pid1 IN ($list) AND pid2 NOT IN ($list)
			GROUP BY pid2
			HAVING sum(correlation)/" . count($attributeList) . " > $threshold
			ORDER BY cor DESC
			LIMIT 10");
		return $res;
	}
	
	private function computeAggregateCorrelation($attributeCorrelations, $attributeValuePairs){
		$sum = 0;
		for($i = 0; $i < count($attributeValuePairs); $i++)
		{
			$id = $attributeValuePairs[$i]->getPropertyId()->getPrefixedId();
			if(isset($attributeCorrelations[$id]))
			{
				$sum += ($attributeCorrelations[$id]) / $this->propertyRelations[$id]['appearances'];
			}
		}
		return $sum/count($attributeValuePairs);
	}

	public function suggestionsByEntity( $entity, $resultSize, $threshold = 0 ) {
		$attributeValuePairs = $entity->getAllSnaks();
		return $this->suggestionsByAttributes( $attributeValuePairs, $resultSize, $threshold );
	}
	
}
