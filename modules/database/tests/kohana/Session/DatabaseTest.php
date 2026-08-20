<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Session_Database
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.session
 * @package    Kohana/Database
 * @category   Tests
 */
class Kohana_Session_DatabaseTest extends Unittest_TestCase
{
	protected Database_PDO $db;

	public function setUp(): void
	{
		parent::setUp();

		if (! extension_loaded('pdo_sqlite')) {
			$this->markTestSkipped('PDO SQLite extension is not available.');
		}

		$config = array(
			'type'       => 'PDO',
			'connection' => array(
				'dsn'        => 'sqlite::memory:',
				'persistent' => false,
			),
			'table_prefix' => '',
			'charset'      => null,
			'caching'      => false,
		);

		Cookie::$salt = 'test_salt_for_session_database_test';
		$this->db = new Database_PDO('session_db', $config);
		$this->db->connect();
		Database::$instances['session_db'] = $this->db;

		// Create sessions table
		$this->db->query(null, 'CREATE TABLE sessions (
			session_id VARCHAR(24) PRIMARY KEY,
			last_active INTEGER,
			contents TEXT
		)');
	}

	public function tearDown(): void
	{
		unset(Database::$instances['session_db']);
		$this->db->disconnect();
		parent::tearDown();
	}

	public function test_session_database_crud(): void
	{
		$config = array(
			'group' => 'session_db',
			'table' => 'sessions',
			'name'  => 'test_sess',
		);

		$session = new Session_Database($config);
		$session->set('user_id', 42);
		$session->set('username', 'alice');

		$this->assertSame(42, $session->get('user_id'));
		$this->assertSame('alice', $session->get('username'));

		// Test write
		$this->assertTrue($session->write());

		// Test read back with session id
		$id = $session->id();
		$this->assertNotEmpty($id);

		$session2 = new Session_Database($config, $id);
		$this->assertSame(42, $session2->get('user_id'));
		$this->assertSame('alice', $session2->get('username'));

		// Test delete
		$session2->delete('username');
		$this->assertNull($session2->get('username'));

		// Test destroy
		$this->assertTrue($session2->destroy());
	}
}
