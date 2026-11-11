<?php

declare(strict_types=1);

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests return type signatures on magic methods, Log_Writer::write(),
 * and destructor return type absence for PHP compatibility.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.magic_methods
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_MagicMethodsSignatureTest extends Unittest_TestCase
{
	/**
	 * Data provider for magic methods and their expected return types.
	 *
	 * @return array<string, array{0: class-string, 1: string, 2: string}>
	 */
	public static function provider_magic_methods_return_types(): array
	{
		return array(
			'View::__set' => array(View::class, '__set', 'void'),
			'View::__isset' => array(View::class, '__isset', 'bool'),
			'View::__unset' => array(View::class, '__unset', 'void'),
			'ORM::__isset' => array(ORM::class, '__isset', 'bool'),
			'ORM::__unset' => array(ORM::class, '__unset', 'void'),
			'ORM::__serialize' => array(ORM::class, '__serialize', 'array'),
			'Log_Writer::write' => array(Log_Writer::class, 'write', 'void'),
		);
	}

	/**
	 * Tests that magic methods and Log_Writer::write declare expected return types.
	 *
	 * @dataProvider provider_magic_methods_return_types
	 * @param class-string $class
	 */
	public function test_methods_declare_expected_return_type(string $class, string $method, string $expectedType): void
	{
		if (!class_exists($class)) {
			$this->markTestSkipped("Class {$class} is not available.");
		}

		// Arrange & Act
		$refMethod = new ReflectionMethod($class, $method);

		// Assert
		$this->assertTrue($refMethod->hasReturnType(), "Method {$class}::{$method}() must have a return type declaration.");

		$returnType = $refMethod->getReturnType();
		$this->assertInstanceOf(ReflectionNamedType::class, $returnType);
		$this->assertFalse($returnType->allowsNull(), "Method {$class}::{$method}() return type must not be nullable.");
		$this->assertSame($expectedType, $returnType->getName(), "Method {$class}::{$method}() must return type {$expectedType}.");
	}

	/**
	 * Data provider for classes whose __destruct() method must NOT declare a return type.
	 *
	 * @return array<string, array{0: class-string}>
	 */
	public static function provider_destructors_without_return_type(): array
	{
		return array(
			'Log_Syslog::__destruct' => array(Log_Syslog::class),
			'Database::__destruct' => array(Database::class),
			'Database_Result::__destruct' => array(Database_Result::class),
			'Image_GD::__destruct' => array(Image_GD::class),
			'Image_Imagick::__destruct' => array(Image_Imagick::class),
		);
	}

	/**
	 * Tests that destructors do not declare a return type to comply with PHP rules.
	 *
	 * @dataProvider provider_destructors_without_return_type
	 * @param class-string $class
	 */
	public function test_destructors_do_not_declare_return_type(string $class): void
	{
		if (!class_exists($class)) {
			$this->markTestSkipped("Class {$class} is not available.");
		}

		// Arrange & Act
		$refMethod = new ReflectionMethod($class, '__destruct');

		// Assert
		$this->assertFalse(
			$refMethod->hasReturnType(),
			"Destructor in {$class} cannot declare a return type in PHP."
		);
	}
}
