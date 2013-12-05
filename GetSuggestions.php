<?php

use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\StoreFactory;

include '/Suggesters/SimplePHPSuggester.php';

/**
 * API module to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 * @author John Erling Blad < jeblad@gmail.com >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Marius Hoch < hoo@online.de >
 * @author Michał Łazowik
 */


class GetSuggestions extends ApiBase {

	public function __construct( ApiMain $main, $name, $prefix = '' ) {
		parent::__construct( $main, $name, $prefix );
	}

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
	//	wfProfileIn( __METHOD__ );

		$params = $this->extractRequestParams();

		if ( !isset( $params['entityid'] ) ) {
			wfProfileOut( __METHOD__ );
			$this->dieUsage( 'provide parameter "entityid"', 'param-missing' );
		}

		$result = $this->getResult();
		
		$suggester = new SimplePHPSuggester();
		
		$schnittstelle = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new EntityId( Item::ENTITY_TYPE, (int)($params['entityid']) );
		$entity = $schnittstelle->getEntity($id);
		
		$results = $suggester->suggestionsByEntity($entity, 10);
		foreach($results as $rank => $suggestion)
		{
			$result->addValue("", "attribut $rank", $suggestion->getPropertyId());
		}
		
		$success = true;

		$result->addValue(
			null,
			'success',
			(int)$success
		);

	//	wfProfileOut( __METHOD__ );
	}

	/**
	 * @see ApiBase::getAllowedParams()
	 */
	public function getAllowedParams() {
		return array(
			'entityid' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false,
			),
			'properties' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_ALLOW_DUPLICATES => false
			)
		);
	}

	/**
	 * @see ApiBase::getParamDescription()
	 */
	public function getParamDescription() {
		return array_merge( parent::getParamDescription(), array(
			'entityid' => 'Suggest attributes for given entity',
			'properties' => 'Identifier for the site on which the corresponding page resides'
		) );
	}

	/**
	 * @see ApiBase::getDescription()
	 */
	public function getDescription() {
		return array(
			'API module to get property suggestions.'
		);
	}

	/**
	 * @see ApiBase::getPossibleErrors()
	 */
	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'param-missing', 'info' => $this->msg( 'wikibase-api-param-missing' )->text() )
		) );
	}

	/**
	 * @see ApiBase::getExamples()
	 */
	protected function getExamples() {
		return array();
	}

}
