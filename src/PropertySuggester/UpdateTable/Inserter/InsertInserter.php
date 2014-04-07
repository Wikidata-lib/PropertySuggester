<?php

namespace PropertySuggester\UpdateTable\Inserter;

use PropertySuggester\UpdateTable\InserterContext;

class InsertInserter implements Inserter {

	/**
	 * Import using SQL Insert
	 * @param InserterContext $insertionContext
	 * @return bool
	 */
	function execute( InserterContext $insertionContext ) {
		$fileHandle = fopen( $insertionContext->getWholePath(), "r" );
		if ( $fileHandle !== false ) {
			$accumulator = Array();
			$data = fgetcsv( $fileHandle, 0, ';' );
			while ( $data !== false ) {
				$accumulator[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );

				$data = fgetcsv( $fileHandle, 0, ';' );

				if ( $data === false or count( $accumulator ) > 1000 ) {
					$insertionContext->getDb()->insert( $insertionContext->getTableName(), $accumulator );
					$accumulator = Array();
				}
			}
		} else {
			return false;
		}
		fclose( $fileHandle );
		return true;
	}
} 