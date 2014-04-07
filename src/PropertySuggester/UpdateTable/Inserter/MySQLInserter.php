<?php
/**
 * Created by PhpStorm.
 * User: felix.niemeyer
 * Date: 4/7/14
 * Time: 11:27 AM
 */

namespace PropertySuggester\UpdateTable\Inserter;


class MySQLInserter extends Inserter {
	function execute()
	{
		$myContext = $this->context;
		$dbTableName = $myContext->getDbTableName();
		$wholePath = $myContext->getWholePath();
		$myContext->getDb()->query( "
				LOAD DATA INFILE '$wholePath'
				INTO TABLE $dbTableName
				FIELDS
					TERMINATED BY ';'
				LINES
					TERMINATED BY '\\n'
			" );
	}
} 