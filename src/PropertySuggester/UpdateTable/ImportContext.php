<?php

namespace PropertySuggester\UpdateTable;

use LoadBalancer;

/**
 * Context for importing data from a csv file to a db table using a Importer strategy
 * Class ImportContext
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class ImportContext {
	/**
	 * file system path to the CSV to load data from
	 * @var string
	 */
	private $csvFilePath = "";

	/**
	 * delimiter used in csv file
	 * @var string
	 */
	private $csvDelimiter = ",";

	/**
	 * table name of the table to import to
	 * @var string
	 */
	private $targetTableName = "";

	/**
	 * @var LoadBalancer
	 */
	private $lb = "";

	/**
	 * @param string $csvDelimiter
	 */
	public function setCsvDelimiter( $csvDelimiter ) {
		$this->csvDelimiter = $csvDelimiter;
	}

	/**
	 * @return string
	 */
	public function getCsvDelimiter() {
		return $this->csvDelimiter;
	}

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
