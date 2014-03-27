<?php

/**
 * Class registration file for PropertySuggester.
 */
return array(
	'PropertySuggesterHooks' => 'PropertySuggesterHooks.php',
	
	'PropertySuggester\Maintenance\UpdateTable' => '/maintenance/UpdateTable.php',

	'PropertySuggester\SpecialSuggester' => 'src/PropertySuggester/SpecialSuggester.php',

	'PropertySuggester\GetSuggestions' => 'src/PropertySuggester/GetSuggestions.php',
	'PropertySuggester\GetSuggestionsHelper' => 'src/PropertySuggester/GetSuggestionsHelper.php',
	
	'PropertySuggester\Suggesters\Suggestion' => 'src/PropertySuggester/Suggesters/Suggestion.php',
	'PropertySuggester\Suggesters\SuggesterEngine' => 'src/PropertySuggester/Suggesters/SuggesterEngine.php',
	'PropertySuggester\Suggesters\SimplePHPSuggester' => 'src/PropertySuggester/Suggesters/SimplePHPSuggester.php',
);