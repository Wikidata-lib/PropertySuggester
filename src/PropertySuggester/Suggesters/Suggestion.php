<?php

namespace PropertySuggester\Suggesters;

use Wikibase\DataModel\Entity\PropertyId;

class Suggestion {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var float
	 */
	private $probability;

	/**
	 * @param PropertyId $propertyId
	 * @param float $probability
	 */
	function __construct( PropertyId $propertyId, $probability ) {
		$this->propertyId = $propertyId;
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

}
