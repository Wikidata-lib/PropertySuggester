<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A interface for strategies, which imports entries from CSV file into DB table
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
interface Importer {
	/**
	 * Run specific algorithm to import data to wbs_propertypairs db table from csv. Returns success
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext );

}
