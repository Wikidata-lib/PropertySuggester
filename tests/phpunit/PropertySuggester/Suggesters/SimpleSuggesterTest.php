<?php

namespace PropertySuggester\Suggesters;

use LoadBalancerSingle;
use MediaWikiTestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;

/**
 * @covers PropertySuggester\Suggesters\SimplePHPSuggester
 * @group PropertySuggester
 * @group API
 * @group Database
 * @group medium
 */
class SimpleSuggesterTest extends MediaWikiTestCase {

	/**
	 * @var SuggesterEngine
	 */
	protected $suggester;

	private function row( $pid1, $pid2, $count, $probability ) {
		return array( 'pid1' => $pid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability );
	}

	public function addDBData() {
		$rows = array();
		$rows[] = $this->row( 1, 2, 100, 0.1 );
		$rows[] = $this->row( 1, 3, 50, 0.05 );
		$rows[] = $this->row( 2, 3, 100, 0.1 );
		$rows[] = $this->row( 2, 4, 200, 0.2 );
		$rows[] = $this->row( 3, 1, 100, 0.5 );

		$this->db->insert( 'wbs_propertypairs', $rows );
	}

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';
		$lb = new LoadBalancerSingle( array("connection" => $this->db ) );
		$this->suggester = new SimpleSuggester( $lb );
	}

	public function testDatabaseHasRows() {
		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1', 'pid2' ) );
		$this->assertEquals( 5, $res->numRows() );
	}

	public function testSuggestByPropertyIds() {
		$ids = array( new PropertyId( 'p1' ) );

		$res = $this->suggester->suggestByPropertyIds( $ids, 100 );

		$this->assertEquals( new PropertyId( 'p2' ), $res[0]->getPropertyId() );
		$this->assertEquals( new PropertyId( 'p3' ), $res[1]->getPropertyId() );
	}

	public function testSuggestByItem() {
		$item = Item::newFromArray( array( 'entity' => 'q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P1' ) ) );
		$statement->setGuid( 'claim0' );
		$item->addClaim( $statement );

		$res = $this->suggester->suggestByItem( $item, 100 );

		$this->assertEquals( new PropertyId( 'p2' ), $res[0]->getPropertyId() );
		$this->assertEquals( new PropertyId( 'p3' ), $res[1]->getPropertyId() );
	}

	public function testDeprecatedProperties() {
		$ids = array( new PropertyId( 'p1' ) );

		$this->suggester->setDeprecatedPropertyIds( array( 2 ) );

		$res = $this->suggester->suggestByPropertyIds( $ids, 100 );

		$resultIds = array_map( function ( Suggestion $r ) { return $r->getPropertyId()->getNumericId(); }, $res );
		$this->assertNotContains( 2 , $resultIds );
		$this->assertContains( 3 , $resultIds );
	}
}
