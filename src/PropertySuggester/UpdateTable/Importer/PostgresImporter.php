<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

class PostgresImporter implements Importer {

	/**
	 * Insert using Postgres' 'COPY' statement
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {
		$db = $importContext->getDb();
		$dbTableName = $db->tableName( $importContext->getTableName() );
		$wholePath = $importContext->getWholePath();
		$db->query( "
			COPY $dbTableName
			FROM '$wholePath'
			WITH DELIMITER ';'
		" );
		return true;
	}
}
