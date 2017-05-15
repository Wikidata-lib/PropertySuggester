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
		$suggesterResult = [
			[ 'id' => '8' ],
			[ 'id' => '14' ],
			[ 'id' => '20' ],
		];

		$searchResult = [
			[ 'id' => '7' ],
			[ 'id' => '8' ],
			[ 'id' => '13' ],
			[ 'id' => '14' ],
			[ 'id' => '15' ],
			[ 'id' => '16' ],
		];

		$mergedResult = $this->resultBuilder->mergeWithTraditionalSearchResults(
			$suggesterResult,
			$searchResult,
			5
		);

		$expected = [
			[ 'id' => '8' ],
			[ 'id' => '14' ],
			[ 'id' => '20' ],
			[ 'id' => '7' ],
			[ 'id' => '13' ],
		];

		$this->assertEquals( $mergedResult, $expected );
	}

}
