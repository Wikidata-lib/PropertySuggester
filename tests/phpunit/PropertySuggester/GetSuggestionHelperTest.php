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

	public function tearDown() {
		parent::tearDown();
	}
}

