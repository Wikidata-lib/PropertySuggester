<?php
namespace PropertySuggester;

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
    }


	function execute() {
		global $wgVersion, $wgTitle, $wgLang;
        $csv = null;
        if ( substr( $this->getOption( 'file' ), 0, 2 ) === "--" ) {
            $this->error( "The --file option requires a file as an argument.\n", true );
        } elseif ($this->hasOption( 'file' )) {
            $csv = $this->getOption( 'file' );
        }

		wfWaitForSlaves( 5 ); // let's not kill previos data, shall we? ;) --tor

		# Attempt to connect to the database as a privileged user
		# This will vomit up an error if there are permissions problems
		$db = wfGetDB( DB_MASTER );

		$this->output( "Updating attribute pair occurrence table for " . wfWikiID() . "\n" );

        $this->output( "\t Step1: removing old entries" );
        $db->delete( "wbs_propertypairs", "*" );
        $this->output( " ...Step1 done\n" );

        $this->output( "\t Step2: loading new entries from file" );
        $wholePath = str_replace( '\\', '/', __DIR__ . "/" . $csv);

        $db->query("
            LOAD DATA INFILE '$wholePath'
            REPLACE
            INTO TABLE wbs_propertypairs
            FIELDS
                TERMINATED BY ';'
            LINES
                TERMINATED BY '\\n'
        ");
        $this->output( " ...Step2 done\n" );


		$this->output( "Done \n" );
	}
}

$maintClass = 'PropertySuggester\UpdateTable';
require_once RUN_MAINTENANCE_IF_MAIN;
