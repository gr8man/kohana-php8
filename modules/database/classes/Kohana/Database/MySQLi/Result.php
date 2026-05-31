<?php

declare(strict_types=1); defined('SYSPATH') OR die('No direct script access.');
/**
 * MySQLi database result.   See [Results](/database/results) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query/Result
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_MySQLi_Result extends Database_Result {

	protected $_internal_row = 0;

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		parent::__construct($result, $sql, $as_object, $params);

		// Find the number of rows in the result
		$this->_total_rows = $result->num_rows;
	}

	public function __destruct()
	{
		if (is_resource($this->_result))
		{
			$this->_result->free();
		}
	}

	public function seek(int $offset): void
	{
		if ($this->offsetExists($offset) AND $this->_result->data_seek($offset))
		{
			// Set the current row to the offset
			$this->_current_row = $this->_internal_row = $offset;
		}
	}

	public function current(): mixed
	{
		return mysqli_fetch_assoc($this->_result);
	}

} // End Database_MySQLi_Result_Select
