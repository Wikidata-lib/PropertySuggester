<?php

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

interface Inserter {
	/**
	 * run specific algorithm to import data to wbs_propertypairs db table from csv. Returns success
	 * @param InserterContext $insertionContext
	 * @return bool
	 */
	function execute( InserterContext $insertionContext );
} 