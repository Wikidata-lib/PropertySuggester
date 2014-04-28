<?php

/**
 * Class registration file for PropertySuggester.
 */
return array(
	'PropertySuggesterHooks' => 'PropertySuggesterHooks.php',
	
	'PropertySuggester\Maintenance\UpdateTable' => '/maintenance/UpdateTable.php',

	'PropertySuggester\GetSuggestions' => 'src/PropertySuggester/GetSuggestions.php',
	'PropertySuggester\GetValueSuggestions' => 'src/PropertySuggester/GetValueSuggestions.php',
	'PropertySuggester\Suggestion' => 'src/PropertySuggester/Suggestion.php',
	'PropertySuggester\SuggestionGenerator' => 'src/PropertySuggester/SuggestionGenerator.php',

	'PropertySuggester\Suggesters\SuggesterEngine' => 'src/PropertySuggester/Suggesters/SuggesterEngine.php',
	'PropertySuggester\Suggesters\SimpleSuggester' => 'src/PropertySuggester/Suggesters/SimpleSuggester.php',
	'PropertySuggester\ResultBuilder' => 'src/PropertySuggester/ResultBuilder.php',

	'PropertySuggester\ValueSuggesters\ValueSuggesterEngine' => 'src/PropertySuggester/ValueSuggesters/ValueSuggesterEngine.php',
	'PropertySuggester\ValueSuggesters\ValueSuggester' => 'src/PropertySuggester/ValueSuggesters/ValueSuggester.php',

	'PropertySuggester\UpdateTable\Importer\Importer' => 'src/PropertySuggester/UpdateTable/Importer/Importer.php',
	'PropertySuggester\UpdateTable\Importer\BasicImporter' => 'src/PropertySuggester/UpdateTable/Importer/BasicImporter.php',
	'PropertySuggester\UpdateTable\Importer\PostgresImporter' => 'src/PropertySuggester/UpdateTable/Importer/PostgresImporter.php',
	'PropertySuggester\UpdateTable\Importer\MySQLImporter' => 'src/PropertySuggester/UpdateTable/Importer/MySQLImporter.php',
	'PropertySuggester\UpdateTable\ImportContext' => 'src/PropertySuggester/UpdateTable/ImportContext.php'
);