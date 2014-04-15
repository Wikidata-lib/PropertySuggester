<?php

namespace PropertySuggester\ValueSuggester;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\ItemId;

class ValueSuggestion {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var ItemId
	 */
	private $value;

	/**
	 * @var string
	 */

	private $probability;

	/**
	 * @param PropertyId $propertyId
	 * @param float $probability
	 */
	function __construct( PropertyId $propertyId, ItemId $value, $probability ) {
		$this->propertyId = $propertyId;
		$this->value = $value;
		$this->probability = $probability;
	}

	/**
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->propertyId;
	}

	/**
	 * @return float
	 */
	public function getProbability() {
		return $this->probability;
	}

	/**
	 * @return \Wikibase\DataModel\Entity\EntityId
	 */
	public function getValue() {
		return $this->value;
	}
}
