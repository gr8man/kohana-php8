<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder_Select
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 * @group kohana.database.query.builder.select
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Query_Builder_SelectTest extends Unittest_TestCase
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
				public function query(int $type, string $sql, bool $as_object = false, array $params = null) {
					throw new RuntimeException("Unexpected query call: $sql");
				}
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

	public function test_simple_select(): void
	{
		$query = DB::select()->from('users');
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT * FROM `users`', $sql);
	}

	public function test_select_columns(): void
	{
		$query = DB::select('id', 'username')->from('users');
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT `id`, `username` FROM `users`', $sql);
	}

	public function test_select_with_alias(): void
	{
		$query = DB::select(array('id', 'user_id'))->from('users');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`id` AS `user_id`', $sql);
	}

	public function test_select_distinct(): void
	{
		$query = DB::select('city')->from('users')->distinct(true);
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT DISTINCT `city` FROM `users`', $sql);
	}

	public function test_select_from_multiple_tables(): void
	{
		$query = DB::select('u.id', 'r.name')
			->from(array('users', 'u'), array('roles', 'r'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`users` AS `u`', $sql);
		$this->assertStringContainsString('`roles` AS `r`', $sql);
	}

	public function test_select_where(): void
	{
		$query = DB::select()->from('users')->where('id', '=', 5);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString("WHERE `id` = 5", $sql);
	}

	public function test_select_where_string_value(): void
	{
		$query = DB::select()->from('users')->where('username', '=', 'john');
		$sql = $query->compile($this->_db);

		if ($this->_db instanceof Mock_Database_For_Test) {
			$escaped = $this->_db->escape('john');
			$this->assertStringContainsString("WHERE `username` = $escaped", $sql);
		}
	}

	public function test_select_multiple_where(): void
	{
		$query = DB::select()->from('users')
			->where('id', '=', 1)
			->where('status', '=', 'active');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`id` = 1', $sql);
		$this->assertStringContainsString('`status`', $sql);
	}

	public function test_select_or_where(): void
	{
		$query = DB::select()->from('users')
			->where('role', '=', 'admin')
			->or_where('role', '=', 'superadmin');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('OR', $sql);
	}

	public function test_select_where_in(): void
	{
		$query = DB::select()->from('users')->where('id', 'IN', array(1, 2, 3));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('IN', $sql);
		$this->assertStringContainsString('1', $sql);
		$this->assertStringContainsString('2', $sql);
	}

	public function test_select_where_null(): void
	{
		$query = DB::select()->from('users')->where('deleted_at', '=', null);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('IS NULL', $sql);
	}

	public function test_select_where_not_null(): void
	{
		$query = DB::select()->from('users')->where('deleted_at', '!=', null);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('IS NOT NULL', $sql);
	}

	public function test_select_where_between(): void
	{
		$query = DB::select()->from('users')->where('id', 'BETWEEN', array(10, 20));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('BETWEEN', $sql);
		$this->assertStringContainsString('10', $sql);
		$this->assertStringContainsString('20', $sql);
	}

	public function test_select_where_grouped(): void
	{
		$query = DB::select()->from('users')
			->where_open()
			->where('id', '=', 1)
			->or_where('id', '=', 2)
			->where_close();
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('(', $sql);
		$this->assertStringContainsString(')', $sql);
	}

	public function test_select_order_by(): void
	{
		$query = DB::select()->from('users')->order_by('username');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ORDER BY', $sql);
	}

	public function test_select_order_by_desc(): void
	{
		$query = DB::select()->from('users')->order_by('id', 'DESC');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ORDER BY `id` DESC', $sql);
	}

	public function test_select_order_by_multiple(): void
	{
		$query = DB::select()->from('users')
			->order_by('status', 'ASC')
			->order_by('name', 'DESC');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ASC', $sql);
		$this->assertStringContainsString('DESC', $sql);
	}

	public function test_select_limit(): void
	{
		$query = DB::select()->from('users')->limit(10);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('LIMIT 10', $sql);
	}

	public function test_select_limit_offset(): void
	{
		$query = DB::select()->from('users')->limit(10)->offset(20);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('LIMIT 10', $sql);
		$this->assertStringContainsString('OFFSET 20', $sql);
	}

	public function test_select_group_by(): void
	{
		$query = DB::select()->from('users')->group_by('status');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('GROUP BY', $sql);
	}

	public function test_select_having(): void
	{
		$query = DB::select('status', DB::expr('COUNT(*) as cnt'))
			->from('users')
			->group_by('status')
			->having('cnt', '>', 1);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('HAVING', $sql);
	}

	public function test_select_join(): void
	{
		$query = DB::select()->from('users')
			->join('roles', 'LEFT')
			->on('users.role_id', '=', 'roles.id');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('JOIN', $sql);
		$this->assertStringContainsString('LEFT', $sql);
	}

	public function test_select_using(): void
	{
		$query = DB::select()->from('users')
			->join('profiles')
			->using('user_id');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('USING', $sql);
	}

	public function test_select_union(): void
	{
		$query = DB::select()->from('users')
			->union(DB::select()->from('archived_users'));
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('UNION', $sql);
	}

	public function test_select_expression_in_columns(): void
	{
		$query = DB::select(DB::expr('COUNT(*) as total'))->from('users');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('COUNT(*) as total', $sql);
	}

	public function test_select_with_table_alias(): void
	{
		$query = DB::select('u.id', 'u.name')
			->from(array('users', 'u'))
			->where('u.active', '=', 1);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('`users` AS `u`', $sql);
	}

	public function test_reset_select(): void
	{
		$query = DB::select('id', 'name')->from('users')->where('id', '=', 1);
		$query->reset();
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT *', $sql);
	}

	public function test_select_array_method(): void
	{
		$query = DB::select()->select_array(array('id', 'name'))->from('users');
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT `id`, `name` FROM `users`', $sql);
	}

	public function test_order_by_security_valid_directions(): void
	{
		$query = DB::select()->from('users')->order_by('name', 'ASC');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ASC', $sql);

		$query = DB::select()->from('users')->order_by('name', 'DESC');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('DESC', $sql);

		$query = DB::select()->from('users')->order_by('name', 'RAND()');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('RAND()', $sql);
	}

	public function test_order_by_invalid_direction_defaults_to_asc(): void
	{
		$query = DB::select()->from('users')->order_by('name', 'INVALID');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('ASC', $sql);
	}

	public function test_where_close_empty_removes_empty_group(): void
	{
		$query = DB::select()->from('users');
		$query->where_open();
		$query->where_close_empty();
		$sql = $query->compile($this->_db);
		$this->assertStringNotContainsString('(', $sql);
	}

	public function test_where_close_empty_keeps_non_empty_group(): void
	{
		$query = DB::select()->from('users')
			->where_open()
			->where('id', '=', 1)
			->where_close_empty();
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('(', $sql);
		$this->assertStringContainsString(')', $sql);
	}

	public function test_select_empty_from(): void
	{
		$query = DB::select('1');
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT `1`', $sql);
	}

	public function test_from_resets_previous_from(): void
	{
		$query = DB::select()->from('users')->from('roles');
		$sql = $query->compile($this->_db);
		$this->assertSame('SELECT * FROM `users`, `roles`', $sql);
	}

	public function test_join_without_type_defaults_to_join(): void
	{
		$query = DB::select()->from('users')->join('roles')->on('users.id', '=', 'roles.user_id');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('JOIN `roles`', $sql);
		$this->assertStringNotContainsString('LEFT', $sql);
		$this->assertStringNotContainsString('RIGHT', $sql);
	}

	public function test_multiple_joins(): void
	{
		$query = DB::select()->from('users')
			->join('roles', 'LEFT')->on('users.role_id', '=', 'roles.id')
			->join('profiles', 'INNER')->on('users.id', '=', 'profiles.user_id');
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('LEFT JOIN', $sql);
		$this->assertStringContainsString('INNER JOIN', $sql);
	}

	public function test_and_having(): void
	{
		$query = DB::select('status', DB::expr('COUNT(*) as cnt'))
			->from('users')
			->group_by('status')
			->and_having('cnt', '>', 5);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('HAVING', $sql);
	}

	public function test_or_having(): void
	{
		$query = DB::select('status', DB::expr('COUNT(*) as cnt'))
			->from('users')
			->group_by('status')
			->having('cnt', '>', 10)
			->or_having('cnt', '<', 2);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('OR', $sql);
	}

	public function test_having_grouping(): void
	{
		$query = DB::select('status', DB::expr('COUNT(*) as cnt'))
			->from('users')
			->group_by('status')
			->having_open()
			->having('cnt', '>', 5)
			->or_having('cnt', '=', 0)
			->having_close();
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('(', $sql);
		$this->assertStringContainsString(')', $sql);
	}

	public function test_union_all(): void
	{
		$subquery = DB::select()->from('deleted_users');
		$query = DB::select()->from('users')->union($subquery, true);
		$sql = $query->compile($this->_db);
		$this->assertStringContainsString('UNION ALL', $sql);
	}
}
