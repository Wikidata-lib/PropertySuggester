# PropertySuggester

PropertySuggester is an extension to Wikibase to provide suggested properties when a user tries to add new
statements to an item.

[![Build Status](https://travis-ci.org/Wikidata-lib/PropertySuggester.svg?branch=master)](https://travis-ci.org/Wikidata-lib/PropertySuggester)
[![Coverage Status](https://coveralls.io/repos/Wikidata-lib/PropertySuggester/badge.png?branch=master)](https://coveralls.io/r/Wikidata-lib/PropertySuggester?branch=master)

On [Packagist](https://packagist.org/packages/propertysuggester/property-suggester):
[![Latest Stable Version](https://poser.pugx.org/propertysuggester/property-suggester/v/stable.png)](https://packagist.org/packages/propertysuggester/propertysuggester)
[![License](https://poser.pugx.org/propertysuggester/property-suggester/license.png)](https://packagist.org/packages/propertysuggester/propertysuggester)

## Installation

The recommended way to use this library is via [Composer](http://getcomposer.org/).

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `propertysuggester/property-suggester` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file:

    {
        "require": {
            "propertysuggester/property-suggester": "*"
        }
    }

### Setup

This extension adds a new table "wbs_propertypairs" that contains the information that is needed to generate
suggestions. You can use [PropertySuggester-Python](https://github.com/Wikidata-lib/PropertySuggester-Python) to
generate this data from a wikidata dump.

* run ```composer dump-autoload``` in the extension folder (if installed without composer)
* add ```require_once "$IP/extensions/PropertySuggester/PropertySuggester.php";``` to your localsettings
* run ```maintenance/update.php``` to create the necessary table
* follow the readme of PropertySuggester-Python to generate and load suggestion data

### Configuration

* $wgPropertySuggesterMinProbability - a float that sets a minimum threshold for suggestions (default 0.05)
* $wgPropertySuggesterDeprecatedIds - a list of ints that blacklist suggestions
* $wgPropertySuggesterInitialSuggestions - a list of ints that will be suggested when no statements exist

## Release notes

### 3.1.1 (2016-08-03)
* Follow up fix for entity suggester, update method call in EntitySuggester.

### 3.1.0 (2016-08-03)
* Adapted entity suggester for changes in Wikibase.

### 3.0.2 (2016-06-20)
* Adapt entity type for namespaces
* Minor cleanups

### 3.0.1 (2016-03-14)
* Defined compatibility with Wikibase DataModel ~6.0

### 3.0.0 (2016-02-25)
* Now requires PHP 5.5.0 or higher
* Defined compatibility with Wikibase DataModel ~5.0

### 2.4.5 (2015-12-27)
* Add i18n to the `wbsgetsuggestions` api module. This makes MediaWiki's `ApiDocumentationTest` pass.

### 2.4.4 (2015-10-14)
* Fixed ResourceLoader dependencies of the `jquery.wikibase.entityselector` module.

### 2.4.3 (2015-09-17)
* Defined compatibility with Wikibase DataModel Services ~3.0

### 2.4.2 (2015-09-03)
* Defined compatibility with Wikibase DataModel Services ~2.0

### 2.4.1 (2015-08-27)
* Added explicit dependency on Wikibase DataModel.
* `wbsgetsuggestions` API never returns more than one `aliases` entry per match.
* `wbsgetsuggestions` does not return `aliases` when the label already is a successful match.
* `wbsearchentities` is explicitely called with the `uselang` option set.

### 2.4.0 (2015-08-12)
* Require DataModelServices ~1.1
* Use EntityLookup interface from DataModelServices to replace removed WikibaseLib interface

### 2.3.1 (2015-07-13)
* Fix use of WikibaseApiTestCase due to namespace change

### 2.3.0 (2015-06-26)
* Replace use of Wikibase\TermIndex::getMatchingIDs with Wikibase\TermIndex::getTopMatchingTerms.
* EntitySelector no longer passes "type" parameter to wbsgetsuggestions which avoids an "Unrecognized parameter" warning.

### 2.2.1 (2015-06-18)
* Replace use of Wikibase\Term with Wikibase\TermIndexEntry, per change in Wikibase.

### 2.2.0 (2015-04-29)
* Adjust api code for core api changes (this requires a newer mediawiki core)
* Replace deprecated Item::addClaim

### 2.1.0 (2015-04-02)
* Suggest initial properties for items and properties with no statements yet.

### 2.0.6 (2015-02-20)
* No longer use Wikibase\Utils as it was renamed
* Remove obvious function-level profiling

### 2.0.5 (2015-01-29)
* Fix TermIndex method call in ResultBuilder

### 2.0.4 (2015-01-13)
* Adjust to changes in ValueView 0.10.0.

### 2.0.3 (2015-01-06)
* Adjust to removal of claimview

### 2.0.2 (2014-12-17)
* Fix TermIndex method call in ResultBuilder

### 2.0.1 (2014-11-11)
Adjust to new version of DataModel-JavaScript

### 2.0.0 (2014-11-10)
* Consider classifying properties (needs version 2.0.0 of PropertySuggester-Python)

### 1.1.4 (2014-10-22)
* Replace usage of Wikibase\NamespaceUtils for compatibility with Wikibase Repo.
* Specified GPL-2.0+ license

### 1.1.3 (2014-10-17)
* Wikibase Data Model 2.0 compatibility fixes.

### 1.1.2 (2014-09-05)
* Wikibase Data Model 1.0 compatibility fixes.

### 1.1.1 (2014-08-27)
* Update namespace of EntityTitleLookup, per change in Wikibase.

### 1.1.0 (2014-07-25)
* Suggest properties for qualifiers and references based on the property of the mainsnak

### 1.0.0 (2014-07-01)

* Provide Property-Suggestions based on correlations to other properties in the item
