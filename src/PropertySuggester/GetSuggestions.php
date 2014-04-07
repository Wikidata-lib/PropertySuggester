<?php

namespace PropertySuggester;

use ApiBase;
use ApiMain;
use DerivativeRequest;
use PropertySuggester\Suggesters\SimpleSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\ResultBuilder;
use Wikibase\DataModel\Entity\Property;
use Wikibase\EntityLookup;
use Wikibase\StoreFactory;
use Wikibase\Utils;

/**
 * API module to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */
class GetSuggestions extends ApiBase {

	/**
	 * @var EntityLookup
	 */
	private $lookup;

	/**
	 * @var SuggesterEngine
	 */
	private $suggester;

	public function __construct( ApiMain $main, $name, $prefix = '' ) {
		parent::__construct( $main, $name, $prefix );
		$this->lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$this->suggester = new SimpleSuggester( wfGetLB( DB_SLAVE ) );

		global $wgPropertySuggesterDeprecatedIds;
		$this->suggester->setDeprecatedPropertyIds($wgPropertySuggesterDeprecatedIds);
	}

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		wfProfileIn( __METHOD__ );
		$params = $this->extractRequestParams();

		// parse params
		if ( !( $params['entity'] XOR $params['properties'] ) ) {
			wfProfileOut( __METHOD__ );
			$this->dieUsage( 'provide either entity id parameter \'entity\' or a list of properties \'properties\'', 'param-missing' );
		}

		// The entityselector doesn't allow a search for '' so '*' gets mapped to ''
		if ( $params['search'] !== '*' ) {
			$search = $params['search'];
		} else {
			$search = '';
		}

		$language = $params['language'];
		$resultSize = $params['continue'] + $params['limit'];

		$helper = new GetSuggestionsHelper( $this->lookup, $this->suggester );

		if ( $params["entity"] !== null ) {
			$suggestions = $helper->generateSuggestionsByItem( $params["entity"] );
		} else {
			$suggestions = $helper->generateSuggestionsByPropertyList( $params['properties'] );
		}

		// Build result Array
		$resultBuilder = new ResultBuilder();
		$entries = $resultBuilder->createJSON( $suggestions, $language, $search );
		foreach ( $entries as $entry ) {
			if ( !isset( $entry['aliases'] ) ) {
				$this->getResult()->setIndexedTagName( $entry['aliases'], 'alias' );
			}
		}
		if ( $search ) {
			$entries = $resultBuilder->filterByPrefix( $entries, $search ); //refactor do in create Json
		}

		// merge with search result if possible and necessary
		if ( count( $entries ) < $resultSize && $search !== '' ) {

			//Do search api request
			$searchEntitiesParameters = new DerivativeRequest(
				$this->getRequest(),
				array(
					'limit' => $resultSize + 1,
					'continue' => 0,
					'search' => $search,
					'action' => 'wbsearchentities',
					'language' => $language,
					'type' => Property::ENTITY_TYPE
				)
			);
			$api = new ApiMain( $searchEntitiesParameters );
			$api->execute();
			$apiResult = $api->getResultData();
			$searchResult = $apiResult['search'];
			$entries = $resultBuilder->mergeWithTraditionalSearchResults( $entries, $searchResult, $resultSize );
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
				ApiBase::PARAM_DFLT => $this->getContext()->getLanguage()->getCode(),
			),
			'search' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
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
		return array(
			'api.php?action=wbsgetsuggestions&format=json&entity=Q4'
			=> 'Get suggestions for entity 4',
			'api.php?action=wbsgetsuggestions&format=json&entity=Q4&continue=10&limit=5'
			=> 'Get suggestions for entity 4 from rank 10 to 15',
			'api.php?action=wbsgetsuggestions&format=json&properties=P31,P21'
			=> 'Get suggestions for the property combination P21 and P31'
		);
	}
}
