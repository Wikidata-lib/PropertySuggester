<?php

namespace PropertySuggester;

use MediaWikiTestCase;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;

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
		$lookup = $this->getMock( "Wikibase\EntityLookup" );
		$suggester = $this->getMock( "PropertySuggester\Suggesters\SuggesterEngine" );

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

	public function tearDown() {
		parent::tearDown();
	}
}

