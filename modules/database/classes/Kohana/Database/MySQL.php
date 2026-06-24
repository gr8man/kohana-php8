<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database connection.
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_MySQL extends Database
{
	protected $_identifier = '`';

	#[\Override]
	public function connect(): never
	{
		throw new Database_Exception('The ext/mysql extension was removed in PHP 7.0+. Use "mysqli" or "pdo" as the database type in your configuration.');
	}

	#[\Override]
	public function disconnect(): bool
	{
		return true;
	}

	#[\Override]
	public function set_charset($charset): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function query($type, $sql, $as_object = false, array $params = null): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function begin($mode = null): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function commit(): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function rollback(): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function list_tables($like = null): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function list_columns($table, $like = null, $add_prefix = true): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	#[\Override]
	public function escape($value): never
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

} // End Database_MySQL
