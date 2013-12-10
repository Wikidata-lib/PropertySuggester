<?php

class Suggestion {

	private $propertyId;
	private $correlation;
	private $suggestedValue;
        
        function __construct($propertyId, $correlation, $suggestedValue = null){
            $this->propertyId = $propertyId;
            $this->correlation = $correlation;
            $this->suggestedValue = $suggestedValue;
	}
	
	public function getPropertyId() {
		return $this->propertyId;
	}

	public function getCorrelation() {
		return $this->correlation;
	}

	public function getSuggestedValue() {
		return $this->suggestedValue;
	}

	public function setPropertyId($propertyId) {
		$this->propertyId = $propertyId;
	}

	public function setCorrelation($correlation) {
		$this->correlation = $correlation;
	}

	public function setSuggestedValue($suggestedValue) {
		$this->suggestedValue = $suggestedValue;
	}	
}