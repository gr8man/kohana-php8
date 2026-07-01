<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Exception
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.exception
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_ExceptionTest extends Unittest_TestCase
{
	public function test_constructor_stores_message(): void
	{
		$e = new Database_Exception('test error');
		$this->assertSame('test error', $e->getMessage());
	}

	public function test_constructor_stores_code(): void
	{
		$e = new Database_Exception('error', array(), 42);
		$this->assertSame(42, $e->getCode());
	}

	public function test_constructor_default_code_zero(): void
	{
		$e = new Database_Exception('error');
		$this->assertSame(0, $e->getCode());
	}

	public function test_is_instance_of_kohana_exception(): void
	{
		$e = new Database_Exception('error');
		$this->assertInstanceOf(Kohana_Exception::class, $e);
	}

	public function test_is_instance_of_exception(): void
	{
		$e = new Database_Exception('error');
		$this->assertInstanceOf(Exception::class, $e);
	}

	public function test_can_include_previous_exception(): void
	{
		$previous = new Exception('previous error');
		$e = new Database_Exception('wrapper error', array(), 0, $previous);
		$this->assertSame($previous, $e->getPrevious());
	}

	public function test_without_previous_returns_null(): void
	{
		$e = new Database_Exception('error');
		$this->assertNull($e->getPrevious());
	}

	public function test_getMessage_returns_original(): void
	{
		$message = 'Syntax error in SQL statement';
		$e = new Database_Exception($message);
		$this->assertSame($message, $e->getMessage());
	}

	public function test_to_string_works(): void
	{
		$e = new Database_Exception('error');
		$str = (string) $e;
		$this->assertStringContainsString('error', $str);
	}

	public function test_with_previous_and_code(): void
	{
		$previous = new RuntimeException('db down', 500);
		$e = new Database_Exception('query failed', array(), 1045, $previous);
		$this->assertSame('query failed', $e->getMessage());
		$this->assertSame(1045, $e->getCode());
		$this->assertSame($previous, $e->getPrevious());
	}
}
