<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * Object used for caching the results of select queries.  See [Results](/database/results#select-cached) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query/Result
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_Result_Cached extends Database_Result
{
	public function __construct(array $result, $sql, $as_object = null)
	{
		parent::__construct($result, $sql, $as_object);

		// Find the number of rows in the result
		$this->_total_rows = count($result);
	}

	#[\Override]
	public function __destruct()
	{
		// Cached results do not use resources
	}

	#[\Override]
	public function cached(): static
	{
		return $this;
	}

	#[\Override]
	public function seek(int $offset): void
	{
		if ($this->offsetExists($offset)) {
			$this->_current_row = $offset;
		}
	}

	#[\Override]
	public function current(): mixed
	{
		return $this->_result[$this->_current_row];
	}

} // End Database_Result_Cached
