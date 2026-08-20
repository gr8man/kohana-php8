<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Auth_ORM driver and Model_Auth_User
 *
 * @group kohana
 * @group kohana.auth
 * @group kohana.auth.orm
 * @package    Kohana/Auth
 * @category   Tests
 */
class Kohana_Auth_ORMTest extends Unittest_TestCase
{
	protected array $_auth_config;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		if (!class_exists('Mock_Database_For_Auth_ORM_Test', false)) {
			eval('
			class Mock_Database_For_Auth_ORM_Test extends Database {
				protected $_identifier = "`";
				protected $_config = array("table_prefix" => "");
				public function connect() {}
				public function disconnect() { return true; }
				public function set_charset($charset) {}
				public function query(int $type, string $sql, bool $as_object = false, array $params = null) {
					return new Database_Result_Cached(array(), $sql, $as_object);
				}
				public function begin($mode = null) { return true; }
				public function commit() { return true; }
				public function rollback() { return true; }
				public function list_tables($like = null) { return array(); }
				public function list_columns($table, $like = null, $add_prefix = true) {
					return array(
						"id" => array("type" => "int"),
						"email" => array("type" => "string"),
						"username" => array("type" => "string"),
						"password" => array("type" => "string"),
						"logins" => array("type" => "int"),
						"last_login" => array("type" => "int"),
						"user_id" => array("type" => "int"),
						"user_agent" => array("type" => "string"),
						"token" => array("type" => "string"),
						"created" => array("type" => "int"),
						"expires" => array("type" => "int"),
						"name" => array("type" => "string"),
						"description" => array("type" => "string"),
					);
				}
				public function escape($value) { return "\'" . $value . "\'"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		Database::$instances['default'] = new Mock_Database_For_Auth_ORM_Test('default', array('table_prefix' => ''));
	}

	public function setUp(): void
	{
		parent::setUp();

		$this->_auth_config = array(
			'driver'       => 'ORM',
			'hash_method'  => 'sha256',
			'hash_key'     => 'test_key_auth_orm',
			'lifetime'     => 1209600,
			'session_type' => 'native',
			'session_key'  => 'auth_user_orm',
		);
	}

	public function test_auth_orm_instance(): void
	{
		$auth = new Auth_ORM($this->_auth_config);
		$this->assertInstanceOf(Auth_ORM::class, $auth);
		$this->assertFalse($auth->logged_in());
		$this->assertNull($auth->get_user());
	}

	public function test_hash_password_and_verify(): void
	{
		$auth = new Auth_ORM($this->_auth_config);
		$password = 'secretPassword123';
		$hashed = $auth->hash($password);
		$this->assertNotEmpty($hashed);
		$this->assertSame($hashed, $auth->hash($password));
	}

	public function test_model_auth_user_rules(): void
	{
		$user = new Model_Auth_User();
		$rules = $user->rules();
		$this->assertIsArray($rules);
		$this->assertArrayHasKey('username', $rules);
		$this->assertArrayHasKey('password', $rules);
		$this->assertArrayHasKey('email', $rules);
	}

	public function test_model_auth_user_filters(): void
	{
		$user = new Model_Auth_User();
		$filters = $user->filters();
		$this->assertIsArray($filters);
		$this->assertArrayHasKey('password', $filters);
	}

	public function test_model_auth_user_token(): void
	{
		$token = new Model_Auth_User_Token();
		$this->assertInstanceOf(Model_Auth_User_Token::class, $token);
		$ref = new ReflectionMethod($token, 'create_token');
		$generated_token = $ref->invoke($token);
		$this->assertIsString($generated_token);
		$this->assertSame(40, strlen($generated_token));
	}

	public function test_model_auth_role(): void
	{
		$role = new Model_Auth_Role();
		$rules = $role->rules();
		$this->assertIsArray($rules);
		$this->assertArrayHasKey('name', $rules);
	}
}
