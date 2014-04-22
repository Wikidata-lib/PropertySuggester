<?php

/**
 * Class registration file for PropertySuggester.
 */
return array(
	'PropertySuggesterHooks' => 'PropertySuggesterHooks.php',
	
	'PropertySuggester\Maintenance\UpdateTable' => '/maintenance/UpdatePropertyRelationsTable.php',

	'PropertySuggester\GetSuggestions' => 'src/PropertySuggester/GetSuggestions.php',
	'PropertySuggester\GetSuggestionsHelper' => 'src/PropertySuggester/GetSuggestionsHelper.php',
	'PropertySuggester\GetValueSuggestions' => 'src/PropertySuggester/GetValueSuggestions.php',

	'PropertySuggester\ResultBuilder\ResultBuilder' => 'src/PropertySuggester/ResultBuilder/ResultBuilder.php',

	'PropertySuggester\Suggestion' => 'src/PropertySuggester/Suggestion.php',

	'PropertySuggester\Suggesters\SuggesterEngine' => 'src/PropertySuggester/Suggesters/SuggesterEngine.php',
	'PropertySuggester\Suggesters\SimpleSuggester' => 'src/PropertySuggester/Suggesters/SimpleSuggester.php',

	'PropertySuggester\ValueSuggester\ValueSuggesterEngine' => 'src/PropertySuggester/ValueSuggester/ValueSuggesterEngine.php',
	'PropertySuggester\ValueSuggester\ValueSuggester' => 'src/PropertySuggester/ValueSuggester/ValueSuggester.php',

	'PropertySuggester\UpdateTable\Importer\Importer' => 'src/PropertySuggester/UpdateTable/Importer/Importer.php',
	'PropertySuggester\UpdateTable\Importer\BasicImporter' => 'src/PropertySuggester/UpdateTable/Importer/BasicImporter.php',
	'PropertySuggester\UpdateTable\Importer\PostgresImporter' => 'src/PropertySuggester/UpdateTable/Importer/PostgresImporter.php',
	'PropertySuggester\UpdateTable\Importer\MySQLImporter' => 'src/PropertySuggester/UpdateTable/Importer/MySQLImporter.php',
	'PropertySuggester\UpdateTable\ImportContext' => 'src/PropertySuggester/UpdateTable/ImportContext.php'
);