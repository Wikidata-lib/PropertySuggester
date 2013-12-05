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

		if ( ! ( isset( $params['entity'] ) xor isset( $params['properties'])) ) {
			wfProfileOut( __METHOD__ );
			$this->dieUsage( 'provide either entity id parameter "entity" or list of properties "properties"', 'param-missing' );
		}
		
		$resultSize = isset( $params['size']) ? (int)($params['size']) : 10;

		$result = $this->getResult();
		$suggester = new SimplePHPSuggester();
		
		if (isset( $params['entity'] )){
			$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
			$id = new EntityId( Item::ENTITY_TYPE, (int)($params['entity']) );
			$entity = $lookup->getEntity($id);
			$suggestions = $suggester->suggestionsByEntity($entity, $resultSize);
		} else {
			$suggestions = $suggester->suggestionsByAttributeList($params['properties'], $resultSize);
		}
		
		$resultArray = array();
		foreach($suggestions as $rank => $suggestion){
			array_push($resultArray, $suggestion);
		}
		$result->addValue(null, "Suggestions", $resultArray);
		
	//	wfProfileOut( __METHOD__ );
	}

	/**
	 * @see ApiBase::getAllowedParams()
	 */
	public function getAllowedParams() {
		return array(
			'entity' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false,
			),
			'properties' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_ALLOW_DUPLICATES => false
			),
			'size' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false
			)
		);
	}

	/**
	 * @see ApiBase::getParamDescription()
	 */
	public function getParamDescription() {
		return array_merge( parent::getParamDescription(), array(
			'entity' => 'Suggest attributes for given entity',
			'properties' => 'Identifier for the site on which the corresponding page resides',
			'size' => 'Specify number of suggestions to be returned'
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
