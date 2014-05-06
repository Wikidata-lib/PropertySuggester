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
	 */
	function __construct( EntityId $entityId, $probability ) {
		$this->entityId = $entityId;
		$this->probability = $probability;
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
}
