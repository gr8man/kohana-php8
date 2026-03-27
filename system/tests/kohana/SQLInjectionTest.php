<?php

declare(strict_types=1);

defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Security Tests for SQL Injection Prevention (CVE-2019-8979)
 * 
 * These tests verify that the SQL injection fix in Database_Query_Builder
 * properly sanitizes the ORDER BY direction parameter.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.security
 * @group kohana.core.security.sql_injection
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_SQLInjectionTest extends Unittest_TestCase
{
	/**
	 * Tests that valid ORDER BY directions are properly compiled
	 * 
	 * @test
	 */
	public function test_order_by_valid_asc_direction()
	{
		$builder = DB::select()->from('users')->order_by('id', 'ASC');
		$this->assertStringContainsString('ORDER BY', $builder->__toString());
		$this->assertStringContainsString('ASC', strtoupper($builder->__toString()));
	}

	/**
	 * @test
	 */
	public function test_order_by_valid_desc_direction()
	{
		$builder = DB::select()->from('users')->order_by('id', 'DESC');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('DESC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_lowercase_asc()
	{
		$builder = DB::select()->from('users')->order_by('name', 'asc');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_lowercase_desc()
	{
		$builder = DB::select()->from('users')->order_by('name', 'desc');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('DESC', $sql);
	}

	/**
	 * Tests that malicious SQL injection patterns are blocked
	 * 
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_drop_table()
	{
		$builder = DB::select()->from('users')->order_by('id', 'ASC; DROP TABLE users;--');
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('DROP', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_union()
	{
		$builder = DB::select()->from('users')->order_by('id', 'ASC UNION SELECT password');
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('UNION', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_delete()
	{
		$builder = DB::select()->from('users')->order_by('id', "ASC'; DELETE FROM users;--");
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('DELETE', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_sleep()
	{
		$builder = DB::select()->from('users')->order_by('id', 'ASC AND SLEEP(5)');
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('SLEEP', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_exec()
	{
		$builder = DB::select()->from('users')->order_by('id', "ASC');EXEC('xp_cmdshell')--");
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('EXEC', $sql);
		$this->assertStringNotContainsString('xp_cmdshell', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_rejects_sql_injection_insert()
	{
		$builder = DB::select()->from('users')->order_by('id', 'ASC; INSERT INTO users VALUES (1,"hacker")');
		$sql = strtoupper($builder->__toString());
		$this->assertStringNotContainsString('INSERT', $sql);
		$this->assertStringContainsString('ASC', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_accepts_rand_function()
	{
		$builder = DB::select()->from('users')->order_by('id', 'RAND()');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('RAND()', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_accepts_random_function()
	{
		$builder = DB::select()->from('users')->order_by('id', 'RANDOM()');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('RANDOM()', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_with_array_column()
	{
		$builder = DB::select()->from('users')->order_by(array('last_name', 'first_name'), 'ASC');
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
	}

	/**
	 * @test
	 */
	public function test_order_by_multiple_columns()
	{
		$builder = DB::select()
			->from('users')
			->order_by('last_name', 'ASC')
			->order_by('first_name', 'ASC')
			->order_by('id', 'DESC');
		
		$sql = strtoupper($builder->__toString());
		$this->assertStringContainsString('ORDER BY', $sql);
		$this->assertStringContainsString('ASC', $sql);
		$this->assertStringContainsString('DESC', $sql);
	}
}
