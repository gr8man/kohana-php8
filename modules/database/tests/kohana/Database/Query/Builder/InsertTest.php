<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder_Insert
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 * @group kohana.database.query.builder.insert
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Query_Builder_InsertTest extends Unittest_TestCase
{
	/**
	 * @var Database
	 */
	protected $_db;

	public function setUp(): void
	{
		parent::setUp();

		if (!class_exists('Mock_Database_For_Test', false)) {
			eval('
			class Mock_Database_For_Test extends Database {
				protected $_identifier = "`";
				protected $_config = array("table_prefix" => "");

				public function connect() {}
				public function disconnect() { return true; }
				public function set_charset($charset) {}
				public function query(int $type, string $sql, bool $as_object = false, array $params = null) {}
				public function begin($mode = null) { return true; }
				public function commit() { return true; }
				public function rollback() { return true; }
				public function list_tables($like = null) { return array(); }
				public function list_columns($table, $like = null, $add_prefix = true) { return array(); }
				public function escape($value) { return "`" . $value . "`"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		$this->_db = new Mock_Database_For_Test('test', array('table_prefix' => ''));
	}

	public function test_simple_insert(): void
	{
		$query = DB::insert('users', array('id', 'name'))
			->values(array(1, 'John'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('INSERT INTO `users`', $sql);
		$this->assertStringContainsString('`id`', $sql);
		$this->assertStringContainsString('`name`', $sql);
	}

	public function test_insert_with_values(): void
	{
		$query = DB::insert('users', array('username', 'email'))
			->values(array('john', 'john@example.com'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('VALUES', $sql);
	}

	public function test_insert_multiple_rows(): void
	{
		$query = DB::insert('users', array('name'))
			->values(array('John'))
			->values(array('Jane'))
			->values(array('Bob'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('VALUES', $sql);
	}

	public function test_insert_with_expression_value(): void
	{
		$query = DB::insert('users', array('created_at'))
			->values(array(DB::expr('NOW()')));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('NOW()', $sql);
	}

	public function test_insert_reset(): void
	{
		$query = DB::insert('users', array('name'))->values(array('test'));
		$query->reset();
		$refl = new ReflectionClass($query);
		$tableProp = $refl->getProperty('_table');
		$this->assertNull($tableProp->getValue($query));
	}

	public function test_insert_type(): void
	{
		$query = DB::insert('users');
		$this->assertSame(Database::INSERT, $query->type());
	}

	public function test_insert_table_method(): void
	{
		$query = DB::insert()->table('users');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('INSERT INTO `users`', $sql);
	}

	public function test_insert_table_with_alias_throws(): void
	{
		$this->expectException(Kohana_Exception::class);
		$query = DB::insert()->table(array('users', 'u'));
		$query->compile($this->_db);
	}

	public function test_insert_columns_method(): void
	{
		$query = DB::insert('users')->columns(array('id', 'name'))->values(array(1, 'test'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('(`id`, `name`)', $sql);
	}

	public function test_insert_chainable(): void
	{
		$query = DB::insert('users', array('name'));
		$this->assertSame($query, $query->table('users'));
		$this->assertSame($query, $query->columns(array('name')));
		$this->assertSame($query, $query->values(array('test')));
	}
}
