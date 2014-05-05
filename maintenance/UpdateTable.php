<?php

namespace PropertySuggester\Maintenance;

use Maintenance;
use LoadBalancer;
use PropertySuggester\UpdateTable\Importer\BasicImporter;
use PropertySuggester\UpdateTable\Importer\Importer;
use PropertySuggester\UpdateTable\Importer\MySQLImporter;
use PropertySuggester\UpdateTable\ImportContext;
use UnexpectedValueException;


$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script to load property pair occurrence probability table from given csv file
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class UpdateTable extends Maintenance {

	function __construct() {
		parent::__construct();
		$this->mDescription = "Read CSV Dump and refill probability table";
		$this->addOption( 'file', 'CSV table to be loaded (relative path)', true, true );
		$this->addOption( 'use-insert', 'Avoid DBS specific import. Use INSERTs.', false, false );
		$this->addOption( 'silent', 'Do not show information', false, false );
	}

	/**
	 * loads property pair occurrence probability table from given csv file
	 */
	function execute() {
		if ( substr( $this->getOption( 'file' ), 0, 2 ) === "--" ) {
			$this->error( "The --file option requires a file as an argument.\n", true );
		}
		$path = $this->getOption( 'file' );
		$fullPath = realpath( $path );
		$fullPath = str_replace( '\\', '/', $fullPath );

		if ( !file_exists( $fullPath ) ) {
			$this->error( "Cant find $path \n", true );
		}

		$useInsert = $this->getOption( 'use-insert' );
		$showInfo = !$this->getOption( 'silent' );
		$tableName = 'wbs_propertypairs';

		wfWaitForSlaves( 5 );
		$lb = wfGetLB( DB_MASTER );

		$this->clearTable( $lb, $tableName, $showInfo );

		if ( $showInfo ) {
			$this->output( "loading new entries from file\n" );
		}

		$importContext = $this->createImportContext( $lb, $tableName, $fullPath );
		$insertionStrategy = $this->createImportStrategy( $useInsert );

		try {
			$success = $insertionStrategy->importFromCsvFileToDb( $importContext );
		} catch (UnexpectedValueException $e) {
			$this->error( "Import failed: " . $e->getMessage() );
			exit;
		}

		if ( !$success ) {
			$this->error( "Failed to run import to db" );
		}
		if ( $showInfo ) {
			$this->output( "... Done loading\n" );
		}
	}

	/**
	 * @param boolean $useInsert
	 * @return Importer
	 */
	function createImportStrategy( $useInsert ) {
		global $wgDBtype;
		if ( $wgDBtype === 'mysql' and !$useInsert ) {
			return new MySQLImporter();
		} else {
			return new BasicImporter();
		}
	}

	/**
	 * @param LoadBalancer $lb
	 * @param string $tableName
	 * @param string $wholePath
	 * @return ImportContext
	 */
	function createImportContext( LoadBalancer $lb, $tableName, $wholePath ) {
		$importContext = new ImportContext();
		$importContext->setLb( $lb );
		$importContext->setTargetTableName( $tableName );
		$importContext->setCsvFilePath( $wholePath );
		$importContext->setCsvDelimiter( ',' );
		return $importContext;
	}

	/**
	 * @param LoadBalancer $lb
	 * @param string $tableName
	 * @param boolean $showInfo
	 */
	private function clearTable( LoadBalancer $lb, $tableName, $showInfo ) {
		$db = $lb->getConnection( DB_MASTER );
		if ( $db->tableExists( $tableName ) ) {
			if ( $showInfo ) {
				$this->output( "removing old entries\n" );
			}
			$db->delete( $tableName, '*' );
		} else {
			$this->error( "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}
		$lb->reuseConnection( $db );
	}

}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
