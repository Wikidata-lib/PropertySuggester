<?php

use PropertySuggester\Maintenance\UpdateTable;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;

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

	protected $testfilename;

	private function row( $pid1, $pid2, $count, $probability ) {
		return array( 'pid1' => $pid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability );
	}

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$this->testfilename = "_temp_test_csv_file.csv";

		$fhandle = fopen(str_replace("\\","/",__DIR__) . "/../../../maintenance/$this->testfilename", "w");

		$rows = array();
		$rows[] = $this->row( 1, 2, 100, 0.1 );
		$rows[] = $this->row( 1, 3, 50, 0.05 );
		$rows[] = $this->row( 2, 3, 100, 0.1 );
		$rows[] = $this->row( 2, 4, 200, 0.2 );
		$rows[] = $this->row( 3, 1, 123, 0.5 );

		foreach ($rows as $row) {
			fputcsv($fhandle, $row, ";");
		}
	}

	public function testRewriteWithDBSSpecificMethod() {
		$maint = new UpdateTable();
		$maint->loadParamsAndArgs(null, Array("file"=> $this->testfilename, "silent"=>1), null);
		$maint->execute();
		$this->assertEquals(5, $this->db->select('wbs_propertypairs','COUNT(*)')->fetchRow()[0]);
		$res = $this->db->select(
			'wbs_propertypairs',
			'*',
			'1=1',
			__METHOD__,
			array(
				'ORDER BY' => 'pid1 DESC',
				'LIMIT' => 1
			));
		$this->assertEquals(123, $res->fetchRow()["count"]);
	}

	public function testRewriteWithInserts() {
		$maint = new UpdateTable();
		$maint->loadParamsAndArgs(null, Array("file"=> $this->testfilename, "use-insert"=>1, "silent"=>1), null);
		$maint->execute();
		$this->assertEquals(5, $this->db->select('wbs_propertypairs','COUNT(*)')->fetchRow()[0]);
		$res = $this->db->select(
			'wbs_propertypairs',
			'*',
			'1=1',
			__METHOD__,
			array(
				'ORDER BY' => 'pid1 DESC',
				'LIMIT' => 1
			));
		$this->assertEquals(123, $res->fetchRow()["count"]);
	}

	public function tearDown() {
		if(file_exists($this->testfilename))
			unlink($this->testfilename);
		parent::tearDown();
	}
}

