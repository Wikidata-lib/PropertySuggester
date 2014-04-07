<?php

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

class MySQLInserter implements Inserter {

	/**
	 * @param InserterContext $insertionContext
	 * @return bool
	 */
	function execute( InserterContext $insertionContext ) {
		$db = $insertionContext->getDb();
		$dbTableName = $db->tableName( $insertionContext->getTableName() );
		$wholePath = $insertionContext->getWholePath();
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