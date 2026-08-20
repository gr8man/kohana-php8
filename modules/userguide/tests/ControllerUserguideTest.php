<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Controller_Userguide
 *
 * @group kohana
 * @group kohana.userguide
 * @package    Kohana/Userguide
 * @category   Tests
 */
class Kohana_ControllerUserguideTest extends Unittest_TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$modules = Kohana::modules();
		if (!isset($modules['userguide'])) {
			$modules['userguide'] = MODPATH.'userguide'.DIRECTORY_SEPARATOR;
			Kohana::modules($modules);
		}
		if (!Route::get('docs/guide')) {
			Route::set('docs/guide', 'guide(/<module>(/<page>))')
				->defaults(array(
					'controller' => 'Userguide',
					'action'     => 'docs',
				));
		}
		if (!Route::get('docs/media')) {
			Route::set('docs/media', 'guide-media(/<file>)')
				->defaults(array(
					'controller' => 'Userguide',
					'action'     => 'media',
				));
		}
	}

	public function test_userguide_index(): void
	{
		$request = Request::factory('guide');
		$request->action('docs');
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->template = View::factory('userguide/template');
		$controller->before();
		$controller->action_docs();

		$this->assertSame('Userguide', $controller->template->title);
		$this->assertFalse($controller->template->show_comments);
	}

	public function test_userguide_disabled_module_error(): void
	{
		$request = Request::factory('guide/disabled_module_xyz');
		$request->action('docs');
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->template = View::factory('userguide/template');
		$controller->before();
		$controller->error('That module does not exist');

		$this->assertSame(404, $controller->response->status());
		$this->assertStringContainsString('Error', $controller->template->title);
	}

	public function test_userguide_media_missing_file(): void
	{
		$request = Request::factory('guide-media/nonexistent.png');
		$request->action('media');
		$ref_params = new ReflectionProperty($request, '_params');
		$ref_params->setValue($request, array('file' => 'nonexistent.png'));
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->before();
		$controller->action_media();

		$this->assertSame(404, $response->status());
	}

	public function test_userguide_media_existing_file(): void
	{
		$request = Request::factory('guide-media/css/kodoc.css');
		$request->action('media');
		$ref_params = new ReflectionProperty($request, '_params');
		$ref_params->setValue($request, array('file' => 'css/kodoc.css'));
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->before();
		$controller->action_media();

		$this->assertSame(200, $response->status());
		$this->assertNotEmpty($response->body());
	}

	public function test_userguide_file_and_titles(): void
	{
		$request = Request::factory('guide');
		$response = new Response();
		$controller = new Controller_Userguide($request, $response);

		$file = $controller->file('userguide/index');
		$this->assertTrue($file === false || is_string($file));

		$title = $controller->title('some/page');
		$this->assertSame('some/page', $title);

		$section = $controller->section('some/page');
		$this->assertSame('some/page', $section);
	}

	public function test_userguide_action_api_toc(): void
	{
		if (!Route::get('docs/api')) {
			Route::set('docs/api', 'guide-api(/<class>)')
				->defaults(array(
					'controller' => 'Userguide',
					'action'     => 'api',
				));
		}

		$request = Request::factory('guide-api');
		$request->action('api');
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->template = View::factory('userguide/template');
		$controller->before();
		$controller->action_api();

		$this->assertSame('Table of Contents', $controller->template->title);
	}

	public function test_userguide_action_docs_with_module(): void
	{
		Kohana::$config->load('userguide')->set('modules', array(
			'test_mod' => array(
				'enabled'   => true,
				'name'      => 'Test Module',
				'copyright' => '(c) 2026 Test',
			),
		));

		$request = Request::factory('guide/test_mod');
		$request->action('docs');
		$request->param('module', 'test_mod');
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->template = View::factory('userguide/template');
		$controller->before();
		$controller->action_docs();

		$this->assertNotEmpty($controller->template->title);
	}

	public function test_userguide_action_api_with_class(): void
	{
		$route = Route::get('docs/api');
		$request = Request::factory('guide-api/Route');
		$request->action('api');
		$ref_params = new ReflectionProperty($request, '_params');
		$ref_params->setValue($request, array('class' => 'Route'));
		$ref_route = new ReflectionProperty($request, '_route');
		$ref_route->setValue($request, $route);
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = false;
		$controller->template = View::factory('userguide/template');
		$controller->before();
		$controller->action_api();

		$this->assertSame('Route', $controller->template->title);
	}

	public function test_userguide_after_and_helpers(): void
	{
		$route = Route::get('docs/guide');
		$request = Request::factory('guide');
		$ref_route = new ReflectionProperty($request, '_route');
		$ref_route->setValue($request, $route);
		$response = new Response();

		$controller = new Controller_Userguide($request, $response);
		$controller->auto_render = true;
		$controller->before();
		$controller->index();
		$controller->after();

		$this->assertIsArray($controller->template->styles);
		$this->assertIsArray($controller->template->scripts);

		$ref_modules = new ReflectionMethod($controller, '_modules');
		$modules = $ref_modules->invoke($controller);
		$this->assertIsArray($modules);

		$ref_menu = new ReflectionMethod($controller, '_get_all_menu_markdown');
		$menu = $ref_menu->invoke($controller);
		$this->assertIsString($menu);
	}
}
