<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

class MySQLImporter implements Importer {

	/**
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {
		$db = $importContext->getDb();
		$dbTableName = $db->tableName( $importContext->getTableName() );
		$wholePath = $importContext->getWholePath();
		$db->query( "
				LOAD DATA INFILE '$wholePath'
				INTO TABLE $dbTableName
				FIELDS
					TERMINATED BY ';'
				LINES
					TERMINATED BY '\\n'
			" );
		return true;
	}
}
