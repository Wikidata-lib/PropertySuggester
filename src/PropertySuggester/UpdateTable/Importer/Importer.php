<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

interface Importer {
	/**
	 * run specific algorithm to import data to wbs_propertypairs db table from csv. Returns success
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext );
}
