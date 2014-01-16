<?php

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Item;
use Wikibase\Property;
use Wikibase\StoreFactory;
//ToDo: use Wikibase\LanguageFallbackChainFactory;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Utils;

include 'Suggesters/SimplePHPSuggester.php';

/**
 * API module to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */


function cleanPropertyId($propertyId) {
    if ($propertyId[0] === 'P') {
            return (int)substr($propertyId, 1);
    }
    return (int)$propertyId;
}


class GetSuggestions extends ApiBase {

    public function __construct( ApiMain $main, $name, $search = '' ) {
            parent::__construct( $main, $name, $search );
    }

    /**
     * @see ApiBase::execute()
     */
    public function execute() {
        $params = $this->extractRequestParams();
        if ( ! ( isset( $params['entity'] ) xor isset( $params['properties'])) ) {
                wfProfileOut( __METHOD__ );
                $this->dieUsage( 'provide either entity id parameter "entity" or list of properties "properties"', 'param-missing' );
        }
        $resultSize = isset( $params['size']) ? (int)($params['size']) : 10;
        $suggester = new SimplePHPSuggester(); 
        $lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
        if (isset( $params['entity'] )){
                $id = new  ItemId($params['entity']);
                $entity = $lookup->getEntity($id);
                $suggestions = $suggester->suggestionsByItem($entity, 10);
        } else {
                $list = $params['properties'][0];
                $splitted_list = explode(",", $list);
                $int_list = array_map("cleanPropertyId", $splitted_list);
                $suggestions = $suggester->suggestionsByAttributeList($int_list, 10);
        }
		if(!isset($params['language'])){				                             //ToDo: Fallback
				$params['language'] = $property->getLabel('en');
		}
        $entries = $this->createJSON($suggestions, $params['language'], $lookup);
		if(isset($params['search']))
		{
			$entries = $this->filterByPrefix($entries, $params['search']);
		}
        $this->getResult()->addValue(null, 'search', $entries);
    }
	
	public function filterByPrefix($entries, $search)
	{
		$matchingEntries = array();
		foreach($entries as $entry)
		{
			if(preg_match("/\b($search\w*)\b/i", $entry["label"]))                
			{
				$matchingEntries[] = $entry;
			}
		}
		return $matchingEntries;
	}
	
	
	public function createJSON($suggestions, $language, $lookup) {
		$entries = array();
        foreach($suggestions as $suggestion){
            $entry = array();
            $id = new PropertyId("P" . $suggestion->getPropertyId());
            $property = $lookup->getEntity($id);
            $entry["id"] = "P".$suggestion->getPropertyId();
            $entry["url"] = "http://127.0.0.1/devrepo/w/index.php/Property:" . $entry["id"]; //does this always work?
			$entry["description"] = $property->getDescription($language);
            $entry["label"] = $property->getLabel($language);   
            $entries[] = $entry;
        }
		return $entries;
	}

    /**
     * @see ApiBase::getAllowedParams()
     */
    public function getAllowedParams() {
        return array(
            'entity' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false
            ),
            'properties' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_ALLOW_DUPLICATES => false
            ),
            'size' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false
            ),
            'language' => array(
				ApiBase::PARAM_TYPE => Utils::getLanguageCodes(),
				ApiBase::PARAM_ISMULTI => false
            ),
			'search' => array(
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
                    'size' => 'Specify number of suggestions to be returned',
                    'language' => 'language for result'
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
