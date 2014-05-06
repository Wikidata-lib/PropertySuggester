<?php

namespace PropertySuggester;

use ApiBase;
use PropertySuggester\Suggester\PropertySuggester;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Utils;
use InvalidArgumentException;

/**
 * API module to get property suggestions.
 *
 * @licence GNU GPL v2+
 */
class GetPropertySuggestions extends GetSuggestionsApiBase {

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		global $wgPropertySuggesterDeprecatedIds;
		global $wgPropertySuggesterMinProbability;

		$requestParams = $this->extractRequestParams();
		if ( !( $requestParams['entity'] XOR $requestParams['properties'] ) ) {
			throw new InvalidArgumentException( 'provide either entity-id parameter \'entity\' or a list of properties \'properties\'' );
		}
		$paramsParser = new ParamsParser( 500, $wgPropertySuggesterMinProbability );
		$params = $paramsParser->parseAndValidate( $requestParams, $this->getRequest() );

		$suggester = new PropertySuggester( wfGetLB( DB_SLAVE ), $params->internalResultListSize, $params->minProbability );
		$suggester->setDeprecatedPropertyIds( $wgPropertySuggesterDeprecatedIds );
		$suggester->setItem( $this->getItemFromId( $requestParams['entity'] ) );

		$searchResult = new SearchResultWithSuggestions( $suggester, $params, Property::ENTITY_TYPE );

		$this->buildResult( $searchResult, $params->internalResultListSize, $params->search );
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
		return array(
			'entity' => 'Suggest attributes for given entity',
			'properties' => 'Identifier for the site on which the corresponding page resides',
			'size' => 'Specify number of suggestions to be returned',
			'language' => 'language for result',
			'limit' => 'Maximal number of results',
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
	protected function getExamples() {
		return array(
			'api.php?action=wbsgetsuggestions&entity=Q4'
			=> 'Get suggestions for entity 4',
			'api.php?action=wbsgetsuggestions&entity=Q4&continue=10&limit=5'
			=> 'Get suggestions for entity 4 from rank 10 to 15',
			'api.php?action=wbsgetsuggestions&properties=P31|P21'
			=> 'Get suggestions for the property combination P21 and P31'
		);
	}

}
