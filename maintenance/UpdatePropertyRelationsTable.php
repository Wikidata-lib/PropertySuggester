<?php

namespace PropertySuggester\Maintenance;

use Maintenance;
use PropertySuggester\UpdateTable\Importer\BasicImporter;
use PropertySuggester\UpdateTable\Importer\MySQLImporter;
use PropertySuggester\UpdateTable\Importer\PostgresImporter;
use PropertySuggester\UpdateTable\ImportContext;


$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script to load property pair occurrence probability table from given csv file
 *
 * @licence GNU GPL v2+
 * @author BP2013N2
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
		$wholePath = realpath( $this->getOption( 'file' ) );
		$wholePath = str_replace( '\\', '/', $wholePath );

		if ( !file_exists( $wholePath ) ) {
			$this->error( "Cant find $wholePath \n", true );
		}

		$useInsert = $this->getOption( 'use-insert' );
		$showInfo = !$this->getOption( 'silent' );
		$tableName = 'wbs_propertypairs';

		wfWaitForSlaves( 5 );
		$db = wfGetDB( DB_MASTER );
		$this->clearTable( $db, $tableName, $showInfo );

		if ( $showInfo ) {
			$this->output( "loading new entries from file\n" );
		}

		$importContext = $this->createImportContext( $db, $tableName, $wholePath );
		$insertionStrategy = $this->createImportStrategy( $useInsert );
		$success = $insertionStrategy->importFromCsvFileToDb( $importContext );

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
		global $wgDbType;
		if ( $wgDbType == 'mysql' and !$useInsert ) {
			return new MySQLImporter();
		} elseif ( $wgDbType == 'postgres' and !$useInsert ) {
			return new PostgresImporter();
		} else {
			return new BasicImporter();
		}
	}

	/**
	 * @param \DatabaseBase $db
	 * @param string $tableName
	 * @param string $wholePath
	 * @return ImportContext
	 */
	function createImportContext( \DatabaseBase $db, $tableName, $wholePath ) {
		$importContext = new ImportContext();
		$importContext->setDb( $db );
		$importContext->setTableName( $tableName );
		$importContext->setWholePath( $wholePath );
		return $importContext;
	}


	/**
	 * @param \DatabaseBase $db
	 * @param string $tableName
	 * @param boolean $showInfo
	 */
	private function clearTable( \DatabaseBase $db, $tableName, $showInfo ) {
		if ( $db->tableExists( $tableName ) ) {
			if ( $showInfo ) {
				$this->output( "removing old entries\n" );
			}
			$db->delete( $tableName, '*' );
		} else {
			$this->error( "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}
	}

}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
