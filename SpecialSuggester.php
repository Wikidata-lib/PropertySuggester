<?php

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
		$out->addHTML( "hihihihihihi" );

	}
}
