<?php

use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;

include "SuggesterEngine.php";

function compare_pairs($a, $b){ 
	return $a->getCorrelation() > $b->getCorrelation() ? -1 : 1;
}

class SimplePHPSuggester implements SuggesterEngine {
        private $deprecatedPropertyIds = "107";
	private $propertyRelations = array();
        
        public function getDeprecatedPropertyIds(){
		return $this->deprecatedPropertyIds;
	}
	
	public function getPropertyRelations(){
		return $this->propertyRelations;
	}
	
	public function suggestionsByAttributeList( $attributeList, $resultSize, $threshold = 0 ) {
		$suggestionIds = implode(", ", $attributeList);
                $excludedIds = $suggestionIds . ", " . $this->getDeprecatedPropertyIds();
                $dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->query("
			SELECT pid2 AS pid, sum(correlation)/" . count($attributeList) . " AS cor
			FROM wbs_PropertyPairs
			WHERE pid1 IN ($suggestionIds) AND pid2 NOT IN ($excludedIds)
			GROUP BY pid2
			HAVING sum(correlation)/" . count($attributeList) . " > $threshold
			ORDER BY cor DESC
			LIMIT $resultSize");
		$resultArray = array();
                foreach($res as $rank => $suggestInfo){
                        $suggestion = new Suggestion($suggestInfo->pid, $suggestInfo->cor, null, null);
                        array_push($resultArray, $suggestion);       
                }
                return $resultArray;
         }
			
	public function suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize, $threshold = 0 ) {
		$attributeList = array();
		foreach($attributeValuePairs as $key => $value)	{
			array_push($attributeList, (int)(substr($value->getPropertyId(),1)));
		}
		return $this->suggestionsByAttributeList($attributeList, $resultSize, $threshold);
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
		return $this->suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize, $threshold );
	}
	
}
