<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Concrete test helper for Model_Database.
 */
#[AllowDynamicProperties]
class Model_Database_TestHelper extends Model_Database
{
	public function getDb()
	{
		return $this->_db;
	}
}

/**
 * Tests for Model_Database
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.model
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Model_DatabaseTest extends Unittest_TestCase
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
				public function query(int $type, string $sql, bool $as_object = false, array $params = null) {
					return new Database_Result_Cached(array(), $sql, $as_object);
				}
				public function begin($mode = null) { return true; }
				public function commit() { return true; }
				public function rollback() { return true; }
				public function list_tables($like = null) { return array(); }
				public function list_columns($table, $like = null, $add_prefix = true) { return array(); }
				public function escape($value) { return $value; }
				public function table_prefix() { return ""; }
			}
			');
		}
	}

	public function setUp(): void
	{
		parent::setUp();
		Database::$instances['default'] = new Mock_Database_For_Test('default', array('table_prefix' => ''));
	}

	public function tearDown(): void
	{
		unset(Database::$instances['default']);
		parent::tearDown();
	}

	private function getDbProperty(\Model_Database_TestHelper $model)
	{
		return $model->getDb();
	}

	public function test_constructor_with_db_instance(): void
	{
		$db = new Mock_Database_For_Test('test', array('table_prefix' => ''));
		$model = new Model_Database_TestHelper($db);
		$this->assertSame($db, $this->getDbProperty($model));
	}

	public function test_db_property_contains_database_instance(): void
	{
		$db = new Mock_Database_For_Test('test', array('table_prefix' => ''));
		$model = new Model_Database_TestHelper($db);
		$this->assertInstanceOf(Database::class, $this->getDbProperty($model));
	}

	public function test_constructor_with_null_uses_default(): void
	{
		$model = new Model_Database_TestHelper();
		$this->assertInstanceOf(Database::class, $this->getDbProperty($model));
	}

	public function test_constructor_with_null_resolves_default_instance(): void
	{
		$expected = Database::$instances['default'];
		$model = new Model_Database_TestHelper();
		$this->assertSame($expected, $this->getDbProperty($model));
	}

	public function test_constructor_default_argument_uses_default(): void
	{
		$model = new Model_Database_TestHelper();
		$this->assertInstanceOf(Database::class, $this->getDbProperty($model));
	}

	public function test_constructor_with_string_name(): void
	{
		$db = new Mock_Database_For_Test('custom', array('table_prefix' => ''));
		Database::$instances['custom'] = $db;
		$model = new Model_Database_TestHelper('custom');
		$this->assertSame($db, $this->getDbProperty($model));
		unset(Database::$instances['custom']);
	}

	public function test_db_property_returns_same_instance(): void
	{
		$db = new Mock_Database_For_Test('test', array('table_prefix' => ''));
		$model = new Model_Database_TestHelper($db);
		$this->assertSame($db, $this->getDbProperty($model));
	}
}
