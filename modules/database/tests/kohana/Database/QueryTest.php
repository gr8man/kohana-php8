<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_QueryTest extends Unittest_TestCase
{
	public static function setUpBeforeClass(): void
	{
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
				public function escape($value) { return "\'" . $value . "\'"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		$mock = new Mock_Database_For_Test('default', array('table_prefix' => ''));
		Database::$instances['default'] = $mock;
	}

	public static function tearDownAfterClass(): void
	{
		unset(Database::$instances['default']);
	}

	public function test_create_select_query(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$this->assertSame(Database::SELECT, $query->type());
		$this->assertSame('SELECT * FROM users', $query->compile());
	}

	public function test_create_insert_query(): void
	{
		$query = new Database_Query(Database::INSERT, 'INSERT INTO users (id) VALUES (1)');
		$this->assertSame(Database::INSERT, $query->type());
	}

	public function test_create_update_query(): void
	{
		$query = new Database_Query(Database::UPDATE, 'UPDATE users SET name = "test"');
		$this->assertSame(Database::UPDATE, $query->type());
	}

	public function test_create_delete_query(): void
	{
		$query = new Database_Query(Database::DELETE, 'DELETE FROM users');
		$this->assertSame(Database::DELETE, $query->type());
	}

	public function test_param_sets_parameter(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users WHERE id = :id');
		$query->param(':id', 5);

		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_parameters');
		$params = $prop->getValue($query);
		$this->assertArrayHasKey(':id', $params);
		$this->assertSame(5, $params[':id']);
	}

	public function test_params_are_replaced_in_compile(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users WHERE id = :id');
		$query->param(':id', 5);
		$compiled = $query->compile();

		$this->assertStringContainsString('5', $compiled);
		$this->assertStringNotContainsString(':id', $compiled);
	}

	public function test_bind_parameter(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users WHERE id = :id');
		$id = 10;
		$query->bind(':id', $id);

		$compiled = $query->compile();
		$this->assertStringContainsString('10', $compiled);

		$compiled2 = $query->compile();
		$this->assertStringContainsString('10', $compiled2);
	}

	public function test_parameters_merges_multiple(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users WHERE id = :id');
		$query->param(':id', 1);
		$query->parameters(array(':name' => 'John'));

		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_parameters');
		$params = $prop->getValue($query);

		$this->assertArrayHasKey(':id', $params);
		$this->assertArrayHasKey(':name', $params);
	}

	public function test_as_assoc_returns_self(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$result = $query->as_assoc();
		$this->assertSame($query, $result);
	}

	public function test_as_object_returns_self(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$result = $query->as_object();
		$this->assertSame($query, $result);
	}

	public function test_as_object_with_class(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$returned = $query->as_object('stdClass');
		$this->assertSame($query, $returned);
	}

	public function test_cached_returns_self(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$result = $query->cached(3600);
		$this->assertSame($query, $result);
	}

	public function test_cached_with_default_lifetime(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$returned = $query->cached();
		$this->assertSame($query, $returned);
	}

	public function test_cached_with_force_execute(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');
		$returned = $query->cached(3600, true);
		$this->assertSame($query, $returned);
	}

	public function test_to_string_returns_compiled_sql(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT 1');
		$str = (string) $query;
		$this->assertSame('SELECT 1', $str);
	}

	public function test_chainable_methods(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT * FROM users');

		$this->assertSame($query, $query->param(':id', 1));
		$this->assertSame($query, $query->parameters(array(':name' => 'test')));
		$this->assertSame($query, $query->cached(60));
		$this->assertSame($query, $query->as_assoc());
		$this->assertSame($query, $query->as_object());
	}

	public function test_compile_with_multiple_params(): void
	{
		$query = new Database_Query(
			Database::SELECT,
			'SELECT * FROM users WHERE id = :id AND status = :status'
		);
		$query->parameters(array(':id' => 5, ':status' => 'active'));

		$compiled = $query->compile();
		$this->assertStringNotContainsString(':id', $compiled);
		$this->assertStringNotContainsString(':status', $compiled);
	}

	public function test_implements_stringable(): void
	{
		$query = new Database_Query(Database::SELECT, 'SELECT 1');
		$this->assertInstanceOf(Stringable::class, $query);
	}

	public function test_type_returns_correct_value(): void
	{
		$query = new Database_Query(Database::SELECT, '');
		$this->assertSame(Database::SELECT, $query->type());

		$query = new Database_Query(Database::INSERT, '');
		$this->assertSame(Database::INSERT, $query->type());
	}
}
