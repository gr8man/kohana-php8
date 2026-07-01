<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Database_Expression
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.expression
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_ExpressionTest extends Unittest_TestCase
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
				public function query(int $type, string $sql, bool $as_object = false, array $params = null) {}
				public function begin($mode = null) { return true; }
				public function commit() { return true; }
				public function rollback() { return true; }
				public function list_tables($like = null) { return array(); }
				public function list_columns($table, $like = null, $add_prefix = true) { return array(); }
				public function escape($value) { return "\'" . $value . "\'"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		$mock = new Mock_Database_For_Test('default', array('table_prefix' => ''));
		Database::$instances['default'] = $mock;
	}

	public static function tearDownAfterClass(): void
	{
		unset(Database::$instances['default']);
	}

	public function test_create_expression(): void
	{
		$expr = new Database_Expression('COUNT(*)');
		$this->assertInstanceOf(Database_Expression::class, $expr);
		$this->assertSame('COUNT(*)', $expr->value());
	}

	public function test_create_expression_with_parameters(): void
	{
		$expr = new Database_Expression('COUNT(:column)', array(':column' => 'id'));
		$this->assertSame('COUNT(:column)', $expr->value());
	}

	public function test_value_returns_raw_string(): void
	{
		$expr = new Database_Expression('NOW()');
		$this->assertSame('NOW()', $expr->value());
	}

	public function test_to_string_returns_value(): void
	{
		$expr = new Database_Expression('RANDOM()');
		$this->assertSame('RANDOM()', (string) $expr);
	}

	public function test_implements_stringable(): void
	{
		$expr = new Database_Expression('1 + 1');
		$this->assertInstanceOf(Stringable::class, $expr);
	}

	public function test_param_sets_single_parameter(): void
	{
		$expr = new Database_Expression(':name');
		$expr->param(':name', 'value');
		$this->assertSame(':name', $expr->value());
	}

	public function test_parameters_merges_multiple_params(): void
	{
		$expr = new Database_Expression(':a + :b');
		$expr->parameters(array(':a' => 1, ':b' => 2));
		$this->assertSame(':a + :b', $expr->value());
	}

	public function test_bind_parameter_by_reference(): void
	{
		$expr = new Database_Expression(':var');
		$value = 'hello';
		$expr->bind(':var', $value);

		$refl = new ReflectionClass($expr);
		$prop = $refl->getProperty('_parameters');
		$params = $prop->getValue($expr);
		$this->assertArrayHasKey(':var', $params);
	}

	public function test_chainable_methods(): void
	{
		$expr = new Database_Expression(':col');
		$result = $expr->param(':col', 'id');
		$this->assertSame($expr, $result);

		$result = $expr->parameters(array(':col' => 'name'));
		$this->assertSame($expr, $result);
	}

	public function test_compile_without_db_uses_default_instance(): void
	{
		$expr = new Database_Expression('NOW()');
		$compiled = $expr->compile();
		$this->assertSame('NOW()', $compiled);
	}

	public function test_compile_with_parameters(): void
	{
		$expr = new Database_Expression(':min + :max');
		$expr->parameters(array(':min' => 10, ':max' => 20));

		$compiled = $expr->compile();
		$this->assertStringNotContainsString(':min', $compiled);
		$this->assertStringNotContainsString(':max', $compiled);
	}

	public function test_empty_expression(): void
	{
		$expr = new Database_Expression('');
		$this->assertSame('', $expr->value());
		$this->assertSame('', (string) $expr);
	}

	public function test_sql_function_expression(): void
	{
		$expr = new Database_Expression('CONCAT(first_name, " ", last_name)');
		$this->assertSame('CONCAT(first_name, " ", last_name)', $expr->value());
	}

	public function test_subquery_expression(): void
	{
		$expr = new Database_Expression('(SELECT MAX(id) FROM users)');
		$this->assertSame('(SELECT MAX(id) FROM users)', $expr->value());
	}

	public function test_math_expression(): void
	{
		$expr = new Database_Expression('price * 1.08');
		$this->assertSame('price * 1.08', $expr->value());
	}

	public function test_parameters_does_not_overwrite_existing(): void
	{
		$expr = new Database_Expression(':a + :b');
		$expr->param(':a', 1);
		$expr->parameters(array(':b' => 2));

		$refl = new ReflectionClass($expr);
		$prop = $refl->getProperty('_parameters');
		$params = $prop->getValue($expr);

		$this->assertArrayHasKey(':a', $params);
		$this->assertArrayHasKey(':b', $params);
		$this->assertSame(1, $params[':a']);
		$this->assertSame(2, $params[':b']);
	}

	public function test_parameters_precedence(): void
	{
		$expr = new Database_Expression(':key');
		$expr->param(':key', 'original');
		$expr->parameters(array(':key' => 'override'));

		$refl = new ReflectionClass($expr);
		$prop = $refl->getProperty('_parameters');
		$params = $prop->getValue($expr);

		$this->assertSame('override', $params[':key']);
	}

	public function test_repeated_use_of_expression(): void
	{
		$expr = new Database_Expression('SUM(:col)');
		$expr->param(':col', 'price');

		$this->assertSame('SUM(:col)', $expr->value());
		$compiled = $expr->compile();
		$this->assertStringNotContainsString(':col', $compiled);
	}
}
