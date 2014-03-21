<?php

namespace PropertySuggester;

use MediaWikiTestCase;

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


	public function setUp() {
		parent::setUp();
		$lookup = $this->getMock( 'Wikibase\EntityLookup' );
		$suggester = $this->getMock( 'PropertySuggester\Suggesters\SuggesterEngine' );

		$this->helper = new GetSuggestionsHelper( $lookup, $suggester );

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
		$result[1] = array( 'label' => 'def', 'aliases' => array('ghi', 'jkl') );

		$filtered = $this->helper->filterByPrefix( $result, 'gh' );
		$this->assertNotContains( $result[0], $filtered );
		$this->assertContains( $result[1], $filtered );
	}

	public function testMergeWithTraditionalSearchResults() {
		$suggesterResult = array();
		$suggesterResult[0] = array( 'id' => '8' );
		$suggesterResult[1] = array( 'id' => '14');

		$searchResult = array();
		$searchResult[0] = array( 'id' => '7' );
		$searchResult[1] = array( 'id' => '8');
		$searchResult[2] = array( 'id' => '13');
		$searchResult[3] = array( 'id' => '14');
		$searchResult[4] = array( 'id' => '15');
		$searchResult[5] = array( 'id' => '16');

		$mergedResult = $this->helper->mergeWithTraditionalSearchResults( $suggesterResult, $searchResult, 5 );

		$expected = array();
		$expected[0] = array( 'id' => '8' );
		$expected[1] = array( 'id' => '14');
		$expected[2] = array( 'id' => '7');
		$expected[3] = array( 'id' => '13');
		$expected[4] = array( 'id' => '15');

		$this->assertEquals( $mergedResult, $expected );
	}

	public function testGenerateSuggestions() {

		$lookup = $this->getMock( 'Wikibase\EntityLookup' );
		$suggester = $this->getMock( 'PropertySuggester\Suggesters\SuggesterEngine' );

		//Test 'generateSuggestion' with propertyList

		$properties = array();
		$properties[] = PropertyId::newFromNumber( 12 );

		$suggester->expects($this->any())
			->method('suggestByPropertyIds')
			->with($this->equalTo($properties))
			->will($this->returnValue('foo'));

		$this->helper = new GetSuggestionsHelper( $lookup, $suggester );

		//implictly also tests protected method 'cleanPropertyId'
		
		$result1 = $this->helper->generateSuggestions(null, 'P12');
		$result2 = $this->helper->generateSuggestions(null, '12');

		$this->assertEquals( $result1, 'foo' );
		$this->assertEquals( $result1, $result2 );

		//Test 'generateSuggestion' with item

		$item = Item::newFromArray( array( 'entity' => 'Q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P12' ) ) );
		$statement->setGuid( 'claim0' );
		$item->addClaim( $statement );

		$itemId = new ItemId( 'Q42' );

		$lookup->expects($this->once())
			->method('getEntity')
			->with($this->equalTo($itemId))
			->will($this->returnValue($item));

		$suggester->expects($this->any())
			->method('suggestByItem')
			->with($this->equalTo($item))
			->will($this->returnValue('foo'));

		$result3 = $this->helper->generateSuggestions('Q42', null);
		$this->assertEquals( $result3, 'foo' );
	}

	public function tearDown() {
		parent::tearDown();
	}
}

