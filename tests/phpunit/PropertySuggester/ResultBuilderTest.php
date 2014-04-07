<?php

namespace PropertySuggester;

use MediaWikiTestCase;

/**
 *
 * @covers PropertySuggester\ResultBuilder
 *
 * @group PropertySuggester
 *
 * @group API
 *
 * @group medium
 *
 */
class ResultBuilderTest extends MediaWikiTestCase {

	/**
	 * @var ResultBuilder
	 */
	protected $resultBuilder;

	public function setUp() {
		parent::setUp();
		$this->resultBuilder = new ResultBuilder( );
	}

	public function testFilterByPrefix() {
		$result = array();
		$result[0] = array( 'label' => 'Abc', 'aliases' => array() );
		$result[1] = array( 'label' => 'def', 'aliases' => array() );

		$filtered = $this->resultBuilder->filterByPrefix( $result, 'ab' );
		$this->assertContains( $result[0], $filtered );
		$this->assertNotContains( $result[1], $filtered );
	}

	public function testFilterByPrefixWithAlias() {
		$result = array();
		$result[0] = array( 'label' => 'Abc', 'aliases' => array() );
		$result[1] = array( 'label' => 'def', 'aliases' => array( 'Ghi', 'Jkl' ) );

		$filtered = $this->resultBuilder->filterByPrefix( $result, 'gh' );
		$this->assertNotContains( $result[0], $filtered );
		$this->assertContains( $result[1], $filtered );
	}

	/* TODO!
	public function testFilterByPrefixWithNonAscii() {
		$result = array();
		$result[0] = array( 'label' => 'Öüü', 'aliases' => array() );
		$result[1] = array( 'label' => 'xxx', 'aliases' => array( 'Äöö', 'jkl' ) );

		$filtered = $this->helper->filterByPrefix( $result, 'äö' );
		$this->assertNotContains( $result[0], $filtered );
		$this->assertContains( $result[1], $filtered );
	} */

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

		$mergedResult = $this->resultBuilder->mergeWithTraditionalSearchResults( $suggesterResult, $searchResult, 5 );

		$expected = array();
		$expected[0] = array( 'id' => '8' );
		$expected[1] = array( 'id' => '14' );
		$expected[2] = array( 'id' => '20' );
		$expected[3] = array( 'id' => '7' );
		$expected[4] = array( 'id' => '13' );

		$this->assertEquals( $mergedResult, $expected );
	}

	public function tearDown() {
		parent::tearDown();
	}
}