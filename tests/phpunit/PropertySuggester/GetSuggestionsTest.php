<?php

namespace PropertySuggester;

use Wikibase\Test\Api\WikibaseApiTestCase;

/**
 *
 * @covers PropertySuggester\GetSuggestions
 *
 * @group Extensions/PropertySuggester
 *
 * @group API
 * @group Database
 *
 * @group medium
 *
 */
class GetSuggestionsTest extends WikibaseApiTestCase {

	public function setUp() {
		parent::setUp();

	}

	/*
	 * @group Extensions/GetSuggestions
	 */
	public function testFilterByPrefix() {
		$this->assertEquals( 0, 0, "XXXXX" );
		$this->assertEquals( 1, 1, "XXXXX" );
		$this->assertEquals( 2, 2, "XXXXX" );
	}

	public function tearDown() {
		//own tear downs
		parent::tearDown();
	}
}

