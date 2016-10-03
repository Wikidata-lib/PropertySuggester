<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PropertySuggesterHooks;
use RequestContext;
use Title;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers PropertySuggesterHooks
 *
 * @group PropertySuggester
 * @group Wikibase
 */
class PropertySuggesterHooksTest extends MediaWikiTestCase {

	public function testOnBeforePageDisplay_resourceLoaderModuleAdded() {
		$title = $this->getTitleForId( new ItemId( 'Q1' ) );

		$context = $this->getContext( $title );
		$output = $context->getOutput();
		$skin = $context->getSkin();

		PropertySuggesterHooks::onBeforePageDisplay( $output, $skin );

		$this->assertContains( 'ext.PropertySuggester.EntitySelector', $output->getModules() );
	}

	/**
	 * @dataProvider onBeforePageDisplay_resourceLoaderModuleNotAddedProvider
	 */
	public function testOnBeforePageDisplay_resourceLoaderModuleNotAdded( Title $title = null ) {
		$context = $this->getContext( $title );
		$output = $context->getOutput();
		$skin = $context->getSkin();

		PropertySuggesterHooks::onBeforePageDisplay( $output, $skin );

		$this->assertNotContains( 'ext.PropertySuggester.EntitySelector', $output->getModules() );
	}

	public function onBeforePageDisplay_resourceLoaderModuleNotAddedProvider() {
		return [
			[ $this->getTitleForId( new PropertyId( 'P1' ) ) ],
			[ Title::makeTitle( NS_HELP, 'Contents' ) ],
			[ null ]
		];
	}

	private function getTitleForId( EntityId $entityId ) {
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		return $entityContentFactory->getTitleForId( $entityId );
	}

	private function getContext( Title $title = null ) {
		$context = RequestContext::getMain();
		$context->setTitle( $title );

		return $context;
	}

}
