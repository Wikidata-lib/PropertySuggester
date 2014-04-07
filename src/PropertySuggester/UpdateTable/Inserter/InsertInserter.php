<?php
/**
 * Created by PhpStorm.
 * User: felix.niemeyer
 * Date: 4/7/14
 * Time: 11:27 AM
 */

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

class InsertInserter extends Inserter {

	function execute()
	{
		$myContext = $this->context;
		$fileHandle = fopen( $myContext->getWholePath(), "r" );
		if ( $fileHandle !== false ) {
			$accSize = 0;
			$accumulator = Array();
			$data = fgetcsv( $fileHandle, 0, ';' );
			while ( $data !== false ) {
				$accumulator[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );
				$accSize++;

				$data = fgetcsv( $fileHandle, 0, ';' );

				if ( $data === false or $accSize > 1000 ) {
					$myContext->getDb()->insert( $myContext->getTableName(), $accumulator );
					$accumulator = Array();
					$accSize = 0;
				}
			}
		}
		fclose( $fileHandle );
	}
} 