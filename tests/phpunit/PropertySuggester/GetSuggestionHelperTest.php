<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PHPUnit_Framework_MockObject_MockObject;

use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityLookup;

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

	/**
	 * @var SuggesterEngine
	 */
	protected $suggester;

	/**
	 * @var EntityLookup
	 */
	protected $lookup;

	public function setUp() {
		parent::setUp();

		$this->lookup = $this->getMock( 'Wikibase\EntityLookup' );
		$this->suggester = $this->getMock( 'PropertySuggester\Suggesters\SuggesterEngine' );

		$this->helper = new GetSuggestionsHelper( $this->lookup, $this->suggester );

	}

	public function testGenerateSuggestionsWithPropertyList() {
		$properties = array();
		$properties[] = PropertyId::newFromNumber( 12 );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByPropertyIds' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		$result1 = $this->helper->generateSuggestionsByPropertyList( 'P12' );
		$result2 = $this->helper->generateSuggestionsByPropertyList( '12' );

		$this->assertEquals( $result1, array( 'foo' ) );
		$this->assertEquals( $result2, array( 'foo' ) );

	}

	public function testGenerateSuggestionsWithItem() {
		$item = Item::newFromArray( array( 'entity' => 'Q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P12' ) ) );
		$statement->setGuid( 'claim0' ); // otherwise "InvalidArgumentException: Can't add a Claim without a GUID."
		$item->addClaim( $statement );

		$itemId = new ItemId( 'Q42' );

		$this->lookup->expects( $this->once() )
			->method( 'getEntity' )
			->with( $this->equalTo( $itemId ) )
			->will( $this->returnValue( $item ) );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByItem' )
			->with( $this->equalTo( $item ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		$result3 = $this->helper->generateSuggestionsByItem( 'Q42' );
		$this->assertEquals( $result3, array( 'foo' ) );
	}

	public function tearDown() {
		parent::tearDown();
	}
}

