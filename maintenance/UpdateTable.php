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

		$useInsert = $this->getOption( 'use-insert' );
		$tableName = 'wbs_propertypairs';
		$primaryKey = 'row_id';

		wfWaitForSlaves();
		$lb = wfGetLB();

		$this->clearTable( $lb, $tableName, $primaryKey );

		$this->output( "loading new entries from file\n" );

		$importContext = $this->createImportContext( $lb, $tableName, $fullPath, $this->isQuiet() );
		$importStrategy = $this->createImportStrategy( $useInsert );

		try {
			$success = $importStrategy->importFromCsvFileToDb( $importContext );
		} catch (UnexpectedValueException $e) {
			$this->error( "Import failed: " . $e->getMessage() );
			exit;
		}

		if ( !$success ) {
			$this->error( "Failed to run import to db" );
		}
		$this->output( "... Done loading\n" );
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
	 * @param string $primaryKey
	 */
	private function clearTable( LoadBalancer $lb, $tableName, $primaryKey ) {
		$db = $lb->getConnection( DB_MASTER );
		if ( !$db->tableExists( $tableName ) ) {
			$this->error( "$tableName table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}
		$this->output( "removing old entries\n" );
		while ( 1 ) {
			$db->commit( __METHOD__, 'flush' );
			wfWaitForSlaves();

			$idChunk = $db->select(
				$tableName,
				array( $primaryKey ),
				array(),
				__METHOD__,
				array( 'LIMIT' => $this->mBatchSize )
			);
			if( $idChunk->numRows() == 0 ) {
				break;
			}
			$ids = array();
			foreach ( $idChunk as $row ) {
				$ids[] = ( int ) $row->$primaryKey;
			}
			$db->delete( $tableName, array( $primaryKey => $ids ) );
			$this->output( "Deleting a batch\n" );
		}
		$lb->reuseConnection( $db );
	}
}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
