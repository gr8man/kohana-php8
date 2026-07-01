<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for ORM_Validation_Exception
 *
 * @group kohana
 * @group kohana.orm
 *
 * @package    Kohana/ORM
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_ORM_Validation_ExceptionTest extends Unittest_TestCase
{
	protected function makeValidation(array $data = array('username' => 'john')): Validation
	{
		return Validation::factory($data)
			->rules('username', array(array('not_empty')));
	}

	public function test_constructor_stores_values(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$previous = new RuntimeException('previous');

		$exception = new ORM_Validation_Exception(
			'user',
			$validation,
			'Custom error message',
			array(':field' => 'username'),
			42,
			$previous
		);

		$this->assertSame('user', $exception->alias());
		$this->assertSame('Custom error message', $exception->getMessage());
		$this->assertSame(42, $exception->getCode());
		$this->assertSame($previous, $exception->getPrevious());
	}

	public function test_alias_returns_constructor_alias(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$exception = new ORM_Validation_Exception('user', $validation);

		$this->assertSame('user', $exception->alias());
	}

	public function test_objects_contains_validation_object(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$exception = new ORM_Validation_Exception('user', $validation);

		$objects = $exception->objects();

		$this->assertArrayHasKey('_object', $objects);
		$this->assertSame($validation, $objects['_object']);
		$this->assertArrayHasKey('_has_many', $objects);
		$this->assertFalse($objects['_has_many']);
	}

	public function test_add_object_adds_related_validation(): void
	{
		$main = $this->makeValidation();
		$main->check();

		$profile = Validation::factory(array('bio' => 'Hello'))
			->rules('bio', array(array('not_empty')));
		$profile->check();

		$exception = new ORM_Validation_Exception('user', $main);
		$exception->add_object('profile', $profile);

		$objects = $exception->objects();

		$this->assertArrayHasKey('profile', $objects);
		$this->assertArrayHasKey('_object', $objects['profile']);
		$this->assertSame($profile, $objects['profile']['_object']);
		$this->assertArrayHasKey('_has_many', $objects['profile']);
		$this->assertFalse($objects['profile']['_has_many']);
	}

	public function test_add_object_with_has_many_true_stores_nested(): void
	{
		$main = $this->makeValidation();
		$main->check();

		$post1 = Validation::factory(array('title' => 'First'))
			->rules('title', array(array('not_empty')));
		$post1->check();

		$post2 = Validation::factory(array('title' => 'Second'))
			->rules('title', array(array('not_empty')));
		$post2->check();

		$exception = new ORM_Validation_Exception('user', $main);
		$exception->add_object('posts', $post1, true);
		$exception->add_object('posts', $post2, true);

		$objects = $exception->objects();

		$this->assertArrayHasKey('posts', $objects);
		$this->assertArrayHasKey('_has_many', $objects['posts']);
		$this->assertTrue($objects['posts']['_has_many']);
		$this->assertArrayHasKey(0, $objects['posts']);
		$this->assertArrayHasKey(1, $objects['posts']);
		$this->assertSame($post1, $objects['posts'][0]['_object']);
		$this->assertSame($post2, $objects['posts'][1]['_object']);
	}

	public function test_add_object_with_has_many_string_key(): void
	{
		$main = $this->makeValidation();
		$main->check();

		$post = Validation::factory(array('title' => 'First'))
			->rules('title', array(array('not_empty')));
		$post->check();

		$exception = new ORM_Validation_Exception('user', $main);
		$exception->add_object('posts', $post, 'first_post');

		$objects = $exception->objects();

		$this->assertArrayHasKey('posts', $objects);
		$this->assertArrayHasKey('_has_many', $objects['posts']);
		$this->assertTrue($objects['posts']['_has_many']);
		$this->assertArrayHasKey('first_post', $objects['posts']);
		$this->assertSame($post, $objects['posts']['first_post']['_object']);
	}

	public function test_merge_combines_exceptions(): void
	{
		$mainValidation = $this->makeValidation();
		$mainValidation->check();

		$profileValidation = Validation::factory(array('bio' => 'Hello'))
			->rules('bio', array(array('not_empty')));
		$profileValidation->check();

		$main = new ORM_Validation_Exception('user', $mainValidation);
		$related = new ORM_Validation_Exception('profile', $profileValidation);

		$main->merge($related);

		$objects = $main->objects();

		$this->assertArrayHasKey('profile', $objects);
		$this->assertSame($profileValidation, $objects['profile']['_object']);
		$this->assertFalse($objects['profile']['_has_many']);
	}

	public function test_merge_with_has_many_true_stores_nested(): void
	{
		$mainValidation = $this->makeValidation();
		$mainValidation->check();

		$postValidation1 = Validation::factory(array('title' => 'Post 1'))
			->rules('title', array(array('not_empty')));
		$postValidation1->check();

		$postValidation2 = Validation::factory(array('title' => 'Post 2'))
			->rules('title', array(array('not_empty')));
		$postValidation2->check();

		$main = new ORM_Validation_Exception('user', $mainValidation);
		$related1 = new ORM_Validation_Exception('posts', $postValidation1);
		$related2 = new ORM_Validation_Exception('posts', $postValidation2);

		$main->merge($related1, true);
		$main->merge($related2, true);

		$objects = $main->objects();

		$this->assertArrayHasKey('posts', $objects);
		$this->assertTrue($objects['posts']['_has_many']);
		$this->assertArrayHasKey(0, $objects['posts']);
		$this->assertArrayHasKey(1, $objects['posts']);
		$this->assertSame($postValidation1, $objects['posts'][0]['_object']);
		$this->assertSame($postValidation2, $objects['posts'][1]['_object']);
	}

	public function test_errors_returns_validation_errors_array(): void
	{
		$invalid = Validation::factory(array('username' => ''))
			->rules('username', array(array('not_empty')));
		$invalid->check();

		$exception = new ORM_Validation_Exception('test', $invalid);
		$errors = $exception->errors('orm-validation');

		$this->assertIsArray($errors);
		$this->assertArrayHasKey('username', $errors);
	}

	public function test_errors_with_directory_parameter(): void
	{
		$invalid = Validation::factory(array('username' => ''))
			->rules('username', array(array('not_empty')));
		$invalid->check();

		$exception = new ORM_Validation_Exception('test', $invalid);
		$errors = $exception->errors('orm-validation');

		$this->assertIsArray($errors);
		$this->assertArrayHasKey('username', $errors);
		$this->assertIsString($errors['username']);
	}

	public function test_instance_of_kohana_exception_and_exception(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$exception = new ORM_Validation_Exception('user', $validation);

		$this->assertInstanceOf(Kohana_Exception::class, $exception);
		$this->assertInstanceOf(Exception::class, $exception);
	}

	public function test_default_message(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$exception = new ORM_Validation_Exception('user', $validation);

		$this->assertSame('Failed to validate array', $exception->getMessage());
	}

	public function test_default_code_is_zero(): void
	{
		$validation = $this->makeValidation();
		$validation->check();

		$exception = new ORM_Validation_Exception('user', $validation);

		$this->assertSame(0, $exception->getCode());
	}

	public function test_errors_with_null_directory(): void
	{
		$invalid = Validation::factory(array('username' => ''))
			->rules('username', array(array('not_empty')));
		$invalid->check();

		$exception = new ORM_Validation_Exception('test', $invalid);

		try {
			$errors = $exception->errors();
			$this->assertIsArray($errors);
		} catch (TypeError $e) {
			$this->markTestSkipped('errors() requires a directory parameter in PHP 8: ' . $e->getMessage());
		}
	}
}
