<?php

namespace PropertySuggester\Maintenance;

use Maintenance;


$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script to load probability table from given csv file
 *
 * @ingroup Maintenance
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
		global $wgVersion, $wgTitle, $wgLang;
		$csv = null;
		if ( substr( $this->getOption( 'file' ), 0, 2 ) === "--" ) {
			$this->error( "The --file option requires a file as an argument.\n", true );
		} elseif ( $this->hasOption( 'file' ) ) {
			$csv = $this->getOption( 'file' );
		}
		$useInsert = $this->getOption( 'use-insert' );
		$showInfo = !$this->getOption( 'silent' );

		wfWaitForSlaves( 5 ); // let's not kill previos data, shall we? ;) --tor

		# Attempt to connect to the database as a privileged user
		# This will vomit up an error if there are permissions problems
		$db = wfGetDB( DB_MASTER );

		global $wgDBtype;
		$tablename = 'wbs_propertypairs';
		$dbtablename = $db->tableName( $tablename );
		if ( $db->tableExists( $tablename ) ) {
			if ( $showInfo ) $this->output( "removing old entries\n" );
			$db->delete( $tablename, '*' );
			if ( $showInfo ) $this->output( "... Done removing\n" );
		} else {
			$this->error( "$dbtablename table does not exist.\nExecuting core/maintenance/update.php may help.\n", true );
		}

		if ( $showInfo ) $this->output( "loading new entries from file\n" );
		$wholePath = str_replace( '\\', '/', __DIR__ . "/" . $csv );

		if ( $wgDBtype == 'mysql' and !$useInsert ) {
			$db->query( "
				LOAD DATA INFILE '$wholePath'
				INTO TABLE $dbtablename
				FIELDS
					TERMINATED BY ';'
				LINES
					TERMINATED BY '\\n'
			" );
		} elseif ( $wgDBtype == 'postgres' and !$useInsert ) {
			$db->query( "
				COPY $dbtablename
				FROM '$wholePath'
				WITH
					DELIMITER ';'
			" );
		} else {
			$fhandle = fopen( $wholePath, "r" );
			if ( $fhandle !== false ) {
				$accuSize = 0;
				$accu = Array();
				$data = fgetcsv( $fhandle, 0, ';' );
				while ( $data !== false ) {
					$accu[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );
					$accuSize++;

					$data = fgetcsv( $fhandle, 0, ';' );

					if ( $data === false or $accuSize > 1000 ) {
						$db->insert( $tablename, $accu );
						$accu = Array();
						$accuSize = 0;
					}
				}
			}
			fclose( $fhandle );
		}
		if ( $showInfo ) $this->output( "... Done loading\n" );
	}
}

$maintClass = 'PropertySuggester\Maintenance\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
