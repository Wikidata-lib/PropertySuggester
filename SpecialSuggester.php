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
		
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new EntityId( Item::ENTITY_TYPE, 2 );
		$entity = $lookup->getEntity($id);
		$resultWrapper = $suggester->suggestionsByEntity($entity, 10);
		foreach($resultWrapper as $key => $row)	{
			$out->addHTML("property id:" . $row->pid2. " correlation:" .  $row->cor . "<br/>");
		}
	}
}

