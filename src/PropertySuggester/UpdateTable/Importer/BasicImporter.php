<?php

namespace PropertySuggester\UpdateTable\Importer;

use DatabaseBase;
use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which import entries from CSV file into DB table, used as fallback, when no special import commands
 * are supported by the dbms.
 *
 * @author BP2013N2
 * @licence GNU GPL v2+
 */
class BasicImporter implements Importer {

	/**
	 * Import using SQL Insert
	 * @param ImportContext $importContext
	 * @return bool
	 */
	function importFromCsvFileToDb( ImportContext $importContext ) {

		if ( ( $fileHandle = fopen( $importContext->getCsvFilePath(), "r" ) ) == false ) {
			return false;
		}

		$lb = $importContext->getLb();
		$db = $lb->getConnection( DB_MASTER );
		$this->doImport( $fileHandle, $db, $importContext );
		$lb->reuseConnection( $db );

		fclose( $fileHandle );

		return true;
	}

	/**
	 * @param $fileHandle
	 * @param DatabaseBase $db
	 * @param ImportContext $importContext
	 */
	private function doImport( $fileHandle, DatabaseBase $db, ImportContext $importContext ) {
		$accumulator = Array();
		$i = 0;

		while ( true ) {
			$data = fgetcsv( $fileHandle, 0, $importContext->getCsvDelimiter());

			if ( $data == false || ++$i > 1000 ) {
				$db->insert( $importContext->getTargetTableName(), $accumulator );
				if ( $data ) {
					$accumulator = array();
					$i = 0;
				} else {
					break;
				}
			}

			$accumulator[] = array( 'pid1' => $data[0], 'pid2' => $data[1], 'count' => $data[2], 'probability' => $data[3] );
		}
	}

}
