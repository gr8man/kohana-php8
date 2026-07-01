<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder_Update
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 * @group kohana.database.query.builder.update
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Query_Builder_UpdateTest extends Unittest_TestCase
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

	public function test_simple_update(): void
	{
		$query = DB::update('users')->set(array('name' => 'John'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('UPDATE `users`', $sql);
		$this->assertStringContainsString('SET', $sql);
	}

	public function test_update_with_where(): void
	{
		$query = DB::update('users')
			->set(array('status' => 'inactive'))
			->where('last_login', '<', '2024-01-01');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('WHERE', $sql);
	}

	public function test_update_multiple_columns(): void
	{
		$query = DB::update('users')
			->set(array(
				'name' => 'Jane',
				'email' => 'jane@example.com',
				'updated_at' => DB::expr('NOW()'),
			));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`name`', $sql);
		$this->assertStringContainsString('`email`', $sql);
		$this->assertStringContainsString('NOW()', $sql);
	}

	public function test_update_value(): void
	{
		$query = DB::update('users')
			->value('name', 'Jane')
			->value('status', 'active');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`name`', $sql);
		$this->assertStringContainsString('`status`', $sql);
	}

	public function test_update_with_order_by(): void
	{
		$query = DB::update('users')
			->set(array('priority' => 1))
			->order_by('id')
			->limit(1);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('LIMIT 1', $sql);
	}

	public function test_update_reset(): void
	{
		$query = DB::update('users')->set(array('name' => 'test'))->where('id', '=', 1);
		$query->reset();
		$refl = new ReflectionClass($query);
		$tableProp = $refl->getProperty('_table');
		$setProp = $refl->getProperty('_set');
		$this->assertNull($tableProp->getValue($query));
		$this->assertSame(array(), $setProp->getValue($query));
	}

	public function test_update_type(): void
	{
		$query = DB::update('users');
		$this->assertSame(Database::UPDATE, $query->type());
	}

	public function test_update_table_method(): void
	{
		$query = DB::update()->table('users')->set(array('name' => 'test'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('UPDATE `users`', $sql);
	}

	public function test_update_with_expression_value(): void
	{
		$query = DB::update('users')
			->set(array('login_count' => DB::expr('login_count + 1')))
			->where('id', '=', 1);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('login_count + 1', $sql);
	}

	public function test_update_multiple_where_conditions(): void
	{
		$query = DB::update('users')
			->set(array('status' => 'archived'))
			->where('deleted', '=', 1)
			->where('created_at', '<', '2020-01-01');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('AND', $sql);
	}

	public function test_update_chainable(): void
	{
		$query = DB::update('users');
		$this->assertSame($query, $query->table('users'));
		$this->assertSame($query, $query->set(array('name' => 'test')));
		$this->assertSame($query, $query->value('email', 'test@test.com'));
		$this->assertSame($query, $query->where('id', '=', 1));
	}
}
