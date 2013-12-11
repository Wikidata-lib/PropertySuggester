<?php

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\StoreFactory;
use Wikibase\Property;

include '/Suggesters/SimpleMisfitSuggester.php';

/**
 * API module to get suggestions for misfit properties.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */


function cleanProperty($propertyId) {
    if ($propertyId[0] === 'P') {
            return (int)substr($propertyId, 1);
    }
    return (int)$propertyId;
}



class GetMisfits extends ApiBase {

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
		
		$threshold = isset( $params['threshold']) ? (float)($params['threshold']) : 0.05;

		$result = $this->getResult();
		$suggester = new SimpleMisfitSuggester(); 
                $lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		if (isset( $params['entity'] )){
			$id = new EntityId( Item::ENTITY_TYPE, (int)($params['entity']) );
			$entity = $lookup->getEntity($id);
			$suggestions = $suggester->suggestMisfitsByEntity($entity, $threshold);
		} else {
                        $list = $params['properties'][0];
                        $splitted_list = explode(",", $list);
                        $int_list = array_map("cleanProperty", $splitted_list);
			$suggestions = $suggester->suggestMisfitsByAttributList($int_list, $threshold);
		}
		foreach($suggestions as $suggestion){
                        $entry = array();
                        $id = new PropertyId("P" . $suggestion->getPropertyId());
                        $property = $lookup->getEntity($id);
                        $entry["name"] = $property->getLabel('de');
                        $entry["id"] = $suggestion->getPropertyId();
                        $entry["correlation"] = $suggestion->getCorrelation();
                        $entries[] = $entry;
                }
                $result->addValue(null, "suggestions", $entries);
        
         }		
	//	wfProfileOut( __METHOD__ );

        

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
			'threshold' => array(
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
			'entity' => 'Entity which will be analyzed to find misfit properties',
			'properties' => 'Set of properties which will be analyzed to find misfits',
			'threshold' => 'Only properties with a correlation-level smaller than this threshold will be regarded as misfits (default = 0.05)'
		) );
	}

	/**
	 * @see ApiBase::getDescription()
	 */
	public function getDescription() {
		return array(
			'API module for finding misfits in a set of properties.'
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