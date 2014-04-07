<?php

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

class PostgresInserter implements Inserter {

	/**
	 * Insert using Postgres' 'COPY' statement
	 * @param InserterContext $insertionContext
	 * @return bool
	 */
	function execute( InserterContext $insertionContext ) {
		$db = $insertionContext->getDb();
		$dbTableName = $db->tableName( $insertionContext->getTableName() );
		$wholePath = $insertionContext->getWholePath();
		$db->query( "
			COPY $dbTableName
			FROM '$wholePath'
			WITH DELIMITER ';'
		" );
		return true;
	}
} 