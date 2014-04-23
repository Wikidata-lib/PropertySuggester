<?php

namespace PropertySuggester\UpdateTable;

use LoadBalancer;

class ImportContext {
	/**
	 * Path to CSV file
	 * @var string
	 */
	private $csvFilePath = "";

	/**
	 * table name
	 * @var string
	 */
	private $targetTableName = "";

	/**
	 * @var LoadBalancer
	 */
	private $lb = "";


	/**
	 * @param LoadBalancer $lb
	 */
	public function setLb( $lb ) {
		$this->lb = $lb;
	}

	/**
	 * @return LoadBalancer
	 */
	public function getLb() {
		return $this->lb;
	}

	/**
	 * @param string $tablename
	 */
	public function setTargetTableName( $tablename ) {
		$this->targetTableName = $tablename;
	}

	/**
	 * @return string
	 */
	public function getTargetTableName() {
		return $this->targetTableName;
	}

	/**
	 * @param string $wholePath
	 */
	public function setCsvFilePath( $wholePath ) {
		$this->csvFilePath = $wholePath;
	}

	/**
	 * @return string
	 */
	public function getCsvFilePath() {
		return $this->csvFilePath;
	}
}
