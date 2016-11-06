<?php

namespace PropertySuggester\Suggesters;

use InvalidArgumentException;
use LogicException;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikimedia\Rdbms\LoadBalancer;
use Wikimedia\Rdbms\ResultWrapper;

/**
 * a Suggester implementation that creates suggestion via MySQL
 * Needs the wbs_propertypairs table filled with pair probabilities.
 *
 * @licence GNU GPL v2+
 */
class SimpleSuggester implements SuggesterEngine {

	/**
	 * @var int[]
	 */
	private $deprecatedPropertyIds = array();

	/**
	 * @var array Numeric property ids as keys, values are meaningless.
	 */
	private $classifyingPropertyIds = array();

	/**
	 * @var Suggestion[]
	 */
	private $initialSuggestions = array();

	/**
	 * @var float
	 */
	private $classifyingConditionWeight = 0.5;

	/**
	 * @var LoadBalancer
	 */
	private $lb;

	/**
	 * @param LoadBalancer $lb
	 */
	public function __construct( LoadBalancer $lb ) {
		$this->lb = $lb;
	}

	/**
	 * @param int[] $deprecatedPropertyIds
	 */
	public function setDeprecatedPropertyIds( array $deprecatedPropertyIds ) {
		$this->deprecatedPropertyIds = $deprecatedPropertyIds;
	}

	/**
	 * @param int[] $classifyingPropertyIds
	 */
	public function setClassifyingPropertyIds( array $classifyingPropertyIds ) {
		$this->classifyingPropertyIds = array_flip( $classifyingPropertyIds );
	}

	/**
	 * @param int[] $initialSuggestionIds
	 */
	public function setInitialSuggestions( array $initialSuggestionIds ) {
		$suggestions = array();
		foreach ( $initialSuggestionIds as $id ) {
			$suggestions[] = new Suggestion( PropertyId::newFromNumber( $id ), 1.0 );
		}

		$this->initialSuggestions = $suggestions;
	}

	/**
	 * @param float $classifyingConditionWeight
	 */
	public function setClassifyingConditionWeight( $classifyingConditionWeight ) {
		$this->classifyingConditionWeight = $classifyingConditionWeight;
	}

	/**
	 * @param array[] $idTuples Array of ( int property ID, int item ID ) tuples
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @throws InvalidArgumentException
	 * @return Suggestion[]
	 */
	private function getSuggestions( array $idTuples, $limit, $minProbability, $context ) {
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit must be int!' );
		}
		if ( !is_float( $minProbability ) ) {
			throw new InvalidArgumentException( '$minProbability must be float!' );
		}

		if ( !$idTuples ) {
			return $this->initialSuggestions;
		}

		$propertyIds = array_map( function( array $tuple ) {
			return $tuple[0];
		}, $idTuples );
		$excludedIds = array_merge( $propertyIds, $this->deprecatedPropertyIds );
		$count = count( $propertyIds );

		$dbr = $this->lb->getConnection( DB_REPLICA );

		$tupleConditions = [];
		$hasClassifyingPropertyIds = false;

		foreach ( $idTuples as $tuple ) {
			$tupleConditions[] = $this->buildTupleCondition( $tuple[0], $tuple[1] );
			$hasClassifyingPropertyIds = $hasClassifyingPropertyIds || $tuple[1] > 0;
		}

		if ( $hasClassifyingPropertyIds && abs( $this->classifyingConditionWeight - 0.5 ) > 1e-8 ) {
			// Use a weighted SELECT, if we have at least one classifying relation
			// and classifyingConditionWeight is not 0.5.
			$propSelect = $this->getWeightedPropSelect( $dbr->getType(), $count );
		} else {
			$propSelect = "sum(probability)/$count";
		}

		$condition = $dbr->makeList( $tupleConditions, LIST_OR );

		$res = $dbr->select(
			'wbs_propertypairs',
			array( 'pid' => 'pid2', 'prob' => $propSelect ),
			array( $condition,
				   'pid2 NOT IN (' . $dbr->makeList( $excludedIds ) . ')',
				   'context' => $context ),
			__METHOD__,
			array(
				'GROUP BY' => 'pid2',
				'ORDER BY' => 'prob DESC',
				'LIMIT'    => $limit,
				'HAVING'   => 'prob > ' . floatval( $minProbability )
			)
		);

		$this->lb->reuseConnection( $dbr );

		return $this->buildResult( $res );
	}

	/**
	 * @param string $dbType
	 * @param int $count
	 * @return string
	 */
	private function getWeightedPropSelect( $dbType, $count ) {
		// The sum of the weights needs to be 2.
		$weightNonClassifying = floatval( ( 1 - $this->classifyingConditionWeight ) * 2 );
		$weightClassifying = floatval( $this->classifyingConditionWeight * 2 );

		if ( $dbType === 'mysql' ) {
			$propSelect = 'SUM(if(qid1 = 0, probability * ' . $weightNonClassifying .
				', probability * ' . $weightClassifying . '))/' . (int)$count;
		} elseif ( $dbType === 'sqlite' ) {
			$propSelect = 'SUM(CASE WHEN qid1 = 0 THEN probability * ' . $weightNonClassifying .
				' ELSE probability * ' . $weightClassifying . ' END)/' . (int)$count;
		} else {
			// We can only be sure this works on MySQL and SQLite right now.
			$propSelect = 'SUM(probability)/' . (int)$count;
		}

		return $propSelect;
	}

	/**
	 * @see SuggesterEngine::suggestByPropertyIds
	 *
	 * @param PropertyId[] $propertyIds
	 * @param int $limit
 	 * @param float $minProbability
	 * @param string $context
	 * @return Suggestion[]
	 */
	public function suggestByPropertyIds( array $propertyIds, $limit, $minProbability, $context ) {
		$idTuples = array_map( function( PropertyId $propertyId ) {
			return [ $propertyId->getNumericId(), 0 ];
		}, $propertyIds );

		return $this->getSuggestions( $idTuples, $limit, $minProbability, $context );
	}

	/**
	 * @see SuggesterEngine::suggestByEntity
	 *
	 * @param Item $item
	 * @param int $limit
	 * @param float $minProbability
	 * @param string $context
	 * @throws LogicException
	 * @return Suggestion[]
	 */
	public function suggestByItem( Item $item, $limit, $minProbability, $context ) {
		$idTuples = [];

		foreach ( $item->getStatements()->toArray() as $statement ) {
			$mainSnak = $statement->getMainSnak();
			$numericPropertyId = $mainSnak->getPropertyId()->getNumericId();

			if ( !isset( $this->classifyingPropertyIds[$numericPropertyId] ) ) {
				$idTuples[] = [ $numericPropertyId, 0 ];
			} elseif ( $mainSnak instanceof PropertyValueSnak ) {
				$dataValue = $mainSnak->getDataValue();

				if ( !( $dataValue instanceof EntityIdValue ) ) {
					throw new LogicException(
						"Property $numericPropertyId in wgPropertySuggesterClassifyingPropertyIds"
						. ' does not have value type wikibase-entityid'
					);
				}

				$entityId = $dataValue->getEntityId();

				if ( !( $entityId instanceof ItemId ) ) {
					throw new LogicException(
						"PropertyValueSnak for $numericPropertyId, configured in " .
						' wgPropertySuggesterClassifyingPropertyIds, has an unexpected value ' .
						'and data type (not wikibase-item).'
					);
				}

				$numericEntityId = $entityId->getNumericId();
				$idTuples[] = [ $numericPropertyId, $numericEntityId ];
			}
		}

		return $this->getSuggestions( $idTuples, $limit, $minProbability, $context );
	}

	/**
	 * @param int $pid
	 * @param int $qid
	 * @return string
	 */
	private function buildTupleCondition( $pid, $qid ) {
		$tuple = '(pid1 = '. ( int )$pid .' AND qid1 = '. ( int )$qid .')';
		return $tuple;
	}

	/**
	 * Converts the rows of the SQL result to Suggestion objects
	 *
	 * @param ResultWrapper $res
	 * @return Suggestion[]
	 */
	private function buildResult( ResultWrapper $res ) {
		$resultArray = array();
		foreach ( $res as $row ) {
			$pid = PropertyId::newFromNumber( ( int )$row->pid );
			$suggestion = new Suggestion( $pid, $row->prob );
			$resultArray[] = $suggestion;
		}
		return $resultArray;
	}

}
