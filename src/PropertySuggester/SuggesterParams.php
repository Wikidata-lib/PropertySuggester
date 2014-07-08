<?php

namespace PropertySuggester;

/**
 * Stores the suggester params
 */
class SuggesterParams {

	/**
	 * @var string|null
	 */
	public $entity;

	/**
	 * @var string[]|null
	 */
	public $properties;

	/**
	 * @var string
	 */
	public $search;

	/**
	 * the maximum number of suggestions the suggester should return
	 * @var int
	 */
	public $suggesterLimit;

	/**
	 * @var float
	 */
	public $minProbability;

	/**
	 * @var string
	 */
	public $language;

	/**
	 * @var int
	 */
	public $limit;

	/**
	 * @var int
	 */
	public $continue;

	/**
	 * @var string
	 */
	public $context;

	/**
	 * @var int
	 */
	public $resultSize;

}
