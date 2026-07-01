<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder abstract class
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Concrete test helper that exposes protected methods of Database_Query_Builder.
 */
#[AllowDynamicProperties]
class Database_Query_Builder_TestHelper extends Database_Query_Builder
{
	public function __construct()
	{
		parent::__construct(Database::SELECT, '');
	}

	#[\Override]
	public function compile($db = null): string
	{
		return '';
	}

	#[\Override]
	public function reset(): void
	{
	}

	public function public_compile_conditions(Database $db, array $conditions): string
	{
		return $this->_compile_conditions($db, $conditions);
	}

	public function public_compile_join(Database $db, array $joins): string
	{
		return $this->_compile_join($db, $joins);
	}

	public function public_compile_set(Database $db, array $values): string
	{
		return $this->_compile_set($db, $values);
	}

	public function public_compile_group_by(Database $db, array $columns): string
	{
		return $this->_compile_group_by($db, $columns);
	}

	public function public_compile_order_by(Database $db, array $columns): string
	{
		return $this->_compile_order_by($db, $columns);
	}
}

#[AllowDynamicProperties]
class Kohana_Database_Query_BuilderTest extends Unittest_TestCase
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

		$db = new Mock_Database_For_Test('default', array('table_prefix' => ''));
		Database::$instances['default'] = $db;
	}

	public static function tearDownAfterClass(): void
	{
		unset(Database::$instances['default']);
	}

	public function helper(): Database_Query_Builder_TestHelper
	{
		return new Database_Query_Builder_TestHelper();
	}

	public function db(): Database
	{
		return Database::instance();
	}

	/**
	 * _compile_conditions tests
	 */
	public function test_compile_conditions_simple_equals(): void
	{
		$conditions = array(
			array('' => array('id', '=', 5))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`id` = 5', $sql);
	}

	public function test_compile_conditions_not_equals(): void
	{
		$conditions = array(
			array('' => array('id', '!=', 5))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`id` != 5', $sql);
	}

	public function test_compile_conditions_is_null(): void
	{
		$conditions = array(
			array('' => array('col', '=', null))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`col` IS NULL', $sql);
	}

	public function test_compile_conditions_is_not_null(): void
	{
		$conditions = array(
			array('' => array('col', '!=', null))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`col` IS NOT NULL', $sql);
	}

	public function test_compile_conditions_not_null_operator_variant(): void
	{
		$conditions = array(
			array('' => array('col', '<>', null))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`col` IS NOT NULL', $sql);
	}

	public function test_compile_conditions_between(): void
	{
		$conditions = array(
			array('' => array('id', 'BETWEEN', array(1, 10)))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('`id` BETWEEN 1 AND 10', $sql);
	}

	public function test_compile_conditions_between_strings(): void
	{
		$conditions = array(
			array('' => array('name', 'BETWEEN', array('a', 'z')))
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertStringContainsString('BETWEEN', $sql);
		$this->assertStringContainsString('a', $sql);
		$this->assertStringContainsString('z', $sql);
	}

	public function test_compile_conditions_grouped(): void
	{
		$conditions = array(
			array('' => '('),
			array('AND' => array('id', '=', 1)),
			array('OR' => array('id', '=', 2)),
			array('' => ')'),
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('(`id` = 1 OR `id` = 2)', $sql);
	}

	public function test_compile_conditions_multiple_and(): void
	{
		$conditions = array(
			array('' => array('status', '=', 'active')),
			array('AND' => array('deleted', '=', 0)),
		);
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertStringContainsString('AND', $sql);
		$this->assertStringContainsString('`status`', $sql);
		$this->assertStringContainsString('`deleted` = 0', $sql);
	}

	public function test_compile_conditions_empty(): void
	{
		$conditions = array();
		$sql = $this->helper()->public_compile_conditions($this->db(), $conditions);
		$this->assertSame('', $sql);
	}

	/**
	 * _compile_join tests
	 */
	public function test_compile_join_single(): void
	{
		$join = new Database_Query_Builder_Join('users', 'LEFT');
		$join->on('u.role_id', '=', 'r.id');
		$joins = array($join);
		$sql = $this->helper()->public_compile_join($this->db(), $joins);
		$this->assertSame('LEFT JOIN `users` ON (`u`.`role_id` = `r`.`id`)', $sql);
	}

	public function test_compile_join_multiple(): void
	{
		$join1 = new Database_Query_Builder_Join('roles', 'LEFT');
		$join1->on('users.role_id', '=', 'roles.id');
		$join2 = new Database_Query_Builder_Join('profiles', 'INNER');
		$join2->on('users.id', '=', 'profiles.user_id');
		$joins = array($join1, $join2);
		$sql = $this->helper()->public_compile_join($this->db(), $joins);
		$this->assertStringContainsString('LEFT JOIN `roles`', $sql);
		$this->assertStringContainsString('INNER JOIN `profiles`', $sql);
	}

	public function test_compile_join_with_using(): void
	{
		$join = new Database_Query_Builder_Join('profiles');
		$join->using('user_id');
		$joins = array($join);
		$sql = $this->helper()->public_compile_join($this->db(), $joins);
		$this->assertSame('JOIN `profiles` USING (`user_id`)', $sql);
	}

	public function test_compile_join_empty(): void
	{
		$sql = $this->helper()->public_compile_join($this->db(), array());
		$this->assertSame('', $sql);
	}

	/**
	 * _compile_set tests
	 */
	public function test_compile_set_simple(): void
	{
		$values = array(
			array('name', 'John'),
		);
		$sql = $this->helper()->public_compile_set($this->db(), $values);
		$this->assertStringContainsString('`name`', $sql);
		$this->assertStringContainsString('John', $sql);
	}

	public function test_compile_set_multiple(): void
	{
		$values = array(
			array('name', 'Jane'),
			array('status', 'active'),
		);
		$sql = $this->helper()->public_compile_set($this->db(), $values);
		$this->assertStringContainsString('`name`', $sql);
		$this->assertStringContainsString('`status`', $sql);
		$this->assertStringContainsString(',', $sql);
	}

	public function test_compile_set_with_expression(): void
	{
		$values = array(
			array('login_count', DB::expr('login_count + 1')),
		);
		$sql = $this->helper()->public_compile_set($this->db(), $values);
		$this->assertStringContainsString('`login_count`', $sql);
		$this->assertStringContainsString('login_count + 1', $sql);
	}

	public function test_compile_set_numeric_value(): void
	{
		$values = array(
			array('priority', 1),
		);
		$sql = $this->helper()->public_compile_set($this->db(), $values);
		$this->assertStringContainsString('`priority` = 1', $sql);
	}

	public function test_compile_set_empty(): void
	{
		$sql = $this->helper()->public_compile_set($this->db(), array());
		$this->assertSame('', $sql);
	}

	/**
	 * _compile_group_by tests
	 */
	public function test_compile_group_by_single(): void
	{
		$columns = array('status');
		$sql = $this->helper()->public_compile_group_by($this->db(), $columns);
		$this->assertSame('GROUP BY `status`', $sql);
	}

	public function test_compile_group_by_multiple(): void
	{
		$columns = array('status', 'type');
		$sql = $this->helper()->public_compile_group_by($this->db(), $columns);
		$this->assertSame('GROUP BY `status`, `type`', $sql);
	}

	public function test_compile_group_by_with_alias(): void
	{
		$columns = array(array('status', 'status_alias'));
		$sql = $this->helper()->public_compile_group_by($this->db(), $columns);
		$this->assertSame('GROUP BY `status_alias`', $sql);
	}

	/**
	 * _compile_order_by tests
	 */
	public function test_compile_order_by_asc(): void
	{
		$columns = array(
			array('name', 'ASC'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name` ASC', $sql);
	}

	public function test_compile_order_by_desc(): void
	{
		$columns = array(
			array('name', 'DESC'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name` DESC', $sql);
	}

	public function test_compile_order_by_multiple(): void
	{
		$columns = array(
			array('status', 'ASC'),
			array('name', 'DESC'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertStringContainsString('`status` ASC', $sql);
		$this->assertStringContainsString('`name` DESC', $sql);
	}

	public function test_compile_order_by_defaults_to_asc(): void
	{
		$columns = array(
			array('name', null),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name`', $sql);
	}

	public function test_compile_order_by_invalid_direction_defaults_to_asc(): void
	{
		$columns = array(
			array('name', 'INVALID'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name` ASC', $sql);
	}

	public function test_compile_order_by_rand(): void
	{
		$columns = array(
			array('name', 'RAND()'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name` RAND()', $sql);
	}

	public function test_compile_order_by_random(): void
	{
		$columns = array(
			array('name', 'RANDOM()'),
		);
		$sql = $this->helper()->public_compile_order_by($this->db(), $columns);
		$this->assertSame('ORDER BY `name` RANDOM()', $sql);
	}
}
