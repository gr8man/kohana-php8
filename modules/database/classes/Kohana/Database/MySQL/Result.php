<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database result.   See [Results](/database/results) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query/Result
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_MySQL_Result extends Database_Result
{
	public function __construct()
	{
		throw new Database_Exception('The ext/mysql extension was removed in PHP 7.0+. Use "mysqli" or "pdo" as the database type in your configuration.');
	}

	#[\Override]
	public function __destruct()
	{
	}

	#[\Override]
	public function seek(int $offset): void
	{
	}

	#[\Override]
	public function current(): mixed
	{
		return null;
	}

} // End Database_MySQL_Result_Select
