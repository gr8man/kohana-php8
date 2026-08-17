<?php

declare(strict_types=1);
use Kohana_Response as Response;

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for the Codebench controller.
 */
#[AllowDynamicProperties]
class Controller_CodebenchTest extends Unittest_TestCase
{
	// Load the controller class explicitly for the test environment
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../../classes/Controller/Codebench.php';
	}


	/**
	 * @test
	 * @covers Controller_Codebench::action_index
	 */
	public function test_redirect_when_class_posted(): void
	{
		// Set POST data for redirect test
		$_POST['class'] = 'DummyClass';
		// Mock request for redirect test
		$request = $this->createMock(Request::class);
		$request->expects($this->any())->method('param')->willReturn(null);

		$response = Response::factory();
		$controller = new Controller_Codebench($request, $response);
		$_SERVER['HTTP_HOST'] = 'example.com';
		try {
			$controller->action_index();
			$this->fail('Expected HTTP_Exception not thrown');
		} catch (HTTP_Exception $e) {
			$this->assertSame(302, $e->getCode());
			$this->assertStringContainsString('codebench/DummyClass', $e->headers('Location'));

		}
		unset($_POST['class']);
	}

	/**
	 * @test
	 * @covers Controller_Codebench::action_index
	 */
	public function test_successful_bench_execution(): void
	{
		// Define a dummy bench class that will be auto‑loaded (class_exists works after eval)
		$className = 'TestBench';
		if (!class_exists($className, false)) {
			eval('class ' . $className . ' { public function run() { return "result"; } }');
		}
		// Mock request to return the class name
		$request = $this->createMock(Request::class);
		$request->expects($this->any())->method('param')->willReturn('TestBench');
		$response = Response::factory();
		$controller = new Controller_Codebench($request, $response);
		$controller->auto_render = false;
		$controller->template = new stdClass();
		// Invoke the action
		$controller->action_index();
		$this->assertSame($className, $controller->template->class);
		$this->assertSame('result', $controller->template->codebench);
	}
	/**
	* @test
	* @covers Controller_Codebench::action_index
	*/
	public function test_no_post_and_no_class(): void
	{
		// No POST data, request param returns null
		$request = $this->createMock(Request::class);
		$request->expects($this->any())->method('param')->willReturn(null);
		$response = Response::factory();
		$controller = new Controller_Codebench($request, $response);
		$_SERVER['HTTP_HOST'] = 'example.com';
		$controller->auto_render = false;
		$controller->template = new stdClass();
		$controller->template->codebench = null;
		// Should not throw exception
		$controller->action_index();
		$this->assertSame('', $controller->template->class);
		$this->assertNull($controller->template->codebench);
	}

	/**
	* @test
	* @covers Controller_Codebench::action_index
	*/
	public function test_class_not_autoloaded(): void
	{
		// Provide a class name that does not exist
		$request = $this->createMock(Request::class);
		$request->expects($this->any())->method('param')->willReturn('NonExistentClass');
		$response = Response::factory();
		$controller = new Controller_Codebench($request, $response);
		$_SERVER['HTTP_HOST'] = 'example.com';
		$controller->auto_render = false;
		$controller->template = new stdClass();
		$controller->template->codebench = null;
		// Should not throw and template class set, but codebench remains null
		$controller->action_index();
		$this->assertSame('NonExistentClass', $controller->template->class);
		$this->assertNull($controller->template->codebench);
	}
}
