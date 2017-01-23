<?php

namespace PropertySuggester;

use ApiResult;
use MediaWikiTestCase;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\TermIndex;

/**
 * @covers PropertySuggester\ResultBuilder
 *
 * @group PropertySuggester
 * @group API
 * @group medium
 */
class ResultBuilderTest extends MediaWikiTestCase {

	/**
	 * @var ResultBuilder
	 */
	private $resultBuilder;

	public function setUp() {
		parent::setUp();

		$entityTitleLookup = $this->getMock( EntityTitleLookup::class );
		$termIndex = $this->getMock( TermIndex::class );
		$result = new ApiResult( false ); // $maxSize, no limit

		$this->resultBuilder = new ResultBuilder( $result, $termIndex, $entityTitleLookup, '' );
	}

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

		$mergedResult = $this->resultBuilder->mergeWithTraditionalSearchResults(
			$suggesterResult,
			$searchResult,
			5
		);

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
