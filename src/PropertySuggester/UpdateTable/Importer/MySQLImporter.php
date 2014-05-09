<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which imports entries from CSV file into DB table, using "LOAD DATA INFILE" - command.
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class MySQLImporter implements Importer {

	/**
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {

		$lb = $importContext->getLb();
		$db = $lb->getConnection( DB_MASTER );

		$dbTableName = $db->tableName( $importContext->getTargetTableName() );
		$fullPath = $db->addQuotes( $importContext->getCsvFilePath() );
		$delimiter = $db->addQuotes( $importContext->getCsvDelimiter() );

		$db->query("
				LOAD DATA LOCAL INFILE $fullPath
				INTO TABLE $dbTableName
				FIELDS
					TERMINATED BY $delimiter
				LINES
					TERMINATED BY '\\n'
				IGNORE 1 LINES
				(pid1, qid1, pid2, count, probability, context)
				SET qid1 = nullif(@vtwo, '')
			" );
		$lb->reuseConnection( $db );
		return true;
	}

}
