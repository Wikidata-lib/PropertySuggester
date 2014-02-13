<?php

include "Suggestion.php";

class SimpleMisfitSuggester {

	public function suggestMisfitsByEntity( $entity, $threshold ) {
		$attributeValuePairs = $entity->getAllSnaks();
		return $this->suggestionsByAttributeValuePairs( $attributeValuePairs, $threshold );
	}

	public function suggestMisfitsByAttributeValuePairs( $attributeValuePairs, $threshold ) {
		$attributeList = array();
		foreach ( $attributeValuePairs as $value )	{
			array_push( $attributeList, (int)( substr( $value->getPropertyId(), 1 ) ) );
		}
		return $this->suggestionsByAttributeList( $attributeList, $threshold );
	}

	public function suggestMisfitsByAttributList( $attributeList, $threshold ) {
		$suggestionIds = implode( ", ", $attributeList );
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->query( "
			SELECT pid2 AS pid, sum(correlation)/" . ( count( $attributeList ) -1 ) . " AS cor
			FROM wbs_PropertyPairs
			WHERE pid1 IN ($suggestionIds) AND pid2 IN ($suggestionIds)
			GROUP BY pid2
			ORDER BY cor ASC"
		);
		$resultArray = array();
		foreach ( $res as $suggestInfo ) {
			if ( ( $key = array_search( $suggestInfo->pid, $attributeList ) ) !== false ) {
				unset( $attributeList[$key] ); // Find ids that where in the original attributeList but are not contained in the query result
			}
			if ( $suggestInfo->cor <= $threshold ) {
				$suggestion = new Suggestion( $suggestInfo->pid, $suggestInfo->cor, null );
				array_push( $resultArray, $suggestion );
			}
		}
		foreach ( $attributeList as $id ) {
			$suggestion = new Suggestion( $id, 0, null, null );
			$resultArray = array_merge( [$suggestion], $resultArray );
		}
		return $resultArray;
	}

}
