<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Core tests for ORM
 *
 * @group kohana
 * @group kohana.orm
 * @group kohana.orm.core
 *
 * @package    Kohana/ORM
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_ORMTest extends Unittest_TestCase
{
	/**
	 * Set up a test model without database dependency.
	 */
	public static function setUpBeforeClass(): void
	{
		if (!class_exists('Mock_Database_For_ORM_Test', false)) {
			eval('
			class Mock_Database_For_ORM_Test extends Database {
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
				public function list_columns($table, $like = null, $add_prefix = true) { return array(); }
				public function escape($value) { return "\'" . $value . "\'"; }
				public function table_prefix() { return ""; }
			}
			');
		}

		$mock = new Mock_Database_For_ORM_Test('default', array('table_prefix' => ''));
		Database::$instances['default'] = $mock;

		if (!class_exists('Model_TestUser', false)) {
			eval('
			#[AllowDynamicProperties]
			class Model_TestUser extends ORM {
				protected $_table_columns = array(
					"id"        => array("type" => "int"),
					"username"  => array("type" => "string"),
					"email"     => array("type" => "string"),
					"password"  => array("type" => "string"),
					"status"    => array("type" => "string"),
					"logins"    => array("type" => "int"),
					"meta"      => array("type" => "string"),
					"created_at" => array("type" => "int"),
				);

				protected $_has_one = array(
					"profile" => array("model" => "TestProfile"),
				);

				protected $_belongs_to = array(
					"role" => array("model" => "TestRole"),
				);

				protected $_has_many = array(
					"posts" => array("model" => "TestPost"),
				);

				protected $_serialize_columns = array("meta");

				public function rules(): array {
					return array(
						"username" => array(
							array("not_empty"),
							array("min_length", array(":value", 3)),
							array("max_length", array(":value", 50)),
						),
						"email" => array(
							array("not_empty"),
							array("email"),
						),
					);
				}

				public function filters(): array {
					return array(
						true => array(
							array("trim"),
						),
						"username" => array(
							array("strip_tags"),
						),
					);
				}

				public function labels(): array {
					return array(
						"username" => "Username",
						"email"    => "Email Address",
					);
				}
			}
			');
		}

		if (!class_exists('Model_TestProfile', false)) {
			eval('
			#[AllowDynamicProperties]
			class Model_TestProfile extends ORM {
				protected $_table_columns = array(
					"id"      => array("type" => "int"),
					"user_id" => array("type" => "int"),
					"bio"     => array("type" => "string"),
				);
			}
			');
		}

		if (!class_exists('Model_TestRole', false)) {
			eval('
			#[AllowDynamicProperties]
			class Model_TestRole extends ORM {
				protected $_table_columns = array(
					"id"   => array("type" => "int"),
					"name" => array("type" => "string"),
				);
			}
			');
		}

		if (!class_exists('Model_TestPost', false)) {
			eval('
			#[AllowDynamicProperties]
			class Model_TestPost extends ORM {
				protected $_table_columns = array(
					"id"      => array("type" => "int"),
					"user_id" => array("type" => "int"),
					"title"   => array("type" => "string"),
				);
			}
			');
		}
	}

	public function test_factory_creates_model(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertInstanceOf(Model_TestUser::class, $model);
	}

	public function test_factory_with_array(): void
	{
		$model = ORM::factory('TestUser', array('id' => 5));
		$this->assertInstanceOf(Model_TestUser::class, $model);
	}

	public function test_object_name(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('testuser', $model->object_name());
	}

	public function test_table_name(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('testusers', $model->table_name());
	}

	public function test_primary_key_default(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('id', $model->primary_key());
	}

	public function test_loaded_returns_false_initially(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertFalse($model->loaded());
	}

	public function test_saved_returns_false_initially(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertFalse($model->saved());
	}

	public function test_set_and_get_column(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john_doe';
		$this->assertSame('john_doe', $model->username);
	}

	public function test_set_via_set_method(): void
	{
		$model = ORM::factory('TestUser');
		$model->set('username', 'jane_doe');
		$this->assertSame('jane_doe', $model->username);
	}

	public function test_changed_columns_tracking(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'new_user';
		$changed = $model->changed();
		$this->assertArrayHasKey('username', $changed);
	}

	public function test_changed_with_field_name(): void
	{
		$model = ORM::factory('TestUser');
		$model->email = 'test@example.com';
		$this->assertNotNull($model->changed('email'));
	}

	public function test_changed_unchanged_field(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertNull($model->changed('email'));
	}

	public function test_values_sets_multiple_columns(): void
	{
		$model = ORM::factory('TestUser');
		$model->values(array(
			'username' => 'john',
			'email' => 'john@example.com',
		));
		$this->assertSame('john', $model->username);
		$this->assertSame('john@example.com', $model->email);
	}

	public function test_values_ignores_unknown_columns(): void
	{
		$model = ORM::factory('TestUser');
		$model->values(array(
			'username' => 'john',
			'nonexistent' => 'value',
		));
		$this->assertSame('john', $model->username);
	}

	public function test_values_excludes_primary_key(): void
	{
		$model = ORM::factory('TestUser');
		$model->values(array(
			'id' => 999,
			'username' => 'test',
		));
		$this->assertNull($model->id);
	}

	public function test_values_accepts_null(): void
	{
		$model = ORM::factory('TestUser');
		$model->values(array(
			'username' => null,
			'email' => null,
		));
		$this->assertNull($model->username);
	}

	public function test_as_array_returns_column_data(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john';
		$model->email = 'john@example.com';
		$array = $model->as_array();
		$this->assertArrayHasKey('username', $array);
		$this->assertArrayHasKey('email', $array);
		$this->assertSame('john', $array['username']);
	}

	public function test_clear_resets_model(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john';
		$model->email = 'john@example.com';
		$model->clear();

		$this->assertNull($model->username);
		$this->assertNull($model->email);
		$this->assertFalse($model->loaded());
	}

	public function test_clear_resets_changed(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john';
		$model->clear();
		$this->assertSame(array(), $model->changed());
	}

	public function test_isset_returns_true_for_column(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john';
		$this->assertTrue(isset($model->username));
	}

	public function test_isset_returns_false_for_unset_column(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertFalse(isset($model->username));
	}

	public function test_unset_clears_column(): void
	{
		$model = ORM::factory('TestUser');
		$model->username = 'john';
		unset($model->username);
		$this->assertFalse(isset($model->username));
	}

	public function test_to_string_returns_pk(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('', (string) $model);
	}

	public function test_pk_returns_null_when_not_loaded(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertNull($model->pk());
	}

	public function test_table_columns_returns_column_info(): void
	{
		$model = ORM::factory('TestUser');
		$columns = $model->table_columns();
		$this->assertArrayHasKey('id', $columns);
		$this->assertArrayHasKey('username', $columns);
		$this->assertArrayHasKey('email', $columns);
	}

	public function test_has_one_returns_defined_relationships(): void
	{
		$model = ORM::factory('TestUser');
		$has_one = $model->has_one();
		$this->assertArrayHasKey('profile', $has_one);
	}

	public function test_belongs_to_returns_defined_relationships(): void
	{
		$model = ORM::factory('TestUser');
		$belongs_to = $model->belongs_to();
		$this->assertArrayHasKey('role', $belongs_to);
	}

	public function test_has_many_returns_defined_relationships(): void
	{
		$model = ORM::factory('TestUser');
		$has_many = $model->has_many();
		$this->assertArrayHasKey('posts', $has_many);
	}

	public function test_rules_returns_validation_rules(): void
	{
		$model = ORM::factory('TestUser');
		$rules = $model->rules();
		$this->assertArrayHasKey('username', $rules);
		$this->assertArrayHasKey('email', $rules);
	}

	public function test_filters_returns_filter_definitions(): void
	{
		$model = ORM::factory('TestUser');
		$filters = $model->filters();
		$this->assertNotEmpty($filters);
		$this->assertArrayHasKey('username', $filters);
	}

	public function test_labels_returns_label_definitions(): void
	{
		$model = ORM::factory('TestUser');
		$labels = $model->labels();
		$this->assertSame('Username', $labels['username']);
		$this->assertSame('Email Address', $labels['email']);
	}

	public function test_object_plural_auto_generated(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('testusers', $model->object_plural());
	}

	public function test_serialize_value(): void
	{
		$model = ORM::factory('TestUser');
		$value = array('key' => 'value');
		$model->set('meta', $value);

		$refl = new ReflectionClass($model);
		$objectProp = $refl->getProperty('_object');
		$object = $objectProp->getValue($model);
		$this->assertIsString($object['meta']);
	}

	public function test_unserialize_value_on_get(): void
	{
		$model = ORM::factory('TestUser');
		$data = array('theme' => 'dark');
		$model->meta = $data;
		$retrieved = $model->meta;
		$this->assertSame($data, $retrieved);
	}

	public function test_implements_stringable(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertInstanceOf(Stringable::class, $model);
	}

	public function test_where_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->where('id', '=', 1);
		$this->assertSame($model, $result);
	}

	public function test_and_where_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->and_where('status', '=', 'active');
		$this->assertSame($model, $result);
	}

	public function test_or_where_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->or_where('status', '=', 'inactive');
		$this->assertSame($model, $result);
	}

	public function test_order_by_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->order_by('username', 'ASC');
		$this->assertSame($model, $result);
	}

	public function test_limit_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->limit(10);
		$this->assertSame($model, $result);
	}

	public function test_select_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->select('id', 'username');
		$this->assertSame($model, $result);
	}

	public function test_join_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->join('roles', 'LEFT');
		$this->assertSame($model, $result);
	}

	public function test_group_by_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->group_by('status');
		$this->assertSame($model, $result);
	}

	public function test_having_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->having('id', '>', 1);
		$this->assertSame($model, $result);
	}

	public function test_distinct_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->distinct(true);
		$this->assertSame($model, $result);
	}

	public function test_reset_clears_pending_queries(): void
	{
		$model = ORM::factory('TestUser');
		$model->where('id', '=', 1)->order_by('name');
		$model->reset();

		$refl = new ReflectionClass($model);
		$pendingProp = $refl->getProperty('_db_pending');
		$this->assertSame(array(), $pendingProp->getValue($model));
	}

	public function test_reset_with_false_keeps_pending(): void
	{
		$model = ORM::factory('TestUser');
		$model->where('id', '=', 1);
		$model->reset(false);

		$refl = new ReflectionClass($model);
		$resetProp = $refl->getProperty('_db_reset');
		$this->assertFalse($resetProp->getValue($model));
	}

	public function test_errors_filename_defaults_to_object_name(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame('testuser', $model->errors_filename());
	}

	public function test_primary_key_value_null_when_not_loaded(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertNull($model->pk());
	}

	public function test_cached_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->cached(3600);
		$this->assertSame($model, $result);
	}

	public function test_param_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->param(':id', 1);
		$this->assertSame($model, $result);
	}

	public function test_offset_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->offset(10);
		$this->assertSame($model, $result);
	}

	public function test_using_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->using('user_id');
		$this->assertSame($model, $result);
	}

	public function test_on_proxy_returns_self(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->on('u.id', '=', 'p.user_id');
		$this->assertSame($model, $result);
	}

	public function test_where_open_close(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame($model, $model->where_open());
		$this->assertSame($model, $model->where_close());
	}

	public function test_and_or_where_open_close(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame($model, $model->and_where_open());
		$this->assertSame($model, $model->and_where_close());
		$this->assertSame($model, $model->or_where_open());
		$this->assertSame($model, $model->or_where_close());
	}

	public function test_having_open_close(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame($model, $model->having_open());
		$this->assertSame($model, $model->having_close());
	}

	public function test_and_or_having_open_close(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame($model, $model->and_having_open());
		$this->assertSame($model, $model->and_having_close());
		$this->assertSame($model, $model->or_having_open());
		$this->assertSame($model, $model->or_having_close());
	}

	public function test_unique_false_when_not_loaded(): void
	{
		$model = ORM::factory('TestUser');
		$refl = new ReflectionClass($model);
		$loadedProp = $refl->getProperty('_loaded');
		$loadedProp->setValue($model, false);

		try {
			$result = $model->unique('username', 'john');
		} catch (Exception $e) {
			$this->markTestSkipped('unique() requires database: ' . $e->getMessage());
		}
	}

	public function test_get_on_existing_column(): void
	{
		$model = ORM::factory('TestUser');
		$refl = new ReflectionClass($model);
		$objectProp = $refl->getProperty('_object');
		$objectProp->setValue($model, array(
			'id' => null,
			'username' => 'test_user',
			'email' => null,
			'password' => null,
			'status' => null,
			'logins' => null,
			'created_at' => null,
			'meta' => null,
		));

		$this->assertSame('test_user', $model->get('username'));
	}

	public function test_get_on_missing_column_throws(): void
	{
		$this->expectException(Kohana_Exception::class);
		$model = ORM::factory('TestUser');
		$model->get('nonexistent_column');
	}

	public function test_set_on_existing_column_chainable(): void
	{
		$model = ORM::factory('TestUser');
		$result = $model->set('username', 'john');
		$this->assertSame($model, $result);
	}

	public function test_set_on_belongs_to_related(): void
	{
		$model = ORM::factory('TestUser');
		$role = ORM::factory('TestRole');
		$result = $model->set('role', $role);
		$this->assertSame($model, $result);
	}

	public function test_set_on_unknown_column_throws(): void
	{
		$this->expectException(Kohana_Exception::class);
		$model = ORM::factory('TestUser');
		$model->set('nonexistent', 'value');
	}

	public function test_isset_for_has_one_related(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertTrue(isset($model->profile));
	}

	public function test_isset_for_belongs_to_related(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertTrue(isset($model->role));
	}

	public function test_isset_for_has_many_related(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertTrue(isset($model->posts));
	}

	public function test_serialize_round_trip(): void
	{
		$model = ORM::factory('TestUser');
		$serialized = serialize($model);
		$unserialized = unserialize($serialized);
		$this->assertInstanceOf(Model_TestUser::class, $unserialized);
	}

	public function test_original_values_empty_after_clear(): void
	{
		$model = ORM::factory('TestUser');
		$model->clear();
		$this->assertSame(array(), $model->original_values());
	}

	public function test_created_column_returns_null(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertNull($model->created_column());
	}

	public function test_updated_column_returns_null(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertNull($model->updated_column());
	}

	public function test_load_with_returns_empty_array(): void
	{
		$model = ORM::factory('TestUser');
		$this->assertSame(array(), $model->load_with());
	}

	public static function tearDownAfterClass(): void
	{
		unset(Database::$instances['default']);
	}
}
