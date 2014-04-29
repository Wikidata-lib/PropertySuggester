<?php

namespace PropertySuggester;

use ApiBase;
use ApiMain;
use PropertySuggester\ValueSuggesters\ValueSuggester;
use PropertySuggester\ValueSuggesters\ValueSuggesterEngine;
use Wikibase\EntityLookup;
use Wikibase\StoreFactory;
use Wikibase\TermIndex;
use Wikibase\Utils;

/**
 * API module to get property suggestions.
 *
 * @licence GNU GPL v2+
 */
class GetValueSuggestions extends ApiBase {

	/**
	 * @var EntityLookup
	 */
	private $lookup;

	/**
	 * @var ValueSuggesterEngine
	 */
	private $valueSuggester;

	/**
	 * @var TermIndex
	 */
	private $termIndex;

	public function __construct( ApiMain $main, $name, $prefix = '' ) {
		parent::__construct( $main, $name, $prefix );
		$this->lookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$this->termIndex = StoreFactory::getStore( 'sqlstore' )->getTermIndex();
		$this->valueSuggester = new ValueSuggester( wfGetLB( DB_SLAVE ) );
	}

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		wfProfileIn( __METHOD__ );
		$params = $this->extractRequestParams();

		$suggestions = $this->valueSuggester->getValueSuggestionsByItem($params["item"], $params["property"], (float) $params["threshold"]);

		// Build result Array
		$resultBuilder = new ResultBuilder( $this->getResult(), '' );
		$entries = $resultBuilder->createJSON( $suggestions, $params["language"], 'item' );

		// Define Result
		$this->getResult()->addValue( null, 'search', $entries );
		$this->getResult()->addValue( null, 'success', 1 );
	}



	/**
	 * @see ApiBase::getAllowedParams()
	 */
	public function getAllowedParams() {
		return array(
			'item' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'property' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'threshold' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'language' => array(
				ApiBase::PARAM_TYPE => Utils::getLanguageCodes(),
				ApiBase::PARAM_DFLT => $this->getContext()->getLanguage()->getCode(),
			)
		);
	}

	/**
	 * @see ApiBase::getParamDescription()
	 */
	public function getParamDescription() {
		return array_merge( parent::getParamDescription(), array(
			'item' => 'Id of treated entity.',
			'property' => 'Id of property for which values should be shown',
			'threshold' => 'Minimal probability',
			'language' => 'Language of value suggestion labels'
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
			'api.php?action=wbsgetvaluesuggestions&format=json&item=5&property=21&threshold=0.025'
			=> 'Get suggestions for property 21 for item 5',
		);
	}

}
