<?php

namespace ValueSuggester;

use ApiBase;
use ApiMain;
use DerivativeRequest;
use PropertySuggester\ResultBuilder\ValueSuggestionsResultBuilder;
use PropertySuggester\ValueResultBuilder;
use PropertySuggester\ValueSuggester\ValueSuggester;
use PropertySuggester\ValueSuggester\ValueSuggesterEngine;
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
class GetAllValueSuggestions extends ApiBase {

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

		$suggestions = $this->valueSuggester->getValueSuggestions($params["entity"], $params["property"], $params["threshold"]);

		// Build result Array
		$resultBuilder = new ValueSuggestionsResultBuilder( $suggestions );
		$entries = $resultBuilder->createJSON( $suggestions, $language, $search );

		// merge with search result if possible and necessary
		if ( count( $entries ) < $resultSize && $search !== '' ) {
			$searchResult = $this->querySearchApi( $resultSize, $search, $language );
			$entries = $resultBuilder->mergeWithTraditionalSearchResults( $entries, $searchResult, $resultSize );
		}

		// Define Result
		$slicedEntries = array_slice( $entries, $params['continue'], $params['limit'] );
		$this->getResult()->addValue( null, 'search', $slicedEntries );
		$this->getResult()->addValue( null, 'success', 1 );
		if ( count( $entries ) >= $resultSize ) {
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
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true
			),
			'property' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true
			),
			'threshold' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_DFLT => 7,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2,
				ApiBase::PARAM_MIN => 0,
				ApiBase::PARAM_RANGE_ENFORCE => true,
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
			'entity' => 'Suggest Values for given entity. (Id)',
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
			'api.php?action=wbsgetsuggestions&format=json&entity=Q4'
			=> 'Get suggestions for entity 4',
			'api.php?action=wbsgetsuggestions&format=json&entity=Q4&continue=10&limit=5'
			=> 'Get suggestions for entity 4 from rank 10 to 15',
			'api.php?action=wbsgetsuggestions&format=json&properties=P31,P21'
			=> 'Get suggestions for the property combination P21 and P31'
		);
	}

}
