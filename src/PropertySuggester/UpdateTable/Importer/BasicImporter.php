<?php

namespace PropertySuggester\UpdateTable\Importer;

use Database;
use UnexpectedValueException;
use PropertySuggester\UpdateTable\ImportContext;

/**
 * A strategy, which imports entries from CSV file into DB table, used as fallback, when no special import commands
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
	 * @param Database $db
	 * @param ImportContext $importContext
	 * @throws UnexpectedValueException
	 */
	private function doImport( $fileHandle, Database $db, ImportContext $importContext ) {
		$accumulator = array();
		$batchSize = $importContext->getBatchSize();
		$i = 0;
		$header = fgetcsv( $fileHandle, 0, $importContext->getCsvDelimiter() ); //this is to get the csv-header
		$expectedHeader = array( 'pid1', 'qid1', 'pid2', 'count', 'probability', 'context' );
		if( $header != $expectedHeader ) {
			throw new UnexpectedValueException( "provided csv-file does not match the expected format:\n" . join( ',', $expectedHeader ) );
		}
		while ( true ) {
			$data = fgetcsv( $fileHandle, 0, $importContext->getCsvDelimiter() );

			if ( $data == false || ++$i % $batchSize == 0 ) {
				$db->commit( __METHOD__, 'flush' );
				wfGetLBFactory()->waitForReplication();
				$db->insert( $importContext->getTargetTableName(), $accumulator );
				if ( ! $importContext->isQuiet() ) {
					print "$i rows inserted\n";
				}
				$accumulator = array();
				if ( $data == false ) {
					break;
				}
			}

			$qid1 = is_numeric( $data[1] ) ? $data[1] : 0;

			$accumulator[] = array( 'pid1' => $data[0], 'qid1' => $qid1, 'pid2' => $data[2], 'count' => $data[3],
									'probability' => $data[4], 'context' => $data[5] );
		}
	}

}
