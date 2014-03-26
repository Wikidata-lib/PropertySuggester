<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PHPUnit_Framework_MockObject_MockObject;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Entity\ItemId;

/**
 *
 * @covers PropertySuggester\GetSuggestionHelper
 *
 * @group PropertySuggester
 *
 * @group API
 *
 * @group medium
 *
 */
class GetSuggestionHelperTest extends MediaWikiTestCase {

	/**
	 * @var GetSuggestionsHelper
	 */
	protected $helper;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $suggester;
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $lookup;

	public function setUp() {
		parent::setUp();

		$this->lookup = $this->getMock( 'Wikibase\EntityLookup' );
		$this->suggester = $this->getMock( 'PropertySuggester\Suggesters\SuggesterEngine' );

		$this->helper = new GetSuggestionsHelper( $this->lookup, $this->suggester );

	}

	public function testFilterByPrefix() {
		$result = array();
		$result[0] = array( 'label' => 'abc', 'aliases' => array() );
		$result[1] = array( 'label' => 'def', 'aliases' => array() );

		$filtered = $this->helper->filterByPrefix( $result, 'ab' );
		$this->assertContains( $result[0], $filtered );
		$this->assertNotContains( $result[1], $filtered );
	}

	public function testFilterByPrefixWithAlias() {
		$result = array();
		$result[0] = array( 'label' => 'abc', 'aliases' => array() );
		$result[1] = array( 'label' => 'def', 'aliases' => array( 'ghi', 'jkl' ) );

		$filtered = $this->helper->filterByPrefix( $result, 'gh' );
		$this->assertNotContains( $result[0], $filtered );
		$this->assertContains( $result[1], $filtered );
	}

	public function testMergeWithTraditionalSearchResults() {
		$suggesterResult = array();
		$suggesterResult[0] = array( 'id' => '8' );
		$suggesterResult[1] = array( 'id' => '14' );
		$suggesterResult[2] = array( 'id' => '20' );

		$searchResult = array();
		$searchResult[0] = array( 'id' => '7' );
		$searchResult[1] = array( 'id' => '8' );
		$searchResult[2] = array( 'id' => '13' );
		$searchResult[3] = array( 'id' => '14' );
		$searchResult[4] = array( 'id' => '15' );
		$searchResult[5] = array( 'id' => '16' );

		$mergedResult = $this->helper->mergeWithTraditionalSearchResults( $suggesterResult, $searchResult, 5 );

		$expected = array();
		$expected[0] = array( 'id' => '8' );
		$expected[1] = array( 'id' => '14' );
		$expected[2] = array( 'id' => '20' );
		$expected[3] = array( 'id' => '7' );
		$expected[4] = array( 'id' => '13' );

		$this->assertEquals( $mergedResult, $expected );
	}

	public function testGenerateSuggestionsWithPropertyList() {
		$properties = array();
		$properties[] = PropertyId::newFromNumber( 12 );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByPropertyIds' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		//implictly also tests protected method 'cleanPropertyId'

		$result1 = $this->helper->generateSuggestions( null, 'P12' );
		$result2 = $this->helper->generateSuggestions( null, '12' );

		$this->assertEquals( $result1, array( 'foo' ) );
		$this->assertEquals( $result1, $result2 );

	}

	public function testGenerateSuggestionsWithItem() {
		$item = Item::newFromArray( array( 'entity' => 'Q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P12' ) ) );
		$statement->setGuid( 'claim0' ); // otherwise "InvalidArgumentException: Can't add a Claim without a GUID."
		$item->addClaim( $statement );

		$itemId = new ItemId( 'Q42' );

		$this->lookup->expects( $this->once() )
			->method( 'getEntity' )
			->with( $this->equalTo( $itemId ) )
			->will( $this->returnValue( $item ) );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByItem' )
			->with( $this->equalTo( $item ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		$result3 = $this->helper->generateSuggestions( 'Q42', null );
		$this->assertEquals( $result3, array( 'foo' ) );
	}

	public function tearDown() {
		parent::tearDown();
	}
}

