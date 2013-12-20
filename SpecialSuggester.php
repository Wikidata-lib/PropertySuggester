<?php

include 'Suggesters/SimplePHPSuggester.php';

use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;
//use Wikibase.ui.entitysearch;

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
                  
                $out->addHTML("<input placeholder='Property' id='property-chooser' class='ui-autocomplete-input' autofocus>");
              
                $out->addHTML("<input type='button' value='Add' id='add-property-btn'></input>");
                
                $out->addHTML("<p/>");
                
                $out->addHtml("<ul id='selected-properties-list'></ul>");                               
                
                $out->addHtml("<p/>");
                
                $out->addHTML("<div id='result'></div>");
        }
}

