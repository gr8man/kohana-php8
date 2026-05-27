<?php

declare(strict_types=1); defined('SYSPATH') OR die('No direct script access.');
/**
 * MySQL database connection.
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_MySQL extends Database {

	protected $_identifier = '`';

	public function connect()
	{
		throw new Database_Exception('The ext/mysql extension was removed in PHP 7.0+. Use "mysqli" or "pdo" as the database type in your configuration.');
	}

	public function disconnect()
	{
		return TRUE;
	}

	public function set_charset($charset)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function begin($mode = NULL)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function commit()
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function rollback()
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function list_tables($like = NULL)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

	public function escape($value)
	{
		throw new Database_Exception('The ext/mysql driver is not available.');
	}

} // End Database_MySQL
