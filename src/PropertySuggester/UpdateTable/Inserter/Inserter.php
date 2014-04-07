<?php

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

abstract class Inserter{
	/**
	 * @var InserterContext
	 */
	protected $context = null;

	function setContext(InserterContext $insertionContext) {
		$this->context = $insertionContext;
	}

	abstract function execute();
} 