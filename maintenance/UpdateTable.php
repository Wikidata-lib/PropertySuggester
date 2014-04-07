<?php

namespace PropertySuggester\Maintenance;

use Maintenance;
use PropertySuggester\UpdateTable\Inserter\InsertInserter;
use PropertySuggester\UpdateTable\Inserter\MySQLInserter;
use PropertySuggester\UpdateTable\InserterContext;


$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script to load property pair occurrence probability table from given csv file
 *
 * @licence GNU GPL v2+
 * @author 2014BPN2
 */
class UpdateTable extends Maintenance {
	function __construct() {
		parent::__construct();
		$this->mDescription = "Read CSV Dump and refill probability table";
		$this->addOption( 'file', 'CSV table to be loaded (relative path)', true, true );
		$this->addOption( 'use-insert', 'Avoid DBS specific import. Use INSERTs.', false, false );
		$this->addOption( 'silent', 'Do not show information', false, false );
	}

	function execute() {
		$csv = null;

		if ( $this->hasOption( 'file' ) )
		{
			if ( substr( $this->getOption( 'file' ), 0, 2 ) === "--" ) {
				$this->error( "The --file option requires a file as an argument.\n", true );
			}
			$csv = $this->getOption( 'file' );
		}

		$useInsert = $this->getOption( 'use-insert' );
		$showInfo = !$this->getOption( 'silent' );

		wfWaitForSlaves( 5 ); // let's not kill previos data, shall we? ;) --tor

		# Attempt to connect to the database as a privileged user
		# This will vomit up an error if there are permissions problems
		$db = wfGetDB( DB_MASTER );

		global $wgDbType;
		$tableName = 'wbs_propertypairs';
		$dbTableName = $db->tableName( $tableName );

		$this->clearTable($db, $tableName, $dbTableName, $showInfo);

		if ( $showInfo ) {
            $this->output( "loading new entries from file\n" );
        }

		$wholePath = realpath( $csv );
		$wholePath = str_replace( '\\', '/', $wholePath );

		if ( $wgDbType == 'mysql' and !$useInsert ) {
			$insertionStrategy = new MySQLInserter();
		} elseif ( $wgDbType == 'postgres' and !$useInsert ) {
			$insertionStrategy = new PostgresInserter();
		} else {
			$insertionStrategy = new InsertInserter();
		}

		$insertionContext = new InserterContext();
		$insertionContext->setDb($db);
		$insertionContext->setDbTableName($dbTableName);
		$insertionContext->setTableName($tableName);
		$insertionContext->setShowInfo($showInfo);
		$insertionContext->setWholePath($wholePath);
		$insertionStrategy->setContext($insertionContext);
		$insertionStrategy->execute();

		if ( $showInfo ) {
            $this->output( "... Done loading\n" );
        }
	}

	private function clearTable($db, $tableName, $dbTableName, $showInfo) {
		if ( $db->tableExists( $tableName ) ) {
		if ( $showInfo ) {
		$this->output( "removing old entries\n" );
		}
		$db->delete( $tableName, '*' );
		if ( $showInfo ) {
			$this->output( "... Done removing\n" );
		}
		} else {
			$this->error( "$dbTableName table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}
	}
}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
