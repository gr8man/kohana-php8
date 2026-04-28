<?php

declare(strict_types=1); defined('SYSPATH') OR die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Validation_Exception extends Kohana_Exception {

	/**
	 * @param  Validation      $array      Validation object
	 * @param  string          $message    error message
	 * @param  array|null      $values     translation variables
	 * @param  integer|string  $code       the exception code
	 * @param  Throwable|null  $previous   previous exception
	 */
	public function __construct(
		public Validation $array,
		string $message = 'Failed to validate array',
		array $values = NULL,
		$code = 0,
		Throwable $previous = NULL
	) {
		parent::__construct($message, $values, $code, $previous);
	}

}
