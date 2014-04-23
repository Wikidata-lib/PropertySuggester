<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which import entries from CSV file into DB table, using "COPY" - command.
 * Class PostgresImporter
 * @package PropertySuggester\UpdateTable\Importer
 */
class PostgresImporter implements Importer {

	/**
	 * Insert using Postgres' 'COPY' statement
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
			COPY $dbTableName
			FROM '$wholePath'
			WITH DELIMITER ','
		" );
		$lb->reuseConnection($db);
		return true;
	}
}
