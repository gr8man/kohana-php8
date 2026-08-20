<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the View class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.view
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ViewTest extends Unittest_TestCase
{
	protected static $old_modules = array();

	/**
	 * Setups the filesystem for test view files
	 */
	// @codingStandardsIgnoreStart
	public static function setUpBeforeClass(): void
	// @codingStandardsIgnoreEnd
	{
		self::$old_modules = Kohana::modules();

		$new_modules = self::$old_modules + array(
			'test_views' => realpath(dirname(__FILE__).'/../test_data/')
		);
		Kohana::modules($new_modules);
	}

	/**
	 * Restores the module list
	 */
	// @codingStandardsIgnoreStart
	public static function tearDownAfterClass(): void
	// @codingStandardsIgnoreEnd
	{
		Kohana::modules(self::$old_modules);
	}

	/**
	 * Reset global data before each test
	 */
	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		$refl = new ReflectionProperty(View::class, '_global_data');
		$refl->setAccessible(true);
		$refl->setValue(null, array());
	}

	/**
	 * Provider for test_factory and test_constructor
	 *
	 * @return array
	 */
	public function provider_create(): array
	{
		return array(
			array(null, null),
			array('test.css', null),
			array('test.css', array('foo' => 'bar')),
		);
	}

	/**
	 * Tests View::factory()
	 *
	 * @test
	 * @dataProvider provider_create
	 * @covers View::factory
	 */
	public function test_factory($file, $data): void
	{
		$view = View::factory($file, $data);
		$this->assertInstanceOf(View::class, $view);

		if ($data !== null) {
			foreach ($data as $key => $value) {
				$this->assertSame($value, $view->$key);
			}
		}
	}

	/**
	 * Tests View::__construct
	 *
	 * @test
	 * @dataProvider provider_create
	 * @covers View::__construct
	 */
	public function test_constructor($file, $data): void
	{
		$view = new View($file, $data);
		$this->assertInstanceOf(View::class, $view);

		if ($data !== null) {
			foreach ($data as $key => $value) {
				$this->assertSame($value, $view->$key);
			}
		}
	}

	/**
	 * Tests View::set_filename with valid and invalid files
	 *
	 * @test
	 * @covers View::set_filename
	 */
	public function test_set_filename(): void
	{
		$view = new View();
		$result = $view->set_filename('test.css');
		$this->assertSame($view, $result);
	}

	/**
	 * Tests View::set_filename throws exception for nonexistent files
	 *
	 * @test
	 * @covers View::set_filename
	 */
	public function test_set_filename_exception(): void
	{
		$this->expectException(View_Exception::class);
		$view = new View();
		$view->set_filename('nonexistent_view');
	}

	/**
	 * Tests View::set
	 *
	 * @test
	 * @covers View::set
	 */
	public function test_set_with_string_key(): void
	{
		$view = new View();
		$result = $view->set('foo', 'bar');
		$this->assertSame($view, $result);
		$this->assertSame('bar', $view->foo);
	}

	/**
	 * Tests View::set with array
	 *
	 * @test
	 * @covers View::set
	 */
	public function test_set_with_array(): void
	{
		$view = new View();
		$view->set(array('foo' => 'bar', 'baz' => 'qux'));
		$this->assertSame('bar', $view->foo);
		$this->assertSame('qux', $view->baz);
	}

	/**
	 * Tests View::set with Traversable
	 *
	 * @test
	 * @covers View::set
	 */
	public function test_set_with_traversable(): void
	{
		$view = new View();
		$view->set(new ArrayIterator(array('foo' => 'bar')));
		$this->assertSame('bar', $view->foo);
	}

	/**
	 * Tests View::set_global with string key
	 *
	 * @test
	 * @covers View::set_global
	 */
	public function test_set_global_with_string_key(): void
	{
		View::set_global('foo', 'bar');
		$view = new View();
		$this->assertSame('bar', $view->foo);
	}

	/**
	 * Tests View::set_global with array
	 *
	 * @test
	 * @covers View::set_global
	 */
	public function test_set_global_with_array(): void
	{
		View::set_global(array('foo' => 'bar', 'baz' => 'qux'));
		$view = new View();
		$this->assertSame('bar', $view->foo);
		$this->assertSame('qux', $view->baz);
	}

	/**
	 * Tests View::set_global with Traversable
	 *
	 * @test
	 * @covers View::set_global
	 */
	public function test_set_global_with_traversable(): void
	{
		View::set_global(new ArrayIterator(array('foo' => 'bar')));
		$view = new View();
		$this->assertSame('bar', $view->foo);
	}

	/**
	 * Tests View::bind
	 *
	 * @test
	 * @covers View::bind
	 */
	public function test_bind(): void
	{
		$view = new View();
		$value = 'original';
		$result = $view->bind('ref', $value);
		$this->assertSame($view, $result);
		$this->assertSame('original', $view->ref);

		$value = 'modified';
		$this->assertSame('modified', $view->ref);
	}

	/**
	 * Tests View::bind_global
	 *
	 * @test
	 * @covers View::bind_global
	 */
	public function test_bind_global(): void
	{
		$value = 'original';
		View::bind_global('ref', $value);

		$view = new View();
		$this->assertSame('original', $view->ref);

		$value = 'modified';
		$this->assertSame('modified', $view->ref);
	}

	/**
	 * Tests View::__set
	 *
	 * @test
	 * @covers View::__set
	 */
	public function test_magic_set(): void
	{
		$view = new View();
		$view->foo = 'bar';
		$this->assertSame('bar', $view->foo);
	}

	/**
	 * Tests View::__get for local variables
	 *
	 * @test
	 * @covers View::__get
	 */
	public function test_magic_get_local(): void
	{
		$view = new View();
		$view->set('foo', 'local_value');
		$this->assertSame('local_value', $view->foo);
	}

	/**
	 * Tests View::__get for global variables
	 *
	 * @test
	 * @covers View::__get
	 */
	public function test_magic_get_global(): void
	{
		View::set_global('foo', 'global_value');
		$view = new View();
		$this->assertSame('global_value', $view->foo);
	}

	/**
	 * Tests that local data overrides global data in __get
	 *
	 * @test
	 * @covers View::__get
	 */
	public function test_magic_get_local_overrides_global(): void
	{
		View::set_global('foo', 'global_value');
		$view = new View();
		$view->set('foo', 'local_value');
		$this->assertSame('local_value', $view->foo);
	}

	/**
	 * Tests View::__get throws exception for undefined variables
	 *
	 * @test
	 * @covers View::__get
	 */
	public function test_magic_get_undefined(): void
	{
		$this->expectException(Kohana_Exception::class);
		$view = new View();
		$view->nonexistent;
	}

	/**
	 * Tests View::__isset
	 *
	 * @test
	 * @covers View::__isset
	 */
	public function test_magic_isset(): void
	{
		$view = new View();

		// Not set
		$this->assertFalse(isset($view->foo));

		// Local
		$view->set('foo', 'bar');
		$this->assertTrue(isset($view->foo));

		// Global
		unset($view->foo);
		View::set_global('foo', 'bar');
		$this->assertTrue(isset($view->foo));
	}

	/**
	 * Tests View::__unset
	 *
	 * @test
	 * @covers View::__unset
	 */
	public function test_magic_unset(): void
	{
		$view = new View();

		// Local
		$view->set('foo', 'bar');
		$this->assertTrue(isset($view->foo));
		unset($view->foo);
		$this->assertFalse(isset($view->foo));

		// Global
		View::set_global('bar', 'baz');
		$this->assertTrue(isset($view->bar));
		unset($view->bar);
		$this->assertFalse(isset($view->bar));
	}

	/**
	 * Tests View clone
	 *
	 * @test
	 * @covers Kohana_View
	 */
	public function test_magic_clone(): void
	{
		$view = new View('test.css');
		$view->set('foo', 'bar');

		$cloned = clone $view;
		$this->assertSame('bar', $cloned->foo);

		$view->set('foo', 'modified');
		$this->assertSame('bar', $cloned->foo);
	}

	/**
	 * Tests View::capture via render
	 *
	 * @test
	 * @covers View::capture
	 */
	public function test_capture(): void
	{
		$view = new View('test.css');
		$output = $view->render();
		$this->assertStringContainsString('This is a view with a dot in the filename.', $output);
	}

	/**
	 * Tests View::render throws exception when no file is set
	 *
	 * @test
	 * @covers View::render
	 */
	public function test_render_without_file(): void
	{
		$this->expectException(View_Exception::class);
		$view = new View();
		$view->render();
	}

	/**
	 * Tests View::render with file param
	 *
	 * @test
	 * @covers View::render
	 */
	public function test_render_with_file_param(): void
	{
		$view = new View();
		$output = $view->render('test.css');
		$this->assertStringContainsString('This is a view with a dot in the filename.', $output);
	}

	/**
	 * Tests View::render with preset file
	 *
	 * @test
	 * @covers View::render
	 */
	public function test_render_with_preset_file(): void
	{
		$view = new View('test.css');
		$output = $view->render();
		$this->assertStringContainsString('This is a view with a dot in the filename.', $output);
	}

	/**
	 * Tests View::__toString
	 *
	 * @test
	 * @covers View::__toString
	 */
	public function test_magic_to_string(): void
	{
		$view = new View('test.css');
		$output = (string) $view;
		$this->assertStringContainsString('This is a view with a dot in the filename.', $output);
	}
}
