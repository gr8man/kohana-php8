<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Controller_Template
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
class TestTemplateController extends Controller_Template
{
	public string $action_executed = '';

	public function action_test(): void
	{
		$this->action_executed = 'test';
	}
}

#[AllowDynamicProperties]
class TestTemplateControllerNoAutoRender extends Controller_Template
{
	public string $action_executed = '';

	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);
		$this->auto_render = false;
	}

	public function action_test(): void
	{
		$this->action_executed = 'test';
	}
}

#[AllowDynamicProperties]
class Kohana_Controller_TemplateTest extends Unittest_TestCase
{
	public function test_auto_render_defaults_to_true(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = new TestTemplateController($request, $response);

		$this->assertTrue($controller->auto_render);
	}

	public function test_template_defaults(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = new TestTemplateController($request, $response);

		$this->assertSame('template', $controller->template);
	}

	public function test_before_with_auto_render_false_does_not_load_template(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = new TestTemplateControllerNoAutoRender($request, $response);

		$controller->before();
		$this->assertIsString($controller->template);
	}

	public function test_execute_with_auto_render_false(): void
	{
		$request = new Request('test');
		$request->action('test');
		$response = new Response();
		$controller = new TestTemplateControllerNoAutoRender($request, $response);

		$result = $controller->execute();

		$this->assertSame('test', $controller->action_executed);
		$this->assertSame($response, $result);
	}

	public function test_after_with_auto_render_false_does_not_set_body(): void
	{
		$request = new Request('test');
		$response = new Response();
		$controller = new TestTemplateControllerNoAutoRender($request, $response);

		$response->body('existing content');
		$controller->after();

		$this->assertSame('existing content', $response->body());
	}

	public function test_execute_throws_404_for_missing_action(): void
	{
		$request = new Request('test');
		$request->action('nonexistent');
		$response = new Response();
		$controller = new TestTemplateControllerNoAutoRender($request, $response);

		$this->expectException(HTTP_Exception::class);
		$this->expectExceptionCode(404);
		$controller->execute();
	}
}
