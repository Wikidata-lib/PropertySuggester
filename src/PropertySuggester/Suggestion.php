<?php

namespace PropertySuggester;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\PropertyId;

class Suggestion {

	/**
	 * @var EntityId
	 */
	private $entityId;

	/**
	 * @var float
	 */
	private $probability;

	/**
	 * @param EntityId $propertyId
	 * @param float $probability
	 */
	function __construct( EntityId $entityId, $probability, $type = "property" ) {
		$this->entityId = $entityId;
		$this->probability = $probability;
		$this->type = $type;
	}

	/**
	 * @return EntityId
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 * @return float
	 */
	public function getProbability() {
		return $this->probability;
	}

	/**
	 * returns the type of suggestion
	 * @return string
	 */
	public function getType() {
		return "property";
	}

}
