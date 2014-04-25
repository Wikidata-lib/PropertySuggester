<?php

namespace PropertySuggester;

use MediaWikiTestCase;


/**
 * @covers PropertySuggester\SuggesterParams
 * @covers PropertySuggester\SuggesterParamsParser
 * @group PropertySuggester
 * @group API
 * @group medium
 */
class SuggesterParamsParserTest extends MediaWikiTestCase {

	/**
	 * @var SuggesterParamsParser
	 */
	protected $paramsParser;

	/**
	 * @var GetSuggestions
	 */
	protected $api;

	protected $defaultSuggesterResultSize = 100;
	protected $defaultMinProbability = 0.01;
    protected $defaultParams = array( 'entity' => null, 'properties' => null, 'continue' => 10, 'limit' => 5,
									  'language' => 'en', 'search' => '' );
    
	public function setUp() {
		parent::setUp();
		$this->api = $this->getMockBuilder( 'PropertySuggester\GetSuggestions' )->disableOriginalConstructor()->getMock();
		$this->paramsParser = new SuggesterParamsParser( $this->api, $this->defaultSuggesterResultSize, $this->defaultMinProbability );
	}

	public function testSuggesterParameters() {
		$params = $this->paramsParser->parseAndValidate(
			array_merge( $this->defaultParams, array( 'entity' => 'Q1', 'search' => '*') )
		);

		$this->assertEquals( 'Q1', $params->entity );
		$this->assertEquals( null, $params->properties );
		$this->assertEquals( 'en', $params->language );
		$this->assertEquals( 10, $params->continue );
		$this->assertEquals( 5, $params->limit );
		$this->assertEquals( 5+10, $params->suggesterLimit );
		$this->assertEquals( $this->defaultMinProbability, $params->minProbability );
		$this->assertEquals( '', $params->search );
	}

	public function testSuggesterWithSearchParameters() {
		$params = $this->paramsParser->parseAndValidate(
			array_merge( $this->defaultParams, array( 'properties' => array('P31'), 'search' => 'asd') )
		);

		$this->assertEquals( null, $params->entity );
		$this->assertEquals( array( 'P31' ), $params->properties );
		$this->assertEquals( 'en', $params->language );
		$this->assertEquals( 10, $params->continue );
		$this->assertEquals( 5, $params->limit );
		$this->assertEquals( $this->defaultSuggesterResultSize, $params->suggesterLimit );
		$this->assertEquals( 0, $params->minProbability );
		$this->assertEquals( 'asd', $params->search );
	}

	public function testSuggesterWithInvalidParameters(){
		$this->api->expects( $this->exactly(1) )->method( 'dieUsage' );

		$this->paramsParser->parseAndValidate(
			array_merge( $this->defaultParams, array( 'entity' => 'Q1', 'properties' => array('P31') ) )
		);
	}

	public function testSuggesterWithMissingParameters(){
		$this->api->expects( $this->exactly(1) )->method( 'dieUsage' );

		$this->paramsParser->parseAndValidate(
			array_merge( $this->defaultParams, array( 'entity' => null, 'properties' => null ) )
		);
	}

}
