<?php

namespace PropertySuggester;

use Html;
use PropertySuggester\Suggesters\SimpleSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\Repo\Specials\SpecialWikibaseRepoPage;
use Wikibase\Repo\WikibaseRepo;

class SpecialEditItemSuggester extends SpecialWikibaseRepoPage
{

	/**
	 * @var SuggesterEngine
	 */
	protected $suggester;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'PropertySuggester', '' );
		$this->language = $this->getContext()->getLanguage()->getCode();

		$lb = wfGetLB( DB_SLAVE );
		$this->suggester = new SimpleSuggester( $lb );
		global $wgPropertySuggesterDeprecatedIds;
		$this->suggester->setDeprecatedPropertyIds($wgPropertySuggesterDeprecatedIds);
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the  page
	 * @return bool|void
	 */
	public function execute( $par ) {
		$out = $this->getContext()->getOutput();

		$this->setHeaders();
		$out->addStyle( '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
		$out->addModules( 'ext.PropertySuggester' );

		$out->addHTML( '<p>Just enter a property and the module will show you Items that are most likely missing it: <br/>'
		);

		$url = $out->getRequest()->getRequestURL();
		$out->addHTML( "<form action='$url' method='post' id ='form'>" );
		$out->addHTML( "<input placeholder='Property' id='entity-chooser' name='entity-chooser' autofocus>" );
		$out->addHTML( "<input value='Send' id='add-property-btn2' type='submit'  >" );
		$out->addElement("input", array("type"=> "hidden", "name" => "result", 'id'=>'result'));
		$out->addHTML( "<br/>" );
		$prop = $out->getRequest()->getText( "entity-chooser" );
		if ( $prop ) {
			$property = $this->get_the_property( $prop, $out );

			for ($i = 2; $i <= 20000; $i++) {
				$id = $this->parseEntityId( 'Q'.$i );
				try{
					$item = $this->loadEntity( $id )->getEntity();
				}
				catch (\UserInputException $e){
					continue;
				}
				$suggestions = $this->suggester->suggestByItem( $item, 7 );

				foreach ( $suggestions as $suggestion ) {
					$suggestion_pid = $suggestion->getPropertyId();
					$suggestion_label = $this->loadEntity( $suggestion_pid )->getEntity()->getLabel( $this->language );
					$chosenPropertyLabel = $property->getLabel( $this->language );

					if( $suggestion_label === $chosenPropertyLabel ){
						$this->add_item( $item, $id, $out );
						break;
					}
				}
			}
		}
	}

	/**
	 * @param $suggestion
	 * @param $out
	 */
	public function add_item( $item, $id, $out ) {
		$label = $item->getLabel( $this->language );
		$url = $this->getEntityTitle( $id )->getFullUrl();
		$out->addHTML( "<a href=" . $url .">" . $label . " (" . $id . ")</a> <br>");
	}

	/**
	 * @param $entity
	 * @param $out
	 * @return Entity
	 */
	public function get_the_property( $entity, $out ) {
		$propId = $this->parseEntityId( $entity );
		$property = $this->loadEntity( $propId )->getEntity();
		$label = $property->getLabel( $this->language );
		$this->add_elements( $out, $label, $propId );
		return $property;
	}

	/**
	 * @param $out
	 * @param $label
	 */
	public function add_elements( $out, $label ) {
		$out->addElement( 'h2', null, "Chosen Property: $label" );
	}
}

