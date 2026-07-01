<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for the DB factory class
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.db
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_DBTest extends Unittest_TestCase
{
	public function test_query_returns_database_query(): void
	{
		$query = DB::query(Database::SELECT, 'SELECT * FROM users');
		$this->assertInstanceOf(Database_Query::class, $query);
	}

	public function test_query_preserves_type(): void
	{
		$select = DB::query(Database::SELECT, 'SELECT * FROM users');
		$this->assertSame(Database::SELECT, $select->type());

		$insert = DB::query(Database::INSERT, 'INSERT INTO users (id) VALUES (1)');
		$this->assertSame(Database::INSERT, $insert->type());

		$update = DB::query(Database::UPDATE, 'UPDATE users SET id = 1');
		$this->assertSame(Database::UPDATE, $update->type());

		$delete = DB::query(Database::DELETE, 'DELETE FROM users');
		$this->assertSame(Database::DELETE, $delete->type());
	}

	public function test_query_preserves_sql(): void
	{
		$sql = 'SELECT * FROM users WHERE id = 5';
		$query = DB::query(Database::SELECT, $sql);
		$this->assertSame($sql, $query->compile());
	}

	public function test_select_returns_select_builder(): void
	{
		$query = DB::select();
		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
	}

	public function test_select_with_columns(): void
	{
		$query = DB::select('id', 'username');
		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);

		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_select');
		$columns = $prop->getValue($query);
		$this->assertContains('id', $columns);
		$this->assertContains('username', $columns);
	}

	public function test_select_array_returns_select_builder(): void
	{
		$query = DB::select_array(array('id', 'name'));
		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
	}

	public function test_insert_returns_insert_builder(): void
	{
		$query = DB::insert('users');
		$this->assertInstanceOf(Database_Query_Builder_Insert::class, $query);
	}

	public function test_insert_with_table_and_columns(): void
	{
		$query = DB::insert('users', array('id', 'name'));
		$this->assertInstanceOf(Database_Query_Builder_Insert::class, $query);
	}

	public function test_update_returns_update_builder(): void
	{
		$query = DB::update('users');
		$this->assertInstanceOf(Database_Query_Builder_Update::class, $query);
	}

	public function test_delete_returns_delete_builder(): void
	{
		$query = DB::delete('users');
		$this->assertInstanceOf(Database_Query_Builder_Delete::class, $query);
	}

	public function test_expr_returns_expression(): void
	{
		$expr = DB::expr('COUNT(*)');
		$this->assertInstanceOf(Database_Expression::class, $expr);
	}

	public function test_expr_with_parameters(): void
	{
		$expr = DB::expr('COUNT(:col)', array(':col' => 'id'));
		$this->assertInstanceOf(Database_Expression::class, $expr);
		$this->assertSame('COUNT(:col)', $expr->value());
	}

	public function test_factory_methods_chain(): void
	{
		$query = DB::select('id')->from('users')->where('id', '=', 1);
		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
	}

	public function test_select_with_alias_column(): void
	{
		$query = DB::select(array('id', 'user_id'));
		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_select');
		$columns = $prop->getValue($query);

		$this->assertCount(1, $columns);
		$this->assertSame(array('id', 'user_id'), $columns[0]);
	}

	public function test_insert_defaults(): void
	{
		$query = DB::insert();
		$refl = new ReflectionClass($query);
		$tableProp = $refl->getProperty('_table');
		$columnsProp = $refl->getProperty('_columns');

		$this->assertNull($tableProp->getValue($query));
		$this->assertSame(array(), $columnsProp->getValue($query));
	}

	public function test_update_defaults(): void
	{
		$query = DB::update();
		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_table');
		$this->assertNull($prop->getValue($query));
	}

	public function test_delete_defaults(): void
	{
		$query = DB::delete();
		$refl = new ReflectionClass($query);
		$prop = $refl->getProperty('_table');
		$this->assertNull($prop->getValue($query));
	}

	public function test_select_array_with_null(): void
	{
		$query = DB::select_array();
		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
	}

	public function test_chained_db_factory(): void
	{
		$query = DB::select('u.id', 'u.name')
			->from(array('users', 'u'))
			->join(array('roles', 'r'), 'LEFT')
			->on('u.role_id', '=', 'r.id')
			->where('u.active', '=', 1)
			->order_by('u.name')
			->limit(10);

		$this->assertInstanceOf(Database_Query_Builder_Select::class, $query);
	}

	public function test_all_query_types_have_correct_base_class(): void
	{
		$this->assertInstanceOf(Database_Query_Builder::class, DB::select());
		$this->assertInstanceOf(Database_Query_Builder::class, DB::insert('t'));
		$this->assertInstanceOf(Database_Query_Builder::class, DB::update('t'));
		$this->assertInstanceOf(Database_Query_Builder::class, DB::delete('t'));
	}
}
