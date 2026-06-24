<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');

/**
 * A version of the stock PHPUnit testcase that includes some extra helpers
 * and default settings
 */
abstract class Kohana_Unittest_TestCase extends PHPUnit\Framework\TestCase
{
	/**
	 * Make sure PHPUnit backs up globals
	 * @var boolean
	 */
	protected $backupGlobals = false;

	/**
	 * A set of unittest helpers that are shared between normal / database
	 * testcases
	 * @var Kohana_Unittest_Helpers
	 */
	protected $_helpers;

	/**
	 * A default set of environment to be applied before each test
	 * @var array
	 */
	protected $environmentDefault = array();

	/**
	 * Creates a predefined environment using the default environment
	 *
	 * Extending classes that have their own setUp() should call
	 * parent::setUp()
	 */
	public function setUp(): void
	{
		$this->_helpers = new Unittest_Helpers();

		$this->setEnvironment($this->environmentDefault);
	}

	/**
	 * Restores the original environment overriden with setEnvironment()
	 *
	 * Extending classes that have their own tearDown()
	 * should call parent::tearDown()
	 */
	public function tearDown(): void
	{
		if ($this->_helpers) {
			$this->_helpers->restore_environment();
		}
	}

	/**
	 * Compatibility for removed assertInternalType
	 */
	public function assertInternalType(string $type, $actual, string $message = ''): void
	{
		match ($type) {
			'array' => $this->assertIsArray($actual, $message),
			'bool', 'boolean' => $this->assertIsBool($actual, $message),
			'float' => $this->assertIsFloat($actual, $message),
			'int', 'integer' => $this->assertIsInt($actual, $message),
			'numeric' => $this->assertIsNumeric($actual, $message),
			'object' => $this->assertIsObject($actual, $message),
			'resource' => $this->assertIsResource($actual, $message),
			'string' => $this->assertIsString($actual, $message),
			'scalar' => $this->assertIsScalar($actual, $message),
			'callable' => $this->assertIsCallable($actual, $message),
			'iterable' => $this->assertIsIterable($actual, $message),
			default => throw new Exception("Invalid type $type for assertInternalType"),
		};
	}

	/**
	 * Compatibility for removed assertAttributeSame
	 */
	public function assertAttributeSame($expected, string $attributeName, $actual, string $message = ''): void
	{
		$reflection = new ReflectionObject($actual);
		$property = $reflection->getProperty($attributeName);
		$this->assertSame($expected, $property->getValue($actual), $message);
	}

	/**
	 * Compatibility for removed assertAttributeEquals
	 */
	public function assertAttributeEquals($expected, string $attributeName, $actual, string $message = ''): void
	{
		$reflection = new ReflectionObject($actual);
		$property = $reflection->getProperty($attributeName);
		$this->assertEquals($expected, $property->getValue($actual), $message);
	}

	/**
	 * Compatibility for removed assertAttributeNotSame
	 */
	public function assertAttributeNotSame($expected, string $attributeName, $actual, string $message = ''): void
	{
		$reflection = new ReflectionObject($actual);
		$property = $reflection->getProperty($attributeName);
		$this->assertNotSame($expected, $property->getValue($actual), $message);
	}

	/**
	 * Compatibility for removed assertAttributeContains
	 */
	public function assertAttributeContains($expected, string $attributeName, $actual, string $message = ''): void
	{
		$reflection = new ReflectionObject($actual);
		$property = $reflection->getProperty($attributeName);
		$this->assertContains($expected, $property->getValue($actual), $message);
	}

	/**
	 * Compatibility for removed assertAttributeNotContains
	 */
	public function assertAttributeNotContains($expected, string $attributeName, $actual, string $message = ''): void
	{
		$reflection = new ReflectionObject($actual);
		$property = $reflection->getProperty($attributeName);
		$this->assertNotContains($expected, $property->getValue($actual), $message);
	}

	/**
	 * Overwrite assertContains to support strings in PHPUnit 9+
	 */
	#[\Override]
	public static function assertContains($needle, iterable $haystack, string $message = ''): void
	{
		if (is_string($haystack)) {
			self::assertStringContainsString($needle, $haystack, $message);
		} else {
			parent::assertContains($needle, $haystack, $message);
		}
	}

	/**
	 * Overwrite assertNotContains to support strings in PHPUnit 9+
	 */
	#[\Override]
	public static function assertNotContains($needle, iterable $haystack, string $message = ''): void
	{
		if (is_string($haystack)) {
			self::assertStringNotContainsString($needle, $haystack, $message);
		} else {
			parent::assertNotContains($needle, $haystack, $message);
		}
	}

	/**
	 * Compatibility for removed assertTag
	 */
	public function assertTag(array $matcher, string $actual, string $message = ''): void
	{
		$tag = $matcher['tag'] ?? null;
		$attributes = $matcher['attributes'] ?? array();

		if ($tag) {
			$this->assertStringContainsString('<' . $tag, $actual, $message);
		}

		foreach ($attributes as $key => $value) {
			if ($value === true) {
				$this->assertStringContainsString((string) $key, $actual, $message);
			} elseif ($value === false) {
				$this->assertStringNotContainsString((string) $key, $actual, $message);
			} else {
				$this->assertStringContainsString($key . '="' . $value . '"', $actual, $message);
			}
		}
	}

	/**
	 * Compatibility for removed assertNotTag
	 */
	public function assertNotTag(array $matcher, string $actual, string $message = ''): void
	{
		$tag = $matcher['tag'] ?? null;

		if ($tag) {
			$this->assertStringNotContainsString('<' . $tag, $actual, $message);
		}
	}

	/**
	 * Compatibility for removed readAttribute
	 */
	public function readAttribute($object, string $attributeName)
	{
		if (is_string($object)) {
			$reflection = new ReflectionClass($object);
			$property = $reflection->getProperty($attributeName);
			return $property->getValue();
		}

		$reflection = new ReflectionObject($object);
		$property = $reflection->getProperty($attributeName);
		return $property->getValue($object);
	}

	/**
	 * Compatibility for removed getMock
	 */
	public function getMock(string $className, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false)
	{
		$builder = $this->getMockBuilder($className);
		if ($methods) {
			$builder->setMethods($methods);
		}
		if (! $callOriginalConstructor) {
			$builder->disableOriginalConstructor();
		}
		if (! $callOriginalClone) {
			$builder->disableOriginalClone();
		}
		if (! $callAutoload) {
			$builder->disableAutoload();
		}
		if ($arguments) {
			$builder->setConstructorArgs($arguments);
		}
		if ($mockClassName) {
			$builder->setMockClassName($mockClassName);
		}
		return $builder->getMock();
	}

	/**
	 * Compatibility for removed setExpectedException
	 */
	public function setExpectedException(string $exception, $message = '', $code = null): void
	{
		$this->expectException($exception);
		if ($message) {
			$this->expectExceptionMessage($message);
		}
		if ($code !== null) {
			$this->expectExceptionCode($code);
		}
	}

	/**
	 * Removes all kohana related cache files in the cache directory
	 */
	public function cleanCacheDir()
	{
		return Unittest_Helpers::clean_cache_dir();
	}

	/**
	 * Helper function that replaces all occurences of '/' with
	 * the OS-specific directory separator
	 *
	 * @param string $path The path to act on
	 * @return string
	 */
	public function dirSeparator($path)
	{
		return Unittest_Helpers::dir_separator($path);
	}

	/**
	 * Allows easy setting & backing up of enviroment config
	 *
	 * Option types are checked in the following order:
	 *
	 * * Server Var
	 * * Static Variable
	 * * Config option
	 *
	 * @param array $environment List of environment to set
	 */
	public function setEnvironment(array $environment)
	{
		return $this->_helpers->set_environment($environment);
	}

	/**
	 * Check for internet connectivity
	 *
	 * @return boolean Whether an internet connection is available
	 */
	public function hasInternet()
	{
		return Unittest_Helpers::has_internet();
	}
}
