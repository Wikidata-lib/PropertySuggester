<?php

namespace PropertySuggester\Suggesters;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * Suggestion returned by a SuggesterEngine
 *
 * @licence GNU GPL v2+
 */
class Suggestion {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var float
	 * average probability that an already existing property is used with the suggested property
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
	 * average probability that an already existing property is used with the suggested property
	 * @return float
	 */
	public function getProbability() {
		return $this->probability;
	}

}
