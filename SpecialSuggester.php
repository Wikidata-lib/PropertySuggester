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

		
		$out->addHTML("Correlation values for all Attributes regarding item Q2 'Moritz Finke': <br />");
		
		//Suggestor testen:
		$suggester = new SimplePHPSuggester();
		$suggester->computeTable();
		
		$this->printTable($suggester->getPropertyRelations(), $out);
		
		$schnittstelle = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new EntityId( Item::ENTITY_TYPE, 2 );
		$entity = $schnittstelle->getEntity($id);
		
		$results = $suggester->suggestionsByEntity($entity, 10);
		foreach($results as $rank => $suggestion)
		{
			$out->addHTML($rank+1 . ". " . $suggestion->getPropertyId() . ":  " . $suggestion->getCorrelation() . "<br />");
		}
	}
	
	function incrementOrInit()
	{
	}
	
	function printTable($propertyRelations, $out)
	{
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$entityPerPage = StoreFactory::getStore( 'sqlstore' )->newEntityPerPage();
		
		$properties = array();
		$propertyIterator = $entityPerPage->getEntities(Property::ENTITY_TYPE);
		foreach($propertyIterator as $key => $value)  {
			$properties[] = $lookup->getEntity($value);
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
			if(isset($propertyRelations[$propertyId1])){
				$out->addHTML("<td>".$propertyRelations[$propertyId1]['appearances']."</td>");
			}else{
				$out->addHTML("<td>0</td>");
			}
			foreach ($properties as $key => $value)
			{
				$propertyId2 = $value->getId()->getPrefixedId();
				$out->addHTML("<td>");
				if(isset($propertyRelations[$propertyId1][$propertyId2])){
					$out->addHTML($propertyRelations[$propertyId1][$propertyId2]);
				}else{
					$out->addHTML("-");
				}
				$out->addHTML("</td>");
			}
			$out->addHTML("</tr>");
		}
		$out->addHTML("</tbody></table>");
	}
}

