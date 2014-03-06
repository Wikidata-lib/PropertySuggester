<?php

namespace PropertySuggester;

use ApiBase;
use ApiMain;
use DerivativeRequest;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Utils;
use PropertySuggester\GetSuggestionsHelper;
use Wikibase\Repo\WikibaseRepo;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\StoreFactory;

/**
 * API module to get property suggestions.
 *
 * @since 0.1
 * @licence GNU GPL v2+
 */


class GetSuggestions extends ApiBase {

	public function __construct( ApiMain $main, $name, $prefix = '' ) {
		parent::__construct( $main, $name, $prefix );
	}

	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		wfProfileIn( __METHOD__ );
		$params = $this->extractRequestParams();

		// parse params
		if ( !( $params['entity'] xor $params['properties'] ) ) {
			wfProfileOut( __METHOD__ );
			$this->dieUsage( 'provide either entity id parameter \'entity\' or a list of properties \'properties\'', 'param-missing' );
		}

		if ( $params['search'] !== '*' ) {
			$search = $params['search'];
		} else {
			$search = '';
		}

		$language = $params['language'];

		$resultSize = $params['continue'] + $params['limit'];
                
                $helper = new GetSuggestionsHelper();

		$suggestions = $helper->generateSuggestions( $params["entity"], $params['properties']);
                
                // Build result Array
                $entries = $this->createJSON( $suggestions, $language ); //ToDo: Put in generate entries
                if ( $search ) {
			$entries = $helper->filterByPrefix( $entries, $search );
		}

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
        
        /**
	 * @param Suggestion[] $suggestions
	 * @param string $language
	 * @return array
	 */
	public function createJSON( $suggestions, $language ) {
		$entries = array();
		$ids = array();
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$ids[] = $id;
		}
		//See SearchEntities
		$terms = StoreFactory::getStore()->getTermIndex()->getTermsOfEntities( $ids, 'property', $language );
		foreach ( $suggestions as $suggestion ) {
			$id = $suggestion->getPropertyId();
			$entry = array();
			$entry['id'] = $id->getPrefixedId();
			$entry['url'] = $entityContentFactory->getTitleForId( $id )->getFullUrl();
			$entry['rating'] = $suggestion->getPropertyId();

			$aliases = array();
			foreach ( $terms as $term ) {
				if ( $term->getEntityId() === $id->getNumericId() ) {
					if ( $term->getType() === 'label' ) {
						$entry['label'] = $term->getText();
					}
					if ( $term->getType() === 'description' ) {
						$entry['description'] = $term->getText();
					}
					if ( $term->getType() === 'alias' ) {
						$aliases[] = $term->getText();
					}
				}
			}

			if ( count( $aliases ) > 0 ) {
				$entry['aliases'] = $aliases;
				$this->getResult()->setIndexedTagName( $entry['aliases'], 'alias' );
			}

			$entries[] = $entry;
		}
		return $entries;
	}

	/**
	 * @param array $entries
	 * @param int $resultSize
	 * @param string $search
	 * @param string $language
	 * @return array
	 */
	public function mergeWithTraditionalSearchResults( array &$entries, $resultSize, $search, $language ) {
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
			if ( !array_key_exists( $sr['id'], $existingKeys ) ) {
				$entries[] = $sr;
				$distinctCount++;
				if ( $distinctCount > $resultSize ) {
					break;
				}
			}
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
		return array();
	}
}
