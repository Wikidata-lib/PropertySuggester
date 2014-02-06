<?php

use Wikibase\DataModel\Entity\PropertyId;

/**
 * Suggestion record
 *
 * @license GPL 2+
 * @author ...
 */
class Suggestion {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var float
	 */
	private $correlation;

	/**
	 * @param PropertyId $propertyId
	 * @param float $correlation
	 *
	 * @throws InvalidArgumentException
	 */
	function __construct( PropertyId $propertyId, $correlation ) {
		if ( !is_float( $correlation ) ) {
			throw new InvalidArgumentException( '$correlation must be a float' );
		}

		$this->propertyId = $propertyId;
		$this->correlation = $correlation;
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
	public function getCorrelation() {
		return $this->correlation;
	}
}