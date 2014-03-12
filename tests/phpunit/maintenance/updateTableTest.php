<?php

use DatabaseBase;
use MediaWikiTestCase;

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
 * @group API
 * @group Database
 *
 * @group medium
 *
 */
class UpdateTableTest extends MediaWikiTestCase {

	protected $testfilepath;

	private function row( $pid1, $pid2, $count, $probability ) {
		return array( 'pid1' => $pid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability );
	}

	public function setUp() {
		parent::setUp();

		$testfilepath = "../../../maintenance/_temp_test_csv_file.csv";
		$fhandle = fopen($testfilepath, "w");

		$rows = array();
		$rows[] = $this->row( 1, 2, 100, 0.1 );
		$rows[] = $this->row( 1, 3, 50, 0.05 );
		$rows[] = $this->row( 2, 3, 100, 0.1 );
		$rows[] = $this->row( 2, 4, 200, 0.2 );
		$rows[] = $this->row( 3, 1, 100, 0.5 );

		foreach ($rows as $row) {
			fputcsv($fhandle, $row, ";");
		}
	}

	public function testRewriteWithDBSSpecificMethod() {

	}

	public function testRewriteWithInserts() {

	}

	public function tearDown() {
		parent::tearDown();
	}
}

