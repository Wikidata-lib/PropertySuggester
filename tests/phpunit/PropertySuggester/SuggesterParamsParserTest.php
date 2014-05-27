<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use InvalidArgumentException;

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

	protected $defaultSuggesterResultSize = 100;
	protected $defaultMinProbability = 0.01;
    protected $defaultParams = array( 'entity' => null, 'properties' => null, 'continue' => 10, 'limit' => 5,
									  'language' => 'en', 'search' => '', 'context' => 'item' );
    
	public function setUp() {
		parent::setUp();
		$this->paramsParser = new SuggesterParamsParser( $this->defaultSuggesterResultSize, $this->defaultMinProbability );
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
		$this->assertEquals( 'item', $params->context );
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
		$this->assertEquals( 'item', $params->context );
	}

	/**
 	* @expectedException InvalidArgumentException
 	*/
	public function testSuggestionWithoutEntityOrProperties() {
		$this->paramsParser->parseAndValidate( array( 'entity' => null, 'properties' => null) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSuggestionWithEntityAndProperties() {
		$this->paramsParser->parseAndValidate( array( 'entity' => 'Q1', 'properties' => array('P31') ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSuggestionWithNonNumericContinue() {
		$this->paramsParser->parseAndValidate( array( 'entity' => 'Q1', 'properties' => null, 'continue' => 'drop') );
	}

}
