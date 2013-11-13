<?php

use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\WikiPageEntityLookup;

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

                $id = new EntityId( Item::ENTITY_TYPE, 3 );
                //kramberechnen
                $schnittstelle = new WikiPageEntityLookup();
                $serialisiertes = $schnittstelle->getEntity($id);
                
                $claims = $serialisiertes->getAllSnaks();
                for($i = 0; $i < count($claims); $i++)
                {
                    $out->addHTML("Atrribut ". $i .": " . 
                            $claims[$i]->getPropertyId() . "<br/> Typ: type" . 
                            $claims[$i]->getDataValue()->getType() . "<br/> value: " .
                            $claims[$i]->getDataValue()->getValue() . "<br/><br/>"); //wir wollen die property id id
                }
                
                $out->addHTML("<br/> <br/> \n\n liste: " . $serialisiertes->serialize());
        }
        
}
