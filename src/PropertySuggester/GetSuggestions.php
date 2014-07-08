<?php

namespace PropertySuggester;

use ApiBase;
use ApiMain;
use DerivativeRequest;
use ProfileSection;
use PropertySuggester\Suggesters\SimpleSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\DataModel\Entity\Property;
use Wikibase\EntityTitleLookup;
use Wikibase\Lib\Store\EntityLookup;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\TermIndex;
use Wikibase\Utils;

/**
 * API module to get property suggestions.
 *
 * @licence GNU GPL v2+
 */
class GetSuggestions extends ApiBase {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var EntityTitleLookup
	 */
	private $entityTitleLookup;

	/**
	 * @var SuggesterEngine
	 */
	private $suggester;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	/**
	 * @var SuggesterParamsParser
	 */
	private $paramsParser;

	public function __construct( ApiMain $main, $name, $prefix = '' ) {
		parent::__construct( $main, $name, $prefix );
		global $wgPropertySuggesterDeprecatedIds;
		global $wgPropertySuggesterMinProbability;

		$store = WikibaseRepo::getDefaultInstance()->getStore();
		$this->termIndex = $store->getTermIndex();
		$this->entityLookup = $store->getEntityLookup();
		$this->entityTitleLookup = WikibaseRepo::getDefaultInstance()->getEntityTitleLookup();

		$this->suggester = new SimpleSuggester( wfGetLB() );
		$this->suggester->setDeprecatedPropertyIds( $wgPropertySuggesterDeprecatedIds );

		$this->paramsParser = new SuggesterParamsParser( 500, $wgPropertySuggesterMinProbability );
	}

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		$profiler = new ProfileSection( __METHOD__ );
		$extracted = $this->extractRequestParams();
		$params = $this->paramsParser->parseAndValidate( $extracted );

		$suggestionGenerator = new SuggestionGenerator( $this->entityLookup, $this->termIndex, $this->suggester );

		if ( $params->entity !== null ) {
			$suggestions = $suggestionGenerator->generateSuggestionsByItem( $params->entity, $params->suggesterLimit, $params->minProbability, $params->context );
		} else {
			$suggestions = $suggestionGenerator->generateSuggestionsByPropertyList( $params->properties, $params->suggesterLimit, $params->minProbability, $params->context );
		}
		$suggestions = $suggestionGenerator->filterSuggestions( $suggestions, $params->search, $params->language, $params->resultSize );

		// Build result Array
		$resultBuilder = new ResultBuilder( $this->getResult(), $this->termIndex, $this->entityTitleLookup, $params->search );
		$entries = $resultBuilder->createResultArray( $suggestions, $params->language, $params->search );

		// merge with search result if possible and necessary
		if ( count( $entries ) < $params->resultSize && $params->search !== '' ) {
			$searchResult = $this->querySearchApi( $params->resultSize, $params->search, $params->language );
			$entries = $resultBuilder->mergeWithTraditionalSearchResults( $entries, $searchResult, $params->resultSize );
		}

		// Define Result
		$slicedEntries = array_slice( $entries, $params->continue, $params->limit );
		$this->getResult()->setIndexedTagName( $slicedEntries, 'search' );
		$this->getResult()->addValue( null, 'search', $slicedEntries );

		$this->getResult()->addValue( null, 'success', 1 );
		if ( count( $entries ) >= $params->resultSize ) {
			$this->getResult()->addValue( null, 'search-continue', $params->resultSize );
		}
		$this->getResult()->addValue( 'searchinfo', 'search', $params->search );
	}


	/**
	 * @param int $resultSize
	 * @param string $search
	 * @param string $language
	 * @return array
	 */
	private function querySearchApi( $resultSize, $search, $language ) {
		$profiler = new ProfileSection( __METHOD__ );
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
		return $searchResult;
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
				ApiBase::PARAM_ISMULTI => true
			),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => 7,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2,
				ApiBase::PARAM_MIN => 0,
				ApiBase::PARAM_RANGE_ENFORCE => true,
			),
			'continue' => null,
			'language' => array(
				ApiBase::PARAM_TYPE => Utils::getLanguageCodes(),
				ApiBase::PARAM_DFLT => $this->getContext()->getLanguage()->getCode(),
			),
			'context' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'item',
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
		return array(
			'entity' => 'Suggest attributes for given entity',
			'properties' => 'Identifier for the site on which the corresponding page resides',
			'size' => 'Specify number of suggestions to be returned',
			'language' => 'language for result',
			'limit' => 'Maximal number of results',
			'context' => 'Either item, reference or qualifier',
			'continue' => 'Offset where to continue a search'
		);
	}

	/**
	 * @see ApiBase::getDescription()
	 */
	public function getDescription() {
		return array(
			'API module to get property suggestions (e.g. when editing data entities)'
		);
	}

	/**
	 * @see ApiBase::getExamples()
	 */
	public function getExamples() {
		return array(
			'api.php?action=wbsgetsuggestions&entity=Q4'
			=> 'Get suggestions for entity 4',
			'api.php?action=wbsgetsuggestions&entity=Q4&continue=10&limit=5'
			=> 'Get suggestions for entity 4 from rank 10 to 15',
			'api.php?action=wbsgetsuggestions&properties=P31|P21'
			=> 'Get suggestions for the property combination P21 and P31',
			'api.php?action=wbsgetsuggestions&properties=P21&context=qualifier'
			=> 'Get suggestions for the qualifier that are used with P21',
			'api.php?action=wbsgetsuggestions&properties=P21&context=reference'
			=> 'Get suggestions for the references that are used with P21'
		);
	}

}
