<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

class BasicImporter implements Importer {

	/**
	 * Import using SQL Insert
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {
		$fileHandle = fopen( $importContext->getWholePath(), "r" );

		if ( $fileHandle == false ) {
			return false;
		}

		$accumulator = Array();
		$data = fgetcsv( $fileHandle, 0, ';' );

		while ( $data !== false ) {
			$accumulator[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );

			$data = fgetcsv( $fileHandle, 0, ';' );

			if ( $data === false or count( $accumulator ) > 1000 ) {
				$importContext->getDb()->insert( $importContext->getTableName(), $accumulator );
				$accumulator = Array();
			}
		}

		fclose( $fileHandle );
		return true;
	}
}
