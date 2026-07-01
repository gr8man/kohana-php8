<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder_Delete
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 * @group kohana.database.query.builder.delete
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Query_Builder_DeleteTest extends Unittest_TestCase
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

	public function test_simple_delete(): void
	{
		$query = DB::delete('users');
		$sql = $query->compile($this->_db);
		$this->assertSame('DELETE FROM `users`', $sql);
	}

	public function test_delete_with_where(): void
	{
		$query = DB::delete('users')->where('id', '=', 1);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('WHERE `id` = 1', $sql);
	}

	public function test_delete_with_multiple_conditions(): void
	{
		$query = DB::delete('users')
			->where('status', '=', 'inactive')
			->where('created_at', '<', '2020-01-01');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('AND', $sql);
	}

	public function test_delete_with_order_by(): void
	{
		$query = DB::delete('users')->where('id', '>', 100)->order_by('id', 'DESC');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ORDER BY', $sql);
	}

	public function test_delete_with_limit(): void
	{
		$query = DB::delete('users')->where('status', '=', 'spam')->limit(10);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('LIMIT 10', $sql);
	}

	public function test_delete_with_order_by_and_limit(): void
	{
		$query = DB::delete('users')
			->where('id', '>', 50)
			->order_by('id', 'ASC')
			->limit(5);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('LIMIT 5', $sql);
	}

	public function test_delete_with_or_where(): void
	{
		$query = DB::delete('users')
			->where('status', '=', 'inactive')
			->or_where('status', '=', 'banned');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('OR', $sql);
	}

	public function test_delete_grouped_where(): void
	{
		$query = DB::delete('users')
			->where_open()
			->where('age', '<', 18)
			->or_where('age', '>', 65)
			->where_close();
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('(', $sql);
		$this->assertStringContainsString(')', $sql);
	}

	public function test_delete_reset(): void
	{
		$query = DB::delete('users')->where('id', '=', 1);
		$query->reset();

		$refl = new ReflectionClass($query);
		$tableProp = $refl->getProperty('_table');
		$whereProp = $refl->getProperty('_where');
		$this->assertNull($tableProp->getValue($query));
		$this->assertSame(array(), $whereProp->getValue($query));
	}

	public function test_delete_type(): void
	{
		$query = DB::delete('users');
		$this->assertSame(Database::DELETE, $query->type());
	}

	public function test_delete_table_method(): void
	{
		$query = DB::delete()->table('users');
		$sql = $query->compile($this->_db);
		$this->assertSame('DELETE FROM `users`', $sql);
	}

	public function test_delete_where_in(): void
	{
		$query = DB::delete('users')->where('id', 'IN', array(1, 2, 3));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('IN', $sql);
	}

	public function test_delete_where_null(): void
	{
		$query = DB::delete('users')->where('deleted_at', '=', null);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('IS NULL', $sql);
	}

	public function test_delete_chainable(): void
	{
		$query = DB::delete('users');
		$this->assertSame($query, $query->table('users'));
		$this->assertSame($query, $query->where('id', '=', 1));
		$this->assertSame($query, $query->order_by('id'));
		$this->assertSame($query, $query->limit(10));
	}

	public function test_delete_no_table(): void
	{
		$query = DB::delete();
		$this->assertNull((new ReflectionClass($query))->getProperty('_table')->getValue($query));
	}
}
