<?php

namespace PropertySuggester;

use ApiResult;
use MediaWikiTestCase;

/**
 * @covers PropertySuggester\ResultBuilder
 * @group PropertySuggester
 * @group API
 * @group medium
 */
class ResultBuilderTest extends MediaWikiTestCase {

	/**
	 * @var ResultBuilder
	 */
	protected $resultBuilder;

	public function setUp() {
		parent::setUp();
		$apiMain =  $this->getMockBuilder( 'ApiMain' )->disableOriginalConstructor()->getMockForAbstractClass();

		$entityTitleLookup = $this->getMock( 'Wikibase\EntityTitleLookup' );
		$termIndex = $this->getMock( 'Wikibase\TermIndex' );
		$result = new ApiResult( $apiMain );

		$this->resultBuilder = new ResultBuilder( $result, $termIndex, $entityTitleLookup, '' );
	}

/*	public function testBuildJson() {
		$suggestions = array(
			new Suggestion( new PropertyId( 'P1' ), 0.3 ),
			new Suggestion( new PropertyId( 'P2' ), 0.5 )
		);

		$result = $this->resultBuilder->createResultArray( $suggestions, 'en' );

		$this->assertArrayEquals( array(), $result );
	}*/

	public function testMergeWithTraditionalSearchResults() {
		$suggesterResult = array(
			array( 'id' =>  '8' ),
			array( 'id' => '14' ),
			array( 'id' => '20' )
		);

		$searchResult = array(
			array( 'id' =>  '7' ),
			array( 'id' =>  '8' ),
			array( 'id' => '13' ),
			array( 'id' => '14' ),
			array( 'id' => '15' ),
			array( 'id' => '16' )
		);

		$mergedResult = $this->resultBuilder->mergeWithTraditionalSearchResults( $suggesterResult, $searchResult, 5 );

		$expected = array(
			array( 'id' =>  '8' ),
			array( 'id' => '14' ),
			array( 'id' => '20' ),
			array( 'id' =>  '7' ),
			array( 'id' => '13' )
		);

		$this->assertEquals( $mergedResult, $expected );
	}

}
