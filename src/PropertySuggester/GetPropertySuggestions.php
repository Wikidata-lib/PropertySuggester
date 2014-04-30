<?php

namespace PropertySuggester;

use ApiBase;
use PropertySuggester\Suggester\PropertySuggester;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\StoreFactory;
use Wikibase\Utils;
use InvalidArgumentException;

/**
 * API module to get property suggestions.
 *
 * @licence GNU GPL v2+
 */
class GetPropertySuggestions extends ApiBase {

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		global $wgPropertySuggesterDeprecatedIds;
		global $wgPropertySuggesterMinProbability;

		$paramsParser = new ParamsParser( 500, $wgPropertySuggesterMinProbability );
		$params = $paramsParser->parseAndValidate( $this->extractRequestParams() );

		$suggester = new PropertySuggester( wfGetLB( DB_SLAVE ), $params->entity, $params->minProbability );
		$suggester->setDeprecatedPropertyIds( $wgPropertySuggesterDeprecatedIds );
		$suggester->setItem($this->getItemFromNumericId($params->entity));

		$searchResult = new SearchResultsWithSuggestions( $suggester, $params, "property" );

		$this->buildResult( $searchResult, $params->internalResultListSize, $params->search );


		//old method code ->
		/*

		$suggestionGenerator = new SuggestionGenerator( $this->entityLookup, $this->termIndex, $this->suggester );

		if ( $params->entity !== null ) {
			$suggestions = $suggestionGenerator->generateSuggestionsByItem( $params->entity, $params->suggesterLimit, $params->minProbability );
		} else {
			$suggestions = $suggestionGenerator->generateSuggestionsByPropertyList( $params->properties, $params->suggesterLimit, $params->minProbability );
		}
		$suggestions = $suggestionGenerator->filterSuggestions( $suggestions, $params->search, $params->language, $params->resultSize );

		// Build result Array
		$resultBuilder = new ResultBuilder( $this->getResult(), $params->search );
		$entries = $resultBuilder->createJSON( $suggestions, $params->language, $params->search );

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
		};
		$this->getResult()->addValue( 'searchinfo', 'search', $params->search )

		*/
	}

	/**
	 * @param $numericItemId
	 * @return null|\Wikibase\Item
	 * @throws InvalidArgumentException
	 */
	private function getItemFromNumericId($numericItemId)
	{
		$entityLookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new ItemId( $numericItemId );
		$item = $entityLookup->getEntity( $id );
		if( $item == null ){
			throw new InvalidArgumentException( 'Item ' . $id . ' could not be found' );
		}
		return $item;
	}

	private function buildResult( SearchResultsWithSuggestions &$searchResult, $resultSize, $search ) {
		$searchResultDictionary = $searchResult->getResultDictionary();

		$this->setIndexedTags( $searchResultDictionary );
		$apiResult = $this->getResult();
		$apiResult->addValue( null, 'search', $searchResultDictionary );
		if ( $searchResult->resultSize() >= $resultSize ) {
			$apiResult->getResult()->addValue( null, 'search-continue', $resultSize );
		}
		$apiResult->addValue( null, 'success', 1 );
		$apiResult->addValue( 'searchinfo', 'search', $search );
	}


	private function setIndexedTags( &$searchResultDictionary ) {
		$this->getResult()->setIndexedTagName( $searchResultDictionary, 'search' );
		foreach ( $searchResultDictionary as $entry ) {
			$this->getResult()->setIndexedTagName( $entry['aliases'], 'alias' );
		}
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
