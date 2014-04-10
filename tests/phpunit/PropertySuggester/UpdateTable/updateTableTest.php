<?php

namespace PropertySuggester\UpdateTable;

use MediaWikiTestCase;
use PropertySuggester\Maintenance\UpdateTable;

/**
 *
 * @covers PropertySuggester\maintenance\updateTableTest
 *
 * @group PropertySuggester
 *
 * @group Database
 *
 * @group medium
 *
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

	private function row( $pid1, $pid2, $count, $probability ) {
		return array( 'pid1' => $pid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability );
	}

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$this->testfilename = sys_get_temp_dir() . "/_temp_test_csv_file.csv";

		$fhandle = fopen( $this->testfilename, "w" );

		$rows = array();
		$rows[] = $this->row( 1, 2, 100, 0.1 );
		$rows[] = $this->row( 1, 3, 50, 0.05 );
		$rows[] = $this->row( 2, 3, 100, 0.1 );
		$rows[] = $this->row( 2, 4, 200, 0.2 );
		$rows[] = $this->row( 3, 1, 123, 0.5 );

		$this->rows = array();

		foreach ( $rows as $row ) {
			fputcsv( $fhandle, $row, ";" );
			$this->rows[] = array_values( $row );
		}
		fclose( $fhandle );
	}

	public function testRewriteNativeStrategy() {
		$maintenanceScript = new UpdateTable();
		$maintenanceScript->loadParamsAndArgs( null, array( "file" => $this->testfilename, "silent" => 1 ), null );
		$this->runScriptAndAssert( $maintenanceScript );
	}

	public function testRewriteWithSQLInserts() {
		$maintenanceScript = new UpdateTable();
		$maintenanceScript->loadParamsAndArgs( null, array( "file" => $this->testfilename, "silent" => 1, "use-insert" => 1 ), null );
		$this->runScriptAndAssert( $maintenanceScript );
	}

	/**
	 * @param UpdateTable $maintenanceScript
	 */
	private function runScriptAndAssert( UpdateTable $maintenanceScript ) {
		$maintenanceScript->execute();
		$this->assertSelect(
			'wbs_propertypairs',
			array( 'pid1', 'pid2', 'count', 'probability' ),
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

