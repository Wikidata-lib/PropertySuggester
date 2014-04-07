<?php
/**
 * Created by PhpStorm.
 * User: felix.niemeyer
 * Date: 4/7/14
 * Time: 11:41 AM
 */

namespace PropertySuggester\UpdateTable;


class InserterContext {
	/**
	 * @var string
	 * Path to CSV file
	 */
	private $wholePath = "";
	/**
	 * @var string
	 * relative table name
	 */
	private $tableName = "";
	/**
	 * @var string
	 * absolute table name (with prefix)
	 */
	private $dbTableName = "";
	/**
	 * @var DatabaseBase
	 */
	private $db = "";

	/**
	 * @var bool
	 * if true, show info while processing
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
	 * @param string $dbTableName
	 */
	public function setDbTableName( $dbtablename ) {
		$this->dbTableName = $dbtablename;
	}

	/**
	 * @return string
	 */
	public function getDbTableName() {
		return $this->dbTableName;
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