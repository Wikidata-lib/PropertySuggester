<?php

namespace PropertySuggester;

use Wikibase\DataModel\Entity\EntityId;

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
	 * @param EntityId $entityId
	 * @param $probability
	 * @param string $type
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
