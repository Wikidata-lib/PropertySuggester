<?php
/**
 * Created by PhpStorm.
 * User: felix.niemeyer
 * Date: 4/7/14
 * Time: 11:27 AM
 */

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

class PostgresInserter extends Inserter {
	function execute()
	{
		$myContext = $this->context;
		$dbTableName = $myContext->getDbTableName();
		$wholePath = $myContext->getWholePath();
		$myContext->getDb()->query( "
			COPY $dbTableName
			FROM '$wholePath'
			WITH DELIMITER ';'
		" );
	}
} 