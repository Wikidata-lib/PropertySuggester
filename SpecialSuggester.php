<?php

include '/Suggesters/SimplePHPSuggester.php';

use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;

class SpecialSuggester extends SpecialPage {
	
        /**
         * Constructor
         */
	public function __construct() {
		parent::__construct( 'Suggester', '', true );
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the page
	 */
	public function execute( $par ) {

		$out = $this->getContext()->getOutput();

		$this->setHeaders();
		$out->addModules( 'ext.Suggester' );

		$out->addWikiMsg( 'suggester-intro' );
		$out->addHTML( "hihihihihihi <br/> <br/> " );

	   /* $id = new EntityId( Item::ENTITY_TYPE, 3 );
		//kramberechnen
		$schnittstelle = new WikiPageEntityLookup();
		$serialisiertes = $schnittstelle->getEntity($id);

		$claims = $serialisiertes->getAllSnaks();
		for($i = 0; $i < count($claims); $i++)
		{
			$out->addHTML("Atrribut ". $i .": " . 
					$claims[$i]->getPropertyId() . "<br/> Typ: type" . 
					$claims[$i]->getDataValue()->getType() . "<br/> value: " .
					$claims[$i]->getDataValue()->getValue() . "<br/><br/>"); //wir wollen die property id 
		}

		$out->addHTML("<br/> <br/> \n\n liste: " . $serialisiertes->serialize());*/

		
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$entityPerPage = StoreFactory::getStore( 'sqlstore' )->newEntityPerPage();
		$entityIterator = $entityPerPage->getEntities(Item::ENTITY_TYPE);//WithoutTerm(\Wikibase\Term::TYPE_DESCRIPTION, 'en', Item::ENTITY_TYPE, 100, 0);

		$propertyRelations = array();

		foreach($entityIterator as $key => $value) {
			$entity = $lookup->getEntity($value);
			$out->addHTML("<br/> <br/>" . $entity->serialize());
			foreach($entity->getAllSnaks() as $i => $snak1)
			{
				$propertyId1 = $snak1->getPropertyId()->getPrefixedId();
			   // $propertyRelations[$propertyId1] = array();
				if(!isset($propertyRelations[$propertyId1]['appearances']))
				{
					$propertyRelations[$propertyId1]['appearances'] = 0; //init
				}
				$propertyRelations[$propertyId1]['appearances']++;
				foreach($entity->getAllSnaks() as $j => $snak2)
				{
					$propertyId2 = $snak2->getPropertyId()->getPrefixedId();
					if(!isset($propertyRelations[$propertyId1][$propertyId2]))
						$propertyRelations[$propertyId1][$propertyId2] = 0; //init
					$propertyRelations[$propertyId1][$propertyId2]++;
				}
			}
		}
		
		$properties = array();
		$propertyIterator = $entityPerPage->getEntities(Property::ENTITY_TYPE);
		foreach($propertyIterator as $key => $value)  {
			$properties[] = $lookup->getEntity($value);;
		}
		
		$out->addHTML("<table border='1'><thead><tr>");
		$out->addHTML("<th></th><th>Name</th><th>Appearances</th>");
		foreach ($properties as $key => $value) {
			$out->addHTML("<th>".$value->getLabel('de')."(".$value->getId()->getPrefixedId().")</th>");
		}
		$out->addHTML("</tr></thead><tbody>");
		foreach ($properties as $key => $value) {
			$propertyId1 = $value->getId()->getPrefixedId();
			$out->addHTML("<tr><td>".$propertyId1."</td>");
			$out->addHTML("<td>".$value->getLabel('de')."</td>");
			if(isset($propertyRelations[$propertyId1]))
				$out->addHTML("<td>".$propertyRelations[$propertyId1]['appearances']."</td>");
			else
				$out->addHTML("<td>0</td>");
			foreach ($properties as $key => $value)
			{
				$propertyId2 = $value->getId()->getPrefixedId();
				$out->addHTML("<td>");
				if(isset($propertyRelations[$propertyId1][$propertyId2]))
					$out->addHTML($propertyRelations[$propertyId1][$propertyId2]);
				else
					$out->addHTML("-");
				$out->addHTML("</td>");
			}
			$out->addHTML("</tr>");
		}
		$out->addHTML("</tbody></table>");
		$out->addHTML("Correlation values for all Attributes regarding item Q2 'Moritz Finke': <br />");
		
		//Suggestor testen:
		$suggestor = new SimplePHPSuggester();
		$suggestor->computeTable();
		
		$schnittstelle = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new EntityId( Item::ENTITY_TYPE, 2 );
		$entity = $schnittstelle->getEntity($id);
		
		$results = $suggestor->suggestionsByEntity($entity, 10);
		foreach($results as $rank => $correlation)
		{
			$out->addHTML($rank+1 . ". " . $correlation['id'] . ":  " . $correlation['correlation'] . "<br />");
		}
	}
	
	function incrementOrInit()
	{
		
	}
}
