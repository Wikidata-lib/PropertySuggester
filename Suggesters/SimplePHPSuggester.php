<?php

use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;

include "Suggester.php";

function compare_pairs($a, $b){ 
	return $a->getCorrelation() > $b->getCorrelation() ? -1 : 1;
}

class SimplePHPSuggester implements Suggester {
	private $propertyRelations = array();
	
	public function getPropertyRelations(){
		return $this->propertyRelations;
	}
	
	public function suggestionsByAttributes( $attributeValuePairs, $resultSize ) {
		$result = array();
		$k = 0;
		foreach($this->propertyRelations as $key => $attributeCorrelations)
		{
			$aggregateCorrelation = $this->computeAggregateCorrelation($attributeCorrelations, $attributeValuePairs);
			$result[$k] = new Suggestion($key, $aggregateCorrelation, NULL);
			$k++;
		}
		usort($result, 'compare_pairs');
		return $result;
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

	public function suggestionsByEntity( $entity, $resultSize ) {
		$attributeValuePairs = $entity->getAllSnaks();
		return $this->suggestionsByAttributes( $attributeValuePairs, $resultSize );
	}
	
	public function computeTable(){
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$entityPerPage = StoreFactory::getStore( 'sqlstore' )->newEntityPerPage();
		$entityIterator = $entityPerPage->getEntities(Item::ENTITY_TYPE);//WithoutTerm(\Wikibase\Term::TYPE_DESCRIPTION, 'en', Item::ENTITY_TYPE, 100, 0);

		foreach($entityIterator as $key => $value) {
			$entity = $lookup->getEntity($value);
			foreach($entity->getAllSnaks() as $i => $snak1)
			{
				$propertyId1 = $snak1->getPropertyId()->getPrefixedId();
				if(!isset($this->propertyRelations[$propertyId1]['appearances']))
				{
					$this->propertyRelations[$propertyId1]['appearances'] = 0; //init
				}
				$this->propertyRelations[$propertyId1]['appearances']++;
				foreach($entity->getAllSnaks() as $j => $snak2)
				{
					$propertyId2 = $snak2->getPropertyId()->getPrefixedId();
					if(!isset($this->propertyRelations[$propertyId1][$propertyId2]))
					{
						$this->propertyRelations[$propertyId1][$propertyId2] = 0; //init
					}
					if(!($propertyId1 === $propertyId2))
					{
						$this->propertyRelations[$propertyId1][$propertyId2]++;
					}
				}
			}
		}
	}
}
