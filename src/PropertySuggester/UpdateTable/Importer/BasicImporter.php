<?php

namespace PropertySuggester\UpdateTable\Importer;

use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which import entries from CSV file into DB table, used as fallback, when no special import commands are supported by the dbms
 * Class BasicImporter
 * @package PropertySuggester\UpdateTable\Importer
 */
class BasicImporter implements Importer {

	/**
	 * Import using SQL Insert
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {
		$fileHandle = fopen( $importContext->getCsvFilePath(), "r" );

		$lb = $importContext->getLb();
		$db = $lb->getConnection( DB_MASTER );

		if ( $fileHandle == false ) {
			return false;
		}

		doImport($fileHandle, $db, $importContext);

		$lb->reuseConnection( $db );

		fclose( $fileHandle );
		return true;
	}

	private function doImport($fileHandle, $db, $importContext) {
		$accumulator = Array();
		$i = 0;

		while ( ( $data = fgetcsv( $fileHandle, 0, ',' ) ) !== false ) {
			$accumulator[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );
			++$i;

			if ( $data === false || $i > 1000 ) {
				$db->insert( $importContext->getTargetTableName(), $accumulator );
				$i = 0;
				$accumulator = Array();
			}
		}
	}
}
