<?php

namespace PropertySuggester;

use ApiBase;
use ApiMain;
use PropertySuggester\Suggester\ItemSuggester;
use PropertySuggester\ValueSuggesters\ValueSuggester;
use PropertySuggester\ValueSuggesters\ValueSuggesterEngine;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\EntityLookup;
use Wikibase\StoreFactory;
use Wikibase\TermIndex;
use Wikibase\Utils;

/**
 * API module to get property suggestions.
 *
 * @licence GNU GPL v2+
 */
class GetItemSuggestions extends GetSuggestionsApiBase {


	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		global $wgPropertySuggesterMinProbability;

		$requestParams = $this->extractRequestParams();
		$paramsParser = new ParamsParser( 500, $wgPropertySuggesterMinProbability );
		$params = $paramsParser->parseAndValidate( $requestParams, $this->getRequest() );

		$suggester = new ItemSuggester( wfGetLB( DB_SLAVE ) );
		$suggester->setItem( $this->getItemFromId( $requestParams['item'] ) );
		$suggester->setNumericPropertyId( substr($requestParams['property'],1) );
		$suggester->setMinProbability( $requestParams['threshold'] );

		$searchResult = new SearchResultWithSuggestions( $suggester, $params, Item::ENTITY_TYPE );

		$this->buildResult( $searchResult, $params->internalResultListSize, $params->search );
	}


	/**
	 * @see ApiBase::getAllowedParams()
	 */
	public function getAllowedParams() {
		return array(
			'item' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'property' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'threshold' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
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
			'item' => 'Id of treated entity.',
			'property' => 'Id of property for which values should be shown',
			'threshold' => 'Minimal probability',
			'language' => 'Language of value suggestion labels'
		);
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
			'api.php?action=wbsgetvaluesuggestions&format=json&item=5&property=21&threshold=0.025'
			=> 'Get suggestions for property 21 for item 5',
		);
	}

}
