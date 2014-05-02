<?php

namespace PropertySuggester;

use WebRequest;

/**
 * Stores the suggester params
 */
class Params {

	/**
	 * @var string
	 */
	public $search;

	/**
	 * @var WebRequest
	 */
	public $request;

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
	 * @var int
	 */
	public $internalResultListSize;

}
