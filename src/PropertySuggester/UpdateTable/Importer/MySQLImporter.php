<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which import entries from CSV file into DB table, using "LOAD DATA INFILE" - command.
 * Class MySQLImporter
 * @package PropertySuggester\UpdateTable\Importer
 */
class MySQLImporter implements Importer {

	/**
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {

		$lb = $importContext->getLb();
		$db = $lb->getConnection(DB_MASTER);

		$db = $importContext->getDb();
		$dbTableName = $db->tableName( $importContext->getTargetTableName() );
		$wholePath = $importContext->getCsvFilePath();
		$db->query( "
				LOAD DATA INFILE '$wholePath'
				INTO TABLE $dbTableName
				FIELDS
					TERMINATED BY ','
				LINES
					TERMINATED BY '\\n'
			" );
		$lb->reuseConnection($db);
		return true;
	}
}
