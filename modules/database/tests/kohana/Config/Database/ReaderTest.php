<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Config_Database_Reader
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.config
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Config_Database_ReaderTest extends Unittest_TestCase
{
	public function test_implements_config_reader(): void
	{
		$reader = new Config_Database_Reader();
		$this->assertInstanceOf(Kohana_Config_Reader::class, $reader);
	}

	public function test_load_method_exists(): void
	{
		$reader = new Config_Database_Reader();
		$this->assertTrue(method_exists($reader, 'load'));
	}

	public function test_constructor_with_instantiated_config(): void
	{
		$reader = new Config_Database_Reader(array('instance' => 'default'));
		$this->assertInstanceOf(Config_Database_Reader::class, $reader);
	}

	public function test_constructor_with_custom_table_name(): void
	{
		$reader = new Config_Database_Reader(array(
			'instance'   => 'default',
			'table_name' => 'custom_config',
		));
		$this->assertInstanceOf(Kohana_Config_Reader::class, $reader);
	}

	public function test_constructor_default_config(): void
	{
		$reader = new Config_Database_Reader();
		$this->assertInstanceOf(Config_Database_Reader::class, $reader);
	}
}
