<?php

namespace PropertySuggester\UpdateTable;

use DatabaseBase;

class InserterContext {
	/**
	 * Path to CSV file
	 * @var string
	 */
	private $wholePath = "";

	/**
	 * relative table name
	 * @var string
	 */
	private $tableName = "";

	/**
	 * @var DatabaseBase
	 */
	private $db = "";

	/**
	 * if true, show info while processing
	 * @var bool
	 */
	private $showInfo = true;


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
	 * @param bool $showInfo
	 */
	public function setShowInfo( $showInfo ) {
		$this->showInfo = $showInfo;
	}

	/**
	 * @return bool
	 */
	public function getShowInfo() {
		return $this->showInfo;
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