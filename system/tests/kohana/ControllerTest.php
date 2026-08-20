<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Controller
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.controller
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class TestController extends Controller
{
	public bool $before_called = false;
	public bool $after_called = false;
	public string $executed_action = '';

	public function action_index(): void
	{
		$this->executed_action = 'index';
		$this->response->body('index output');
	}

	public function action_custom(): void
	{
		$this->executed_action = 'custom';
		$this->response->body('custom output');
	}

	public function before(): void
	{
		parent::before();
		$this->before_called = true;
	}

	public function after(): void
	{
		parent::after();
		$this->after_called = true;
	}
}

#[AllowDynamicProperties]
class Kohana_ControllerTest extends Unittest_TestCase
{
	public function test_execute_calls_before_action_after(): void
	{
		$request = new Request('test');
		$request->action('index');
		$response = new Response();
		$controller = new TestController($request, $response);

		$result = $controller->execute();

		$this->assertTrue($controller->before_called);
		$this->assertSame('index', $controller->executed_action);
		$this->assertTrue($controller->after_called);
		$this->assertSame($response, $result);
		$this->assertSame('index output', $result->body());
	}

	public function test_execute_custom_action(): void
	{
		$request = new Request('test');
		$request->action('custom');
		$response = new Response();
		$controller = new TestController($request, $response);

		$result = $controller->execute();

		$this->assertSame('custom', $controller->executed_action);
		$this->assertSame('custom output', $result->body());
	}

	public function test_execute_throws_404_for_missing_action(): void
	{
		$request = new Request('test');
		$request->action('nonexistent');
		$response = new Response();
		$controller = new TestController($request, $response);

		$this->expectException(HTTP_Exception::class);
		$this->expectExceptionCode(404);
		$controller->execute();
	}

	public function test_request_and_response_are_accessible(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = new TestController($request, $response);

		$this->assertSame($request, $controller->request);
		$this->assertSame($response, $controller->response);
	}

	public function test_before_default_does_nothing(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = $this->getMockForAbstractClass(Controller::class, array($request, $response));

		$controller->before();
		$this->assertTrue(true);
	}

	public function test_after_default_does_nothing(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = $this->getMockForAbstractClass(Controller::class, array($request, $response));

		$controller->after();
		$this->assertTrue(true);
	}

	public function test_template_controller(): void
	{
		$request = new Request('test');
		$request->action('index');
		$response = new Response();
		$controller = new class ($request, $response) extends Controller_Template {
			public $auto_render = false;
			public function action_index(): void
			{
				$this->response->body('template output');
			}
		};

		$res = $controller->execute();
		$this->assertSame('template output', $res->body());
	}
}
