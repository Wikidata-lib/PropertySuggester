# PropertySuggester

PropertySuggester is an extension to Wikibase to provide suggested properties when a user tries to add new
statements to an item.

[![Build Status](https://travis-ci.org/Wikidata-lib/PropertySuggester.png?branch=master)](https://travis-ci.org/Wikidata-lib/PropertySuggester)

On [Packagist](https://packagist.org/packages/propertysuggester/propertysuggester):
[![Latest Stable Version](https://poser.pugx.org/propertysuggester/propertysuggester/v/stable.png)](https://packagist.org/packages/propertysuggester/propertysuggester)
[![License](https://poser.pugx.org/propertysuggester/propertysuggester/license.png)](https://packagist.org/packages/propertysuggester/propertysuggester)

## Installation

The recommended way to use this library is via [Composer](http://getcomposer.org/).

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `propertysuggester/propertysuggester` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
version 1.0 of this package:

    {
        "require": {
            "propertysuggester/propertysuggester": "*"
        }
    }

### Setup

This extension adds a new table "wbs_propertypairs" that contains the information that is needed to generate
suggestions. You can use [PropertySuggester-Python](https://github.com/Wikidata-lib/PropertySuggester-Python) to
generate this data from a wikidata dump.
