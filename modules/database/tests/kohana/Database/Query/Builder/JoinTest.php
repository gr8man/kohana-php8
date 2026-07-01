<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Query_Builder_Join
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query.builder
 * @group kohana.database.query.builder.join
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Query_Builder_JoinTest extends Unittest_TestCase
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

	public function test_constructor_sets_table(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$this->assertAttributeSame('users', '_table', $join);
	}

	public function test_constructor_sets_type(): void
	{
		$join = new Database_Query_Builder_Join('users', 'LEFT');
		$this->assertAttributeSame('LEFT', '_type', $join);
	}

	public function test_constructor_default_type_null(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$this->assertAttributeSame(null, '_type', $join);
	}

	public function test_on_adds_condition(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$join->on('u.id', '=', 'p.user_id');
		$this->assertAttributeContains(array('u.id', '=', 'p.user_id'), '_on', $join);
	}

	public function test_on_throws_when_using_already_called(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->expectExceptionMessage('cannot be combined');
		$join = new Database_Query_Builder_Join('users');
		$join->using('user_id');
		$join->on('u.id', '=', 'p.user_id');
	}

	public function test_using_adds_columns(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$join->using('user_id');
		$this->assertAttributeContains('user_id', '_using', $join);
	}

	public function test_using_adds_multiple_columns(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$join->using('col1', 'col2');
		$this->assertAttributeEquals(array('col1', 'col2'), '_using', $join);
	}

	public function test_using_throws_when_on_already_called(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->expectExceptionMessage('cannot be combined');
		$join = new Database_Query_Builder_Join('users');
		$join->on('u.id', '=', 'p.user_id');
		$join->using('user_id');
	}

	public function test_compile_default_join(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$sql = $join->compile();
		$this->assertSame('JOIN `users` ON ()', $sql);
	}

	public function test_compile_left_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'LEFT');
		$sql = $join->compile();
		$this->assertSame('LEFT JOIN `users` ON ()', $sql);
	}

	public function test_compile_right_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'RIGHT');
		$sql = $join->compile();
		$this->assertSame('RIGHT JOIN `users` ON ()', $sql);
	}

	public function test_compile_inner_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'INNER');
		$sql = $join->compile();
		$this->assertSame('INNER JOIN `users` ON ()', $sql);
	}

	public function test_compile_outer_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'OUTER');
		$sql = $join->compile();
		$this->assertSame('OUTER JOIN `users` ON ()', $sql);
	}

	public function test_compile_cross_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'CROSS');
		$sql = $join->compile();
		$this->assertSame('CROSS JOIN `users` ON ()', $sql);
	}

	public function test_compile_with_on_conditions(): void
	{
		$join = new Database_Query_Builder_Join('roles', 'LEFT');
		$join->on('users.role_id', '=', 'roles.id');
		$sql = $join->compile();
		$this->assertSame('LEFT JOIN `roles` ON (`users`.`role_id` = `roles`.`id`)', $sql);
	}

	public function test_compile_with_multiple_on_conditions(): void
	{
		$join = new Database_Query_Builder_Join('roles', 'INNER');
		$join->on('users.role_id', '=', 'roles.id');
		$join->on('users.active', '=', DB::expr('1'));
		$sql = $join->compile();
		$this->assertStringContainsString('AND', $sql);
		$this->assertStringContainsString('`users`.`role_id` = `roles`.`id`', $sql);
		$this->assertStringContainsString('`users`.`active` = 1', $sql);
	}

	public function test_compile_with_using_columns(): void
	{
		$join = new Database_Query_Builder_Join('profiles');
		$join->using('user_id');
		$sql = $join->compile();
		$this->assertSame('JOIN `profiles` USING (`user_id`)', $sql);
	}

	public function test_compile_with_multiple_using_columns(): void
	{
		$join = new Database_Query_Builder_Join('profiles');
		$join->using('user_id', 'type');
		$sql = $join->compile();
		$this->assertSame('JOIN `profiles` USING (`user_id`, `type`)', $sql);
	}

	public function test_on_returns_this(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$result = $join->on('a', '=', 'b');
		$this->assertSame($join, $result);
	}

	public function test_using_returns_this(): void
	{
		$join = new Database_Query_Builder_Join('users');
		$result = $join->using('col');
		$this->assertSame($join, $result);
	}

	public function test_compile_with_table_alias(): void
	{
		$join = new Database_Query_Builder_Join(array('users', 'u'), 'LEFT');
		$join->on('u.id', '=', 'p.user_id');
		$sql = $join->compile();
		$this->assertSame('LEFT JOIN `users` AS `u` ON (`u`.`id` = `p`.`user_id`)', $sql);
	}

	public function test_reset_clears_properties(): void
	{
		$join = new Database_Query_Builder_Join('users', 'LEFT');
		$join->on('a', '=', 'b');
		$join->reset();
		$this->assertAttributeSame(null, '_type', $join);
		$this->assertAttributeSame(null, '_table', $join);
		$this->assertAttributeSame(array(), '_on', $join);
	}

	public function test_compile_full_join(): void
	{
		$join = new Database_Query_Builder_Join('users', 'FULL');
		$sql = $join->compile();
		$this->assertSame('FULL JOIN `users` ON ()', $sql);
	}

	public function test_compile_only_custom_and_on_resets(): void
	{
		$join = new Database_Query_Builder_Join('users', 'LEFT');
		$sql = $join->compile();
		$this->assertSame('LEFT JOIN `users` ON ()', $sql);
	}

	public function test_using_accepts_array_of_columns(): void
	{
		$join = new Database_Query_Builder_Join('profiles');
		$join->using('user_id', 'account_id', 'type');
		$sql = $join->compile();
		$this->assertSame('JOIN `profiles` USING (`user_id`, `account_id`, `type`)', $sql);
	}

	public function test_on_with_different_operators(): void
	{
		$join = new Database_Query_Builder_Join('roles', 'LEFT');
		$join->on('users.id', '<>', 'roles.user_id');
		$sql = $join->compile();
		$this->assertStringContainsString('<>', $sql);
	}
}
