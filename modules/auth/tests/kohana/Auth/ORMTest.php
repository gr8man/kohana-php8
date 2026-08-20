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

		Database::$instances['default'] = new Mock_Database_For_Auth_ORM_Test('default', array('table_prefix' => ''));

		Kohana::$config->load('auth')->set('hash_key', 'test_key_auth_orm');
		Kohana::$config->load('auth')->set('hash_method', 'sha256');

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

	public function test_check_password(): void
	{
		$auth = new Auth_ORM($this->_auth_config);
		$user = new Model_Auth_User();
		$password = 'mypassword123';
		$hashed = $auth->hash_password($password);
		$user->password = $hashed;
		$auth->force_login($user);
		$this->assertTrue($auth->check_password($password, $hashed));
		$this->assertFalse($auth->check_password('wrongpass', $hashed));
	}

	public function test_force_login_and_logout(): void
	{
		$auth = new Auth_ORM($this->_auth_config);
		$user = new Model_Auth_User();
		$user->password = 'some_hashed_val';
		$auth->force_login($user, true);

		$this->assertSame($user->password, $auth->password($user));
		$this->assertSame($user, $auth->get_user());
		$this->assertTrue($auth->logout(true, true));
		$this->assertFalse($auth->logged_in());
	}

	public function test_model_auth_user_helper_methods(): void
	{
		$user = new Model_Auth_User();
		$labels = $user->labels();
		$this->assertIsArray($labels);
		$this->assertArrayHasKey('username', $labels);

		$validation = Model_Auth_User::get_password_validation(array('password' => 'secret123', 'password_confirm' => 'secret123'));
		$this->assertInstanceOf(Validation::class, $validation);

		$this->assertSame('username', $user->unique_key('john_doe'));
		$this->assertSame('email', $user->unique_key('john@example.com'));

		$user->complete_login();
		$this->assertTrue(true);
	}

	public function test_auth_orm_logged_in_and_password(): void
	{
		Database::$instances['default'] = new Mock_Database_For_Auth_ORM_Test('default', array('table_prefix' => ''));
		$auth = new Auth_ORM($this->_auth_config);
		$user = new Model_User();
		$ref_loaded = new ReflectionProperty($user, '_loaded');
		$ref_loaded->setValue($user, true);
		$ref_pk = new ReflectionProperty($user, '_primary_key_value');
		$ref_pk->setValue($user, 1);
		$password = 'mypassword123';
		$hashed = $auth->hash($password);
		$ref_object = new ReflectionProperty($user, '_object');
		$ref_object->setValue($user, array(
			'id' => 1,
			'username' => 'testuser',
			'email' => 'test@example.com',
			'password' => $hashed,
			'logins' => 0,
			'last_login' => 0,
		));

		$auth->force_login($user);
		$this->assertTrue($auth->logged_in());
		$this->assertTrue($auth->check_password($password));

		$this->assertSame($hashed, $auth->password($user));

		$this->assertFalse($auth->auto_login());

		Cookie::set('authautologin', 'dummy_token_val', 3600);
		$this->assertFalse($auth->auto_login());
		$auth->logout(true, true);
	}
}
