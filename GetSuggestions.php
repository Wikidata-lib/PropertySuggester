<?php
//ToDo: use Wikibase\LanguageFallbackChainFactory;

use Wikibase\Api\SearchEntities;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\StoreFactory;
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
        
        if (isset($params['search']) && $params['search'] != "*") {
            $search = $params['search'];
        } else {
            $search = "";
        }
                
        $limit = $params['limit'];
        $continue = $params['continue'];
        
        $suggester = new SimplePHPSuggester(); 
        $lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
        if (isset( $params['entity'] )){
                $id = new  ItemId($params['entity']);
                $entity = $lookup->getEntity($id);

                $suggestions = $suggester->suggestionsByItem($entity, 1000);
        } else {
                $list = $params['properties'][0];
                $splitted_list = explode(",", $list);
                $int_list = array_map("cleanPropertyId", $splitted_list);

                $suggestions = $suggester->suggestionsByAttributeList($int_list, 1000);
        }
        
        $language = "en";
		if(isset($params['language'])){ // TODO: use fallback
				$language = $params['language'];
		}
        
        $entries = $this->createJSON($suggestions, $language, $lookup);
		
		if($search)
		{
			$entries = $this->filterByPrefix($entries, $search);
		}
        
        $sliced_entries = array_slice($entries, $continue, $limit);
		
		if(count(sliced_entries) < $limit && $search)
		{
			$apicallcontinue = $continue - count($sliced_entries);
			$apicallcontinue = $apicallcontinue < 0 ? 0 : $apicallcontinue;
			$searchEntitiesParameters = new DerivativeRequest( 
				$this->getRequest(),
				array(
				'limit' => $limit, //search results can overlap with suggestions. Think!
				'continue' => $apicallcontinue,
				'search' => $search,
				'action' => 'wbsearchentities',
				'language' => $language,
				'type' => \Wikibase\Property::ENTITY_TYPE)
			);
			
			
			$api = new ApiMain($searchEntitiesParameters);
			$api->execute();
			$searchEntitesResult = $api->getResultData();
			$searchResult = $searchEntitesResult['search'];
			$noDuplicateEntries = array();
			$distinctCount = 0;
			foreach($searchResult as $sr)
			{
				$duplicate = false;
				foreach($sliced_entries as $sug)
				{
					if($sr["id"] === $sug["id"])
					{
						$duplicate = true;
						break;
					}
				}
				if(!$duplicate)
				{
					$noDuplicateEntries[] = $sr;
					$distinctCount++;
					if($distinctCount > $limit - count(sliced_entries))
					{
						break;
					}
				}
			}
			$sliced_entries = array_merge($sliced_entries, $noDuplicateEntries);
		}
		
		
		
        $this->getResult()->addValue(null, 'search', $sliced_entries);
        $this->getResult()->addValue(null, 'success', 1);
        if ( count($entries) > $continue + $limit) {
            $this->getResult()->addValue(null, 'search-continue', $continue + $limit);
        }
        $this->getResult()->addValue('searchinfo', 'search', $search );
    }
	
	public function filterByPrefix($entries, $search)
	{
		$matchingEntries = array();
		foreach($entries as $entry)
		{
			if( 0 == strcasecmp( $search, substr($entry["label"], 0, strlen($search) ) ) )                
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
            if ($property == null) {
                continue;
            }
            $entry["id"] = "P".$suggestion->getPropertyId();
            $entry["label"] = $property->getLabel($language);   
			$entry["description"] = $property->getDescription($language);
			$entry["correlation"] = $suggestion->getCorrelation();
            $entry["url"] = "http://127.0.0.1/devrepo/w/index.php/Property:" . $entry["id"]; //TODO get url!
			$entry["debug:type"] = "suggestion"; //debug
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
            ),
            'properties' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_ALLOW_DUPLICATES => false
            ),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => 7,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2,
				ApiBase::PARAM_MIN => 0,
				ApiBase::PARAM_RANGE_ENFORCE => true,
			),
			'continue' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2,
				ApiBase::PARAM_MIN => 0,
				ApiBase::PARAM_RANGE_ENFORCE => true,
			),
            'language' => array(
				ApiBase::PARAM_TYPE => Utils::getLanguageCodes(),
            ),
			'search' => array(
				ApiBase::PARAM_TYPE => 'string',
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
                    'language' => 'language for result',
					'limit' => 'Maximal number of results',
					'continue' => 'Offset where to continue a search'
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
