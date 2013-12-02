<?php

class Suggestion {

	private $propertyId;
	private $affilation;
	private $suggestedValue;
	
	public function getPropertyId() {
		return $this->propertyId;
	}

	public function getCorrelation() {
		return $this->affilation;
	}

	public function getSuggestedValue() {
		return $this->suggestedValue;
	}

	public function setPropertyId($propertyId) {
		$this->propertyId = $propertyId;
	}

	public function setCorrelation($correlation) {
		$this->affilation = $correlation;
	}

	public function setSuggestedValue($suggestedValue) {
		$this->suggestedValue = $suggestedValue;
	}
	
	
	function Suggestion($propertyId, $correlation, $suggestedValue){
		$this->propertyId = $propertyId;
		$this->affilation = $correlation;
		$this->suggestedValue = $suggestedValue;
	}
	
}
