<?php

/**
 * Class registration file for PropertySuggester.
 */
return array(
	'PropertySuggesterHooks' => 'PropertySuggesterHooks.php',

	'PropertySuggester\SpecialSuggester' => 'src/PropertySuggester/SpecialSuggester.php',

	'PropertySuggester\Maintenance\UpdateTable' => '/maintenance/UpdateTable.php',

	'PropertySuggester\GetSuggestions' => 'src/PropertySuggester/GetSuggestions.php',
	'PropertySuggester\GetSuggestionsHelper' => 'src/PropertySuggester/GetSuggestionsHelper.php',
	'PropertySuggester\ResultBuilder' => 'src/PropertySuggester/ResultBuilder.php',
	
	'PropertySuggester\Suggesters\Suggestion' => 'src/PropertySuggester/Suggesters/Suggestion.php',
	'PropertySuggester\Suggesters\SuggesterEngine' => 'src/PropertySuggester/Suggesters/SuggesterEngine.php',
	'PropertySuggester\Suggesters\SimpleSuggester' => 'src/PropertySuggester/Suggesters/SimpleSuggester.php',

	'PropertySuggester\UpdateTable\Inserter\Inserter' => 'src/PropertySuggester/UpdateTable/Inserter/Inserter.php',
	'PropertySuggester\UpdateTable\Inserter\InsertInserter' => 'src/PropertySuggester/UpdateTable/Inserter/InsertInserter.php',
	'PropertySuggester\UpdateTable\Inserter\PostgresInserter' => 'src/PropertySuggester/UpdateTable/Inserter/PostgresInserter.php',
	'PropertySuggester\UpdateTable\Inserter\MySQLInserter' => 'src/PropertySuggester/UpdateTable/Inserter/MySQLInserter.php',
	'PropertySuggester\UpdateTable\InserterContext' => 'src/PropertySuggester/UpdateTable/InserterContext.php'
);