<?php

/**
 * Class registration file for PropertySuggester.
 */
return array(
	'PropertySuggesterHooks' => 'PropertySuggesterHooks.php',
	
	'PropertySuggester\Maintenance\UpdateTable' => '/maintenance/UpdatePropertyRelationsTable.php',

	'PropertySuggester\GetSuggestions' => 'src/PropertySuggester/GetSuggestions.php',
	'PropertySuggester\GetSuggestionsHelper' => 'src/PropertySuggester/GetSuggestionsHelper.php',
	'PropertySuggester\GetSuggestionsHelper' => 'src/PropertySuggester/GetValueSuggestions.php',

	'PropertySuggester\ResultBuilder\ResultBuilder' => 'src/PropertySuggester/ResultBuilder/ResultBuilder.php',
	'PropertySuggester\ResultBuilder\SuggestionsResultBuilder' => 'src/PropertySuggester/ResultBuilder/SuggestionsResultBuilder.php',
	'PropertySuggester\ResultBuilder\ValueSuggestionsResultBuilder' => 'src/PropertySuggester/ResultBuilder/ValueSuggestionsResultBuilder.php',

	'PropertySuggester\Suggesters\Suggestion' => 'src/PropertySuggester/Suggesters/Suggestion.php',
	'PropertySuggester\Suggesters\SuggesterEngine' => 'src/PropertySuggester/Suggesters/SuggesterEngine.php',
	'PropertySuggester\Suggesters\SimpleSuggester' => 'src/PropertySuggester/Suggesters/SimpleSuggester.php',

	'PropertySuggester\ValueSuggester\Suggestion' => 'src/PropertySuggester/ValueSuggester/Suggestion.php',
	'PropertySuggester\ValueSuggester\SuggesterEngine' => 'src/PropertySuggester/ValueSuggester/SuggesterEngine.php',
	'PropertySuggester\ValueSuggester\SimpleSuggester' => 'src/PropertySuggester/ValueSuggester/SimpleSuggester.php',

	'PropertySuggester\UpdateTable\Importer\Importer' => 'src/PropertySuggester/UpdateTable/Importer/Importer.php',
	'PropertySuggester\UpdateTable\Importer\BasicImporter' => 'src/PropertySuggester/UpdateTable/Importer/BasicImporter.php',
	'PropertySuggester\UpdateTable\Importer\PostgresImporter' => 'src/PropertySuggester/UpdateTable/Importer/PostgresImporter.php',
	'PropertySuggester\UpdateTable\Importer\MySQLImporter' => 'src/PropertySuggester/UpdateTable/Importer/MySQLImporter.php',
	'PropertySuggester\UpdateTable\ImportContext' => 'src/PropertySuggester/UpdateTable/ImportContext.php'
);