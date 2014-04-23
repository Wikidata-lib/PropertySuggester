<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A interface for strategies, which import entries from CSV file into DB table
 * Interface Importer
 * @package PropertySuggester\UpdateTable\Importer
 */
interface Importer {
	/**
	 * run specific algorithm to import data to wbs_propertypairs db table from csv. Returns success
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext );
}
