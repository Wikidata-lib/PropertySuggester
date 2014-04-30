<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityLookup;
use Wikibase\TermIndex;
use InvalidArgumentException;

/**
 * @covers PropertySuggester\GetSuggestions
 * @group PropertySuggester
 * @group API
 * @group medium
 */
class GetSuggestionTest extends MediaWikiTestCase {

	/**
	 * @var GetSuggestions
	 */
	protected $getSuggestions;

	public function setUp() {
		parent::setUp();
		$apiMain =  $this->getMockBuilder( 'ApiMain' )->disableOriginalConstructor()->getMockForAbstractClass();
		$this->getSuggestions = new GetSuggestions( $apiMain, 'wbgetsuggestion' );
	}

	public function testExecution() {
		// TODO!
	}

	public function testGetAllowedParams() {
		$this->assertNotEmpty( $this->getSuggestions->getAllowedParams() );
	}

	public function testGetParamDescription() {
		$this->assertNotEmpty( $this->getSuggestions->getParamDescription() );
	}

	public function testGetDescription() {
		$this->assertNotEmpty( $this->getSuggestions->getDescription() );
	}

	public function testGetExamples() {
		$this->assertNotEmpty( $this->getSuggestions->getExamples() );
	}

}
