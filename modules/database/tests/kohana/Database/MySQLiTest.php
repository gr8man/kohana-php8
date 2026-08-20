<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Database_MySQLi
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.mysqli
 * @package    Kohana/Database
 * @category   Tests
 */
class Kohana_Database_MySQLiTest extends Unittest_TestCase
{
	public function test_instantiation(): void
	{
		$config = array(
			'type'       => 'MySQLi',
			'connection' => array(
				'hostname'   => '127.0.0.1',
				'database'   => 'test_kohana',
				'username'   => 'root',
				'password'   => '',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => 'utf8mb4',
			'caching'      => false,
		);

		$db = new Database_MySQLi('mysqli_test', $config);
		$this->assertInstanceOf(Database_MySQLi::class, $db);
	}

	public function test_parse_type(): void
	{
		$config = array(
			'type'       => 'MySQLi',
			'connection' => array(
				'hostname'   => '127.0.0.1',
				'database'   => 'test_kohana',
				'username'   => 'root',
				'password'   => '',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => 'utf8mb4',
			'caching'      => false,
		);

		$db = new Database_MySQLi('mysqli_test', $config);
		$ref = new ReflectionMethod($db, '_parse_type');
		$result = $ref->invoke($db, 'varchar(255)');
		$this->assertSame(array('varchar', '255'), $result);

		$result2 = $ref->invoke($db, 'int(11) unsigned');
		$this->assertSame(array('int unsigned', '11'), $result2);

		$result3 = $ref->invoke($db, 'text');
		$this->assertSame(array('text', null), $result3);
	}

	public function test_datatype_definitions(): void
	{
		$config = array(
			'type'       => 'MySQLi',
			'connection' => array(
				'hostname'   => '127.0.0.1',
				'database'   => 'test_kohana',
				'username'   => 'root',
				'password'   => '',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => 'utf8mb4',
			'caching'      => false,
		);

		$db = new Database_MySQLi('mysqli_test', $config);
		$type_int = $db->datatype('int');
		$this->assertSame('int', $type_int['type']);

		$type_varchar = $db->datatype('varchar');
		$this->assertSame('string', $type_varchar['type']);

		$type_datetime = $db->datatype('datetime');
		$this->assertSame('string', $type_datetime['type']);

		$type_decimal = $db->datatype('decimal');
		$this->assertSame('float', $type_decimal['type']);

		$type_blob = $db->datatype('blob');
		$this->assertSame('string', $type_blob['type']);
		$this->assertTrue($type_blob['binary']);

		$type_bool = $db->datatype('bool');
		$this->assertSame('bool', $type_bool['type']);

		$type_enum = $db->datatype('enum');
		$this->assertSame('string', $type_enum['type']);

		$type_set = $db->datatype('set');
		$this->assertSame('string', $type_set['type']);

		$type_geometry = $db->datatype('geometry');
		$this->assertSame('string', $type_geometry['type']);
		$this->assertTrue($type_geometry['binary']);
	}

	public function test_quoting_identifiers_and_tables(): void
	{
		$config = array(
			'type'       => 'MySQLi',
			'connection' => array(
				'hostname'   => '127.0.0.1',
				'database'   => 'test_kohana',
				'username'   => 'root',
				'password'   => '',
				'persistent' => false,
			),
			'table_prefix' => 'prefix_',
			'charset'      => 'utf8mb4',
			'caching'      => false,
		);

		$db = new Database_MySQLi('mysqli_test', $config);
		$this->assertSame('`my_table`', $db->quote_identifier('my_table'));
		$this->assertSame('`prefix_users`', $db->quote_table('users'));
		$this->assertSame(123, $db->quote(123));
		$this->assertSame('NULL', $db->quote(null));
		$this->assertSame("'1'", $db->quote(true));
		$this->assertSame("'0'", $db->quote(false));
		$this->assertTrue($db->disconnect());
	}

	public function test_list_tables_and_columns_parsing(): void
	{
		$config = array(
			'type'       => 'MySQLi',
			'connection' => array(
				'hostname'   => '127.0.0.1',
				'database'   => 'test_kohana',
				'username'   => 'root',
				'password'   => '',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => 'utf8mb4',
			'caching'      => false,
		);

		$testable_db = new class ('test_mock_mysqli', $config) extends Database_MySQLi {
			public array $mock_rows = array();

			public function connect(): void
			{
				$this->_connection = null;
			}

			#[\Override]
			public function query($type, $sql, $as_object = false, array $params = null): array
			{
				return $this->mock_rows;
			}
		};

		// Test list_tables
		$testable_db->mock_rows = array(
			array('Tables_in_test' => 'users'),
			array('Tables_in_test' => 'roles'),
		);
		$tables = $testable_db->list_tables();
		$this->assertSame(array('users', 'roles'), $tables);

		// Test list_columns with various types
		$testable_db->mock_rows = array(
			array('Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Default' => null, 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Comment' => '', 'Privileges' => 'select'),
			array('Field' => 'name', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Default' => 'test', 'Collation' => 'utf8mb4_unicode_ci', 'Key' => '', 'Extra' => '', 'Comment' => '', 'Privileges' => 'select'),
			array('Field' => 'price', 'Type' => 'decimal(10,2)', 'Null' => 'NO', 'Default' => '0.00', 'Key' => '', 'Extra' => '', 'Comment' => '', 'Privileges' => 'select'),
			array('Field' => 'status', 'Type' => "enum('active','inactive')", 'Null' => 'NO', 'Default' => 'active', 'Collation' => 'utf8mb4_unicode_ci', 'Key' => '', 'Extra' => '', 'Comment' => '', 'Privileges' => 'select'),
			array('Field' => 'hash', 'Type' => 'binary(16)', 'Null' => 'YES', 'Default' => null, 'Key' => '', 'Extra' => '', 'Comment' => '', 'Privileges' => 'select'),
			array('Field' => 'description', 'Type' => 'text', 'Null' => 'YES', 'Default' => null, 'Collation' => 'utf8mb4_unicode_ci', 'Key' => '', 'Extra' => '', 'Comment' => '', 'Privileges' => 'select'),
		);

		$columns = $testable_db->list_columns('users');
		$this->assertIsArray($columns);
		$this->assertArrayHasKey('id', $columns);
		$this->assertSame('int', $columns['id']['type']);
		$this->assertSame('11', $columns['id']['display']);

		$this->assertArrayHasKey('name', $columns);
		$this->assertSame('string', $columns['name']['type']);
		$this->assertSame('255', $columns['name']['character_maximum_length']);

		$this->assertArrayHasKey('price', $columns);
		$this->assertSame('float', $columns['price']['type']);
		$this->assertSame('10', $columns['price']['numeric_precision']);
		$this->assertSame('2', $columns['price']['numeric_scale']);

		$this->assertArrayHasKey('status', $columns);
		$this->assertSame(array('active', 'inactive'), $columns['status']['options']);

		$this->assertArrayHasKey('hash', $columns);
		$this->assertSame('16', $columns['hash']['character_maximum_length']);
	}

	public function test_mysqli_transactions_and_escape(): void
	{
		if (! extension_loaded('mysqli')) {
			$this->markTestSkipped('mysqli extension not loaded');
		}

		$mock_mysqli = $this->getMockBuilder('mysqli')
			->disableOriginalConstructor()
			->getMock();
		$mock_mysqli->method('query')->willReturn(true);
		$mock_mysqli->method('real_escape_string')->willReturnCallback(fn ($s): string => addslashes((string) $s));
		$mock_mysqli->method('close')->willReturn(true);

		$config = array(
			'type' => 'MySQLi',
			'connection' => array('hostname' => 'localhost', 'database' => 'test', 'username' => 'root', 'password' => ''),
			'table_prefix' => '',
			'charset' => 'utf8mb4',
		);

		$db = new class ('mock_db', $config, $mock_mysqli) extends Database_MySQLi {
			public function __construct($name, array $config, $mock_conn)
			{
				parent::__construct($name, $config);
				$this->_connection = $mock_conn;
			}

			public function connect(): void
			{
			}
		};

		$this->assertTrue($db->begin());
		$this->assertTrue($db->commit());
		$this->assertTrue($db->rollback());
		$this->assertSame("'hello \'world\''", $db->escape("hello 'world'"));
		$this->assertTrue($db->disconnect());
	}

	public function test_mysqli_query_insert_update_and_select(): void
	{
		if (! extension_loaded('mysqli')) {
			$this->markTestSkipped('mysqli extension not loaded');
		}

		$mock_mysqli = new class () {
			public int $insert_id = 42;
			public int $affected_rows = 1;
			public string $error = 'Some error';
			public int $errno = 1064;
			public function query(string $sql): bool
			{
				if (str_starts_with($sql, 'ERROR')) {
					return false;
				}
				return true;
			}
		};

		$config = array(
			'type' => 'MySQLi',
			'connection' => array('hostname' => 'localhost', 'database' => 'test', 'username' => 'root', 'password' => ''),
			'table_prefix' => '',
			'charset' => 'utf8mb4',
		);

		$db = new class ('mock_db', $config, $mock_mysqli) extends Database_MySQLi {
			public function __construct($name, array $config, $mock_conn)
			{
				parent::__construct($name, $config);
				$this->_connection = $mock_conn;
			}

			public function connect(): void
			{
			}
		};

		$insert_res = $db->query(Database::INSERT, 'INSERT INTO users VALUES (42, "Alice")');
		$this->assertSame(array(42, 1), $insert_res);

		$update_res = $db->query(Database::UPDATE, 'UPDATE users SET name="Bob" WHERE id=42');
		$this->assertSame(1, $update_res);

		$dummy_res = new class () {
			public int $num_rows = 2;
			public function data_seek(int $offset): bool
			{
				return true;
			}
			public function free(): void
			{
			}
		};
		$mysqli_result = new Database_MySQLi_Result($dummy_res, 'SELECT * FROM users');
		$this->assertSame(2, $mysqli_result->count());
		$mysqli_result->seek(1);
		$this->assertSame(1, $mysqli_result->key());

		$this->expectException(Database_Exception::class);
		$db->query(Database::SELECT, 'ERROR QUERY');
	}
}
