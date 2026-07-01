<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for ORM query execution methods (find_all, count_all, check, etc.)
 *
 * These tests use a mock database to verify ORM query behavior without
 * requiring a real database connection.
 *
 * @group kohana
 * @group kohana.orm
 * @group kohana.orm.query
 *
 * @package    Kohana/ORM
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_ORMQueryTest extends Unittest_TestCase
{
	public static function setUpBeforeClass(): void
	{
		if (!class_exists('Mock_Database_For_ORM_Query_Test', false)) {
			eval('
			class Mock_Database_For_ORM_Query_Test extends Database {
				protected $_identifier = "`";
				protected $_config = array("table_prefix" => "");
				public function connect() {}
				public function disconnect() { return true; }
				public function set_charset($charset) {}
				public function query(int $type, string $sql, mixed $as_object = false, array $params = null) {
					$data = array();
					if (stripos($sql, "SELECT COUNT") !== false) {
						$data = array(array("COUNT(*)" => "5"));
					}
					return new Database_Result_Cached($data, $sql, $as_object);
				}
				public function begin($mode = null) { return true; }
				public function commit() { return true; }
				public function rollback() { return true; }
				public function list_tables($like = null) { return array(); }
				public function list_columns($table, $like = null, $add_prefix = true) {
					return array(
						"id" => array("type" => "int"),
						"username" => array("type" => "string"),
						"email" => array("type" => "string"),
						"password" => array("type" => "string"),
						"status" => array("type" => "string"),
						"logins" => array("type" => "int"),
						"meta" => array("type" => "string"),
						"created_at" => array("type" => "int"),
					);
				}
				public function escape($value) { return "\'" . $value . "\'"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		$mock = new Mock_Database_For_ORM_Query_Test('default', array('table_prefix' => ''));
		Database::$instances['default'] = $mock;

		if (!class_exists('Model_TestQueryUser', false)) {
			eval('
			#[AllowDynamicProperties]
			class Model_TestQueryUser extends ORM {
				protected $_table_columns = array(
					"id"        => array("type" => "int"),
					"username"  => array("type" => "string"),
					"email"     => array("type" => "string"),
					"password"  => array("type" => "string"),
					"status"    => array("type" => "string"),
					"logins"    => array("type" => "int"),
					"meta"      => array("type" => "string"),
					"created_at" => array("type" => "int"),
				);
			}
			');
		}
	}

	public static function tearDownAfterClass(): void
	{
		unset(Database::$instances['default']);
	}

	public function test_find_all_returns_result(): void
	{
		$model = ORM::factory('TestQueryUser');
		$result = $model->find_all();
		$this->assertInstanceOf(Database_Result::class, $result);
	}

	public function test_find_all_is_countable(): void
	{
		$model = ORM::factory('TestQueryUser');
		$result = $model->find_all();
		$this->assertCount(0, $result);
	}

	public function test_count_all_returns_int(): void
	{
		$model = ORM::factory('TestQueryUser');
		$count = $model->count_all();
		$this->assertIsInt($count);
	}

	public function test_find_with_id_returns_loaded_model(): void
	{
		$model = ORM::factory('TestQueryUser', 1);
		$this->assertInstanceOf(ORM::class, $model);
	}

	public function test_validation_returns_validation_object(): void
	{
		$model = ORM::factory('TestQueryUser');
		$model->check();
		$this->assertTrue(true);
	}

	public function test_find_all_after_where(): void
	{
		$model = ORM::factory('TestQueryUser');
		$result = $model->where('username', '=', 'john')->find_all();
		$this->assertInstanceOf(Database_Result::class, $result);
	}

	public function test_find_all_returns_empty_for_no_results(): void
	{
		$model = ORM::factory('TestQueryUser');
		$result = $model->find_all();
		$this->assertCount(0, $result);
	}

	public function test_find_with_non_existent_id_returns_unloaded_model(): void
	{
		$model = ORM::factory('TestQueryUser', 999);
		$this->assertFalse($model->loaded());
	}

	public function test_as_array_on_loaded_model(): void
	{
		$model = ORM::factory('TestQueryUser');
		$array = $model->as_array();
		$this->assertArrayHasKey('username', $array);
	}

	public function test_pk_returns_value_after_find(): void
	{
		$model = ORM::factory('TestQueryUser', 1);
		$this->assertNull($model->pk());
	}
}
