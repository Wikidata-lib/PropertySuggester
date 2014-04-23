<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which import entries from CSV file into DB table, using "COPY" - command.
 * Class PostgresImporter
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class PostgresImporter implements Importer {

	/**
	 * Insert using Postgres' 'COPY' statement
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {

		$lb = $importContext->getLb();
		$db = $lb->getConnection( DB_MASTER );

		$dbTableName = $db->tableName( $importContext->getTargetTableName() );
		$wholePath = $importContext->getCsvFilePath();
		$db->query( "
			COPY $dbTableName
			FROM '$wholePath'
			WITH DELIMITER '" . $importContext->getCsvDelimiter() . "'
		" );
		$lb->reuseConnection( $db );
		return true;
	}

}
