<?php

include "SuggesterEngine.php";

function compare_pairs($a, $b){ 
	return $a->getCorrelation() > $b->getCorrelation() ? -1 : 1;
}

class SimplePHPSuggester implements SuggesterEngine {
        private $deprecatedPropertyIds = "107";
        
        public function getDeprecatedPropertyIds(){
		return $this->deprecatedPropertyIds;
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
                foreach($res as $suggestInfo){
                        $suggestion = new Suggestion($suggestInfo->pid, $suggestInfo->cor, null);
                        array_push($resultArray, $suggestion);       
                }
                return $resultArray;
         }
			
	public function suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize, $threshold = 0 ) {
		$attributeList = array();
		foreach($attributeValuePairs as $value)	{
			array_push($attributeList, (int)(substr($value->getPropertyId(),1)));
		}
		return $this->suggestionsByAttributeList($attributeList, $resultSize, $threshold);
	}

	public function suggestionsByEntity( $entity, $resultSize, $threshold = 0 ) {
		$attributeValuePairs = $entity->getAllSnaks();
		return $this->suggestionsByAttributeValuePairs( $attributeValuePairs, $resultSize, $threshold );
	}
	
}
