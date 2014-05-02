<?php

namespace PropertySuggester;

use ApiBase;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\StoreFactory;

/**
 * Abstract apiCall class that handles tasks, which need to be performed in GetItemSuggestions AND GetPropertySuggestions
 *
 * @licence GNU GPL v2+
 */
abstract class GetSuggestionsApiBase extends ApiBase {

	/**
	 * @param $numericItemId
	 * @return null|\Wikibase\Item
	 * @throws InvalidArgumentException
	 */
	protected function getItemFromNumericId($numericItemId)
	{
		$entityLookup = StoreFactory::getStore( 'sqlstore' )->getEntityLookup();
		$id = new ItemId( $numericItemId );
		$item = $entityLookup->getEntity( $id );
		if( $item == null ){
			throw new InvalidArgumentException( 'Item ' . $id . ' could not be found' );
		}
		return $item;
	}

	/**
	 * @param SearchResultWithSuggestions $searchResult
	 * @param $resultSize
	 * @param $search
	 */
	protected function buildResult( SearchResultWithSuggestions &$searchResult, $resultSize, $search ) {
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


	protected function setIndexedTags( &$searchResultDictionary ) {
		$this->getResult()->setIndexedTagName( $searchResultDictionary, 'search' );
		foreach ( $searchResultDictionary as $entry ) {
			$this->getResult()->setIndexedTagName( $entry['aliases'], 'alias' );
		}
	}
} 