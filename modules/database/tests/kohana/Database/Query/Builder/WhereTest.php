<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Database_Query_Builder_Where
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.query
 * @group kohana.database.query.builder
 * @package    Kohana/Database
 * @category   Tests
 */
class Kohana_Database_Query_Builder_WhereTest extends Unittest_TestCase
{
	public function test_where_conditions_and_groups(): void
	{
		$db = new Database_PDO('pdo_sqlite', array(
			'type' => 'PDO',
			'connection' => array('dsn' => 'sqlite::memory:'),
		));

		$builder = DB::select()->from('users')
			->where('status', '=', 'active')
			->and_where('role', '=', 'admin')
			->or_where('id', '=', 1)
			->where_open()
			->where('email', 'LIKE', '%@example.com')
			->where_close()
			->order_by('id', 'DESC')
			->limit(10);

		$sql = $builder->compile($db);
		$this->assertStringContainsString("WHERE status = 'active'", $sql);
		$this->assertStringContainsString("AND role = 'admin'", $sql);
		$this->assertStringContainsString("OR id = 1", $sql);
		$this->assertStringContainsString("ORDER BY id DESC", $sql);
		$this->assertStringContainsString("LIMIT 10", $sql);
	}

	public function test_where_close_empty(): void
	{
		$builder = DB::select()->from('users');
		$builder->where_open();
		$builder->where_close_empty();

		$ref = new ReflectionProperty($builder, '_where');
		$this->assertEmpty($ref->getValue($builder));
	}

	public function test_or_where_open_and_close(): void
	{
		$db = new Database_PDO('pdo_sqlite', array(
			'type' => 'PDO',
			'connection' => array('dsn' => 'sqlite::memory:'),
		));

		$builder = DB::select()->from('users')
			->where('id', '>', 0)
			->or_where_open()
			->where('age', '>', 18)
			->or_where_close();

		$sql = $builder->compile($db);
		$this->assertStringContainsString('OR (age > 18)', $sql);
	}
}
