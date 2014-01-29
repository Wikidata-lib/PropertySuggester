<?php
// ToDo: use Wikibase\LanguageFallbackChainFactory;


use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Property;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\StoreFactory;
use Wikibase\Utils;

include 'Suggesters/SimplePHPSuggester.php';

/**
 * API module to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */

function cleanPropertyId( $propertyId ) {
	if ( $propertyId[0] === 'P' ) {
		return (int)substr( $propertyId, 1 );
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

		// parse params
		if ( ! ( isset( $params['entity'] ) xor isset( $params['properties'] ) ) ) {
			wfProfileOut( __METHOD__ );
			$this->dieUsage( 'provide either entity id parameter \'entity\' or list of properties \'properties\'', 'param-missing' );
		}
		$search = '';
		if ( isset( $params['search'] ) && $params['search'] != '*' ) {
			$search = $params['search'];
		}
		$language = 'en';
		if ( isset( $params['language'] ) ) { // TODO: use fallback
			$language = $params['language'];
		}
		
		$resultSize = $params['continue'] + $params['limit'];

		$entries = $this->generateSuggestions( $params["entity"], $params['properties'], $search, $language );

		if ( count( $entries ) < $resultSize && $search !== '' ) {
			$entries = $this->mergeWithTraditionalSearchResults( $entries, $resultSize, $search, $language );
		}

		// Define Result
		$slicedEntries = array_slice( $entries, $params['continue'], $params['limit'] );
		$this->getResult()->addValue( null, 'search', $slicedEntries );
		$this->getResult()->addValue( null, 'success', 1 );
		if ( count( $entries ) > $resultSize ) {
			$this->getResult()->addValue( null, 'search-continue', $resultSize );
		}
		$this->getResult()->addValue( 'searchinfo', 'search', $search );
	}

	public function generateSuggestions( $entity, $propertyList, $search, $language )
	{
		$suggester = new SimplePHPSuggester();
		$lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		if ( $entity !== null ) {
			$id = new  ItemId( $entity );
			$entity = $lookup->getEntity( $id );
			$suggestions = $suggester->suggestionsByItem( $entity );
		} else {
			$splittedList = explode( ',', $propertyList );
			$intList = array_map( 'cleanPropertyId', $splittedList );
			$suggestions = $suggester->suggestionsByAttributeList( $intList );
		}

		// Build result Array
		$entries = $this->createJSON( $suggestions, $language, $lookup );
		if ( $search )	{
			$entries = $this->filterByPrefix( $entries, $search );
		}
		return $entries;
	}

	public function mergeWithTraditionalSearchResults( & $entries, $resultSize, $search, $language )
	{
		$searchEntitiesParameters = new DerivativeRequest(
			$this->getRequest(),
			array(
			'limit' => $resultSize + 1,
			'continue' => 0,
			'search' => $search,
			'action' => 'wbsearchentities',
			'language' => $language,
			'type' => Property::ENTITY_TYPE )
		);
		$api = new ApiMain( $searchEntitiesParameters );
		$api->execute();
		$apiResult = $api->getResultData();
		$searchResult = $apiResult['search'];

		// Avoid duplicates
		$existingKeys = array();
		foreach ( $entries as $entry ) {
			$existingKeys[$entry['id']] = true;
		}

		$distinctCount = count( $entries );
		foreach ( $searchResult as $sr ) {
			if ( !array_key_exists($sr['id'], $existingKeys) ) {
				$entries[] = $sr;
				$distinctCount++;
				if ( $distinctCount > $resultSize ) {
					break;
				}
			}
		}
		return $entries;
	}

	protected function filterByPrefix( $entries, $search )
	{
		$matchingEntries = array();
		foreach ( $entries as $entry ) {
			if ($this->isMatch($entry, $search)) {
				$matchingEntries[] = $entry;
			}
		}
		return $matchingEntries;
	}
	
	protected function isMatch( $entry, $search ) {
		if ( stripos( $entry['label'], $search ) === 0 ) {
			return true;
		}
		foreach ( $entry['aliases'] as $alias ) {
			if ( stripos( $alias, $search ) === 0 ) {
				return true;
			}
		}
		return false;
	}
	
	public function createJSON( $suggestions, $language, $lookup ) {
		$entries = array();
		foreach ( $suggestions as $suggestion ) {
			$entry = array();
			$id = $suggestion->getPropertyId();
			$property = $lookup->getEntity( $id );
			$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();

			if ( $property === null ) {
				continue;
			}
			$entry['id'] = $id->getPrefixedId();
			$entry['label'] = $property->getLabel( $language );
			if ( $aliases = $property->getAliases( $language ) ) {
				$entry['aliases'] = $aliases;
			}
			$entry['description'] = $property->getDescription( $language );
			$entry['correlation'] = $suggestion->getCorrelation();
			$entry['url'] = $entityContentFactory->getTitleForId( $id )->getFullUrl();
			$entry['debug_type'] = 'suggestion'; // debug
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
