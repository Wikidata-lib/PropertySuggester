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
		$this->addOption( 'use-loaddata', 'Use DBS specific fast import. Use INSERTs.', false, false );
		$this->setBatchSize( 1000 );
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

		$useLoadData = $this->getOption( 'use-loaddata' );
		$tableName = 'wbs_propertypairs';

		wfWaitForSlaves();
		$lb = wfGetLB();

		$this->clearTable( $lb, $tableName );

		$this->output( "loading new entries from file\n" );

		$importContext = $this->createImportContext( $lb, $tableName, $fullPath, $this->isQuiet() );
		$importStrategy = $this->createImportStrategy( $useLoadData );

		try {
			$success = $importStrategy->importFromCsvFileToDb( $importContext );
		} catch ( UnexpectedValueException $e ) {
			$this->error( "Import failed: " . $e->getMessage() );
			exit;
		}

		if ( !$success ) {
			$this->error( "Failed to run import to db" );
		}
		$this->output( "... Done loading\n" );
	}

	/**
	 * @param boolean $useLoadData
	 * @return Importer
	 */
	function createImportStrategy( $useLoadData ) {
		global $wgDBtype;
		if ( $wgDBtype === 'mysql' and $useLoadData ) {
			return new MySQLImporter();
		} else {
			return new BasicImporter();
		}
	}

	/**
	 * @param LoadBalancer $lb
	 * @param string $tableName
	 * @param string $wholePath
	 * @param bool $quiet
	 * @return ImportContext
	 */
	function createImportContext( LoadBalancer $lb, $tableName, $wholePath, $quiet ) {
		$importContext = new ImportContext();
		$importContext->setLb( $lb );
		$importContext->setTargetTableName( $tableName );
		$importContext->setCsvFilePath( $wholePath );
		$importContext->setCsvDelimiter( ',' );
		$importContext->setBatchSize( $this->mBatchSize );
		$importContext->setQuiet( $quiet );

		return $importContext;
	}

	/**
	 * @param LoadBalancer $lb
	 * @param string $tableName
	 */
	private function clearTable( LoadBalancer $lb, $tableName ) {
		global $wgDBtype;
		$db = $lb->getConnection( DB_MASTER );
		if ( !$db->tableExists( $tableName ) ) {
			$this->error( "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}
		$this->output( "Removing old entries\n" );
		if ( $wgDBtype === 'sqlite' ) {
			$db->delete( $tableName, "*" );
		} else {
			while ( 1 ) {
				$db->commit( __METHOD__, 'flush' );
				wfWaitForSlaves();
				$this->output( "Deleting a batch\n" );
				$table = $db->tableName( $tableName );
				$db->query( "DELETE FROM $table LIMIT $this->mBatchSize" );
				if ( $db->affectedRows() == 0 ) {
					break;
				}
			}
		}
		$lb->reuseConnection( $db );
	}

}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
