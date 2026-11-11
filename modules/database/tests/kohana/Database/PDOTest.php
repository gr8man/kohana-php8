<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Database_PDO driver
 *
 * @package    Kohana/Database
 * @group      kohana
 * @group      kohana.database
 * @group      kohana.database.pdo
 * @category   Test
 */
class Kohana_Database_PDOTest extends Unittest_TestCase
{
	protected Database_PDO $db;

	public function setUp(): void
	{
		parent::setUp();

		if (! extension_loaded('pdo_sqlite')) {
			$this->markTestSkipped('PDO SQLite extension is not available.');
		}

		$config = array(
			'type'       => 'PDO',
			'connection' => array(
				'dsn'        => 'sqlite::memory:',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => null,
			'caching'      => false,
		);

		$this->db = new Database_PDO('pdo_test', $config);
		$this->db->connect();
	}

	public function tearDown(): void
	{
		$this->db->disconnect();
		if (isset(Database::$instances['test_instance_pdo'])) {
			Database::$instances['test_instance_pdo']->disconnect();
			unset(Database::$instances['test_instance_pdo']);
		}
		if (isset(Database::$instances['test_null_cfg'])) {
			Database::$instances['test_null_cfg']->disconnect();
			unset(Database::$instances['test_null_cfg']);
		}
		if (isset(Database::$instances['test_missing_type'])) {
			Database::$instances['test_missing_type']->disconnect();
			unset(Database::$instances['test_missing_type']);
		}
		$db_config = Kohana::$config->load('database');
		if (isset($db_config['test_null_cfg'])) {
			unset($db_config['test_null_cfg']);
		}
		if (isset($db_config['test_missing_type'])) {
			unset($db_config['test_missing_type']);
		}
		parent::tearDown();
	}

	public function test_connect_and_disconnect(): void
	{
		$this->db->connect();
		$this->assertTrue(true);

		$this->db->disconnect();
		$this->assertTrue(true);
	}

	public function test_crud_queries(): void
	{
		$this->db->query(null, 'CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, email TEXT)');

		// Insert
		$res_insert = $this->db->query(Database::INSERT, "INSERT INTO users (username, email) VALUES ('john', 'john@example.com')");
		$this->assertIsArray($res_insert);
		$this->assertEquals(1, $res_insert[0]); // insert id
		$this->assertSame(1, $res_insert[1]); // total rows affected

		// Select
		$res_select = $this->db->query(Database::SELECT, 'SELECT * FROM users WHERE username = "john"', false);
		$this->assertCount(1, $res_select);
		$row = $res_select->current();
		$this->assertSame('john', $row['username']);
		$this->assertSame('john@example.com', $row['email']);

		// Select as object
		$res_obj = $this->db->query(Database::SELECT, 'SELECT * FROM users WHERE username = "john"', true);
		$obj = $res_obj->current();
		$this->assertIsObject($obj);
		$this->assertSame('john', $obj->username);

		// Update
		$res_update = $this->db->query(Database::UPDATE, "UPDATE users SET email = 'john_updated@example.com' WHERE username = 'john'");
		$this->assertSame(1, $res_update);

		// Delete
		$res_delete = $this->db->query(Database::DELETE, "DELETE FROM users WHERE username = 'john'");
		$this->assertSame(1, $res_delete);
	}

	public function test_transactions(): void
	{
		$this->db->query(null, 'CREATE TABLE accounts (id INTEGER PRIMARY KEY, balance INT)');

		$this->assertTrue($this->db->begin());
		$this->db->query(Database::INSERT, 'INSERT INTO accounts (id, balance) VALUES (1, 100)');
		$this->assertTrue($this->db->commit());

		$res = $this->db->query(Database::SELECT, 'SELECT * FROM accounts WHERE id = 1', false);
		$this->assertCount(1, $res);

		$this->assertTrue($this->db->begin());
		$this->db->query(Database::INSERT, 'INSERT INTO accounts (id, balance) VALUES (2, 200)');
		$this->assertTrue($this->db->rollback());

		$res2 = $this->db->query(Database::SELECT, 'SELECT * FROM accounts WHERE id = 2', false);
		$this->assertCount(0, $res2);
	}

	public function test_escape(): void
	{
		$escaped = $this->db->escape("hello 'world'");
		$this->assertSame("'hello ''world'''", $escaped);
	}

	public function test_unsupported_methods_throw_exceptions(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->db->list_tables();
	}

	public function test_list_columns_throws_exception(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->db->list_columns('users');
	}

	public function test_create_function_and_aggregate(): void
	{
		$this->assertTrue($this->db->create_function('custom_upper', 'strtoupper'));
		$res = $this->db->query(Database::SELECT, "SELECT custom_upper('hello') AS res", false);
		$this->assertSame('HELLO', $res->get('res'));

		$step = function (&$context, $row, $value): float|int|array {
			$context += $value;
			return $context;
		};
		$final = (fn (&$context, $row) => $context);
		$this->assertTrue($this->db->create_aggregate('custom_sum', $step, $final));
	}

	public function test_instance_with_explicit_config(): void
	{
		$config = array(
			'type'       => 'PDO',
			'connection' => array(
				'dsn'        => 'sqlite::memory:',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => null,
			'caching'      => false,
		);

		$instance = Database::instance('test_instance_pdo', $config);
		$this->assertInstanceOf(Database_PDO::class, $instance);
		$this->assertSame($instance, Database::instance('test_instance_pdo'));
	}

	public function test_instance_with_null_config_loads_from_kohana_config(): void
	{
		Kohana::$config->load('database')->set('test_null_cfg', array(
			'type'       => 'PDO',
			'connection' => array(
				'dsn'        => 'sqlite::memory:',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => null,
			'caching'      => false,
		));

		// Both omitting config and passing null config should trigger loading from Kohana::$config
		$config = null;
		$instance = Database::instance('test_null_cfg', $config);
		$this->assertInstanceOf(Database_PDO::class, $instance);
		$this->assertSame($instance, Database::instance('test_null_cfg'));
	}

	public function test_instance_with_null_config_throws_exception_when_type_not_defined(): void
	{
		Kohana::$config->load('database')->set('test_missing_type', array(
			'table_prefix' => '',
		));

		$this->expectException(Kohana_Exception::class);
		$this->expectExceptionMessage('Database type not defined in test_missing_type configuration');

		$config = null;
		Database::instance('test_missing_type', $config);
	}
}
