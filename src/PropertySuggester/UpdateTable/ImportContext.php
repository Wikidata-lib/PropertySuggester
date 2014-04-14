<?php

namespace PropertySuggester\UpdateTable;

use DatabaseBase;

class ImportContext {
	/**
	 * Path to CSV file
	 * @var string
	 */
	private $wholePath = "";

	/**
	 * table name
	 * @var string
	 */
	private $tableName = "";

	/**
	 * @var DatabaseBase
	 */
	private $db = "";


	/**
	 * @param DatabaseBase $db
	 */
	public function setDb( $db ) {
		$this->db = $db;
	}

	/**
	 * @return DatabaseBase
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @param string $tablename
	 */
	public function setTableName( $tablename ) {
		$this->tableName = $tablename;
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @param string $wholePath
	 */
	public function setWholePath( $wholePath ) {
		$this->wholePath = $wholePath;
	}

	/**
	 * @return string
	 */
	public function getWholePath() {
		return $this->wholePath;
	}
}
