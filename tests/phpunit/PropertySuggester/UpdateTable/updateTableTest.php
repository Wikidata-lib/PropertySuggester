<?php

namespace PropertySuggester\UpdateTable;

use MediaWikiTestCase;
use PropertySuggester\Maintenance\UpdateTable;

/**
 * @covers PropertySuggester\maintenance\UpdateTable
 * @covers PropertySuggester\UpdateTable\Importer\BasicImporter
 * @covers PropertySuggester\UpdateTable\Importer\MySQLImporter
 * @covers PropertySuggester\UpdateTable\ImportContext
 * @group PropertySuggester
 * @group Database
 * @group medium
 */
class UpdateTableTest extends MediaWikiTestCase {

	/**
	 * @var string
	 */
	protected $testfilename;

	/**
	 * @var array
	 */
	protected $rows;

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$this->testfilename = sys_get_temp_dir() . "/_temp_test_csv_file.csv";


		$this->rows = array();
		$this->rows[] = array( 1, null, 2, 100, 0.1, 'item' );
		$this->rows[] = array( 1, null, 3, 50, 0.05, 'item' );
		$this->rows[] = array( 2, null, 3, 100, 0.1, 'item' );
		$this->rows[] = array( 2, null, 4, 200, 0.2, 'item' );
		$this->rows[] = array( 3, null, 1, 123, 0.5, 'item' );

		$fhandle = fopen( $this->testfilename, "w" );
		foreach ( $this->rows as $row ) {
			fputcsv( $fhandle, $row, "," );
		}
		fclose( $fhandle );
	}

	public function testRewriteNativeStrategy() {
		$maintenanceScript = new UpdateTable();
		$maintenanceScript->loadParamsAndArgs( null, array( "file" => $this->testfilename, "silent" => true ), null );
		$this->runScriptAndAssert( $maintenanceScript );
	}

	public function testRewriteWithSQLInserts() {
		$maintenanceScript = new UpdateTable();
		$maintenanceScript->loadParamsAndArgs( null, array( "file" => $this->testfilename, "silent" => true, "use-insert" => true ), null );
		$this->runScriptAndAssert( $maintenanceScript );
	}

	/**
	 * @param UpdateTable $maintenanceScript
	 */
	private function runScriptAndAssert( UpdateTable $maintenanceScript ) {
		$maintenanceScript->execute();
		$this->assertSelect(
			'wbs_propertypairs',
			array( 'pid1', 'qid1', 'pid2', 'count', 'probability', 'context' ),
			array(),
			$this->rows
		);
	}

	public function tearDown() {
		if ( file_exists( $this->testfilename ) ) {
			unlink( $this->testfilename );
		}
		parent::tearDown();
	}

}
