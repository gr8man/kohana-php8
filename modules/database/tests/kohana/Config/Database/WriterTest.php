<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Config_Database_Writer
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
class Kohana_Config_Database_WriterTest extends Unittest_TestCase
{
	public function test_implements_config_writer(): void
	{
		$writer = new Config_Database_Writer();
		$this->assertInstanceOf(Kohana_Config_Writer::class, $writer);
	}

	public function test_implements_config_reader(): void
	{
		$writer = new Config_Database_Writer();
		$this->assertInstanceOf(Kohana_Config_Reader::class, $writer);
	}

	public function test_write_method_exists(): void
	{
		$writer = new Config_Database_Writer();
		$this->assertTrue(method_exists($writer, 'write'));
	}

	public function test_constructor_with_instantiated_config(): void
	{
		$writer = new Config_Database_Writer(array('instance' => 'default'));
		$this->assertInstanceOf(Config_Database_Writer::class, $writer);
	}

	public function test_constructor_default_config(): void
	{
		$writer = new Config_Database_Writer();
		$this->assertInstanceOf(Config_Database_Writer::class, $writer);
	}

	public function test_constructor_with_custom_table_name(): void
	{
		$writer = new Config_Database_Writer(array(
			'instance'   => 'default',
			'table_name' => 'custom_config',
		));
		$this->assertInstanceOf(Kohana_Config_Writer::class, $writer);
	}

	public function test_load_and_write_sqlite(): void
	{
		$config = array(
			'type' => 'PDO',
			'connection' => array(
				'dsn' => 'sqlite::memory:',
			),
			'table_prefix' => '',
			'charset' => 'utf8',
		);
		$db = new Database_PDO('config_db', $config);
		Database::$instances['config_db'] = $db;

		$db->query(Database::UPDATE, 'CREATE TABLE config (group_name VARCHAR(128), config_key VARCHAR(128), config_value TEXT, PRIMARY KEY (group_name, config_key))');

		$writer = new Config_Database_Writer(array(
			'instance' => 'config_db',
			'table_name' => 'config',
		));

		$this->assertTrue($writer->write('test_group', 'foo', 'bar'));
		$loaded = $writer->load('test_group');
		$this->assertIsArray($loaded);
		$this->assertSame('bar', $loaded['foo']);

		// Update existing
		$this->assertTrue($writer->write('test_group', 'foo', 'baz'));
		$loaded_updated = $writer->load('test_group');
		$this->assertSame('baz', $loaded_updated['foo']);

		unset(Database::$instances['config_db']);
	}
}
