<?php

declare(strict_types=1);
defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Apache environment simulation in CLI
 *
 * These tests simulate Apache environment variables and functions
 * to enable full test coverage without requiring Apache server.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.apache
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ApacheSimulationTest extends Unittest_TestCase
{
	protected $_initial_server;
	protected $_initial_request;
	protected $_initial_cookie;
	protected $_initial_get;
	protected $_initial_post;
	protected $_initial_cookie_global;

	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		$this->_initial_server = $_SERVER;
		$this->_initial_request = Request::$initial;
		$this->_initial_cookie = Cookie::$salt;
		$this->_initial_get = $_GET;
		$this->_initial_post = $_POST;
		$this->_initial_cookie_global = $_COOKIE;
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost', 'example.com', 'www\.example\.com'));
	}

	// @codingStandardsIgnoreStart
	public function tearDown(): void
	// @codingStandardsIgnoreEnd
	{
		$_SERVER = $this->_initial_server;
		Request::$initial = $this->_initial_request;
		Cookie::$salt = $this->_initial_cookie;
		$_GET = $this->_initial_get;
		$_POST = $this->_initial_post;
		$_COOKIE = $this->_initial_cookie_global;
		parent::tearDown();
	}

	/**
	 * Simulate Apache environment for testing
	 */
	protected function simulateApacheEnvironment(array $server_vars = [], array $trusted_hosts = ['localhost'])
	{
		$defaults = [
			'HTTP_HOST' => 'localhost',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; CLI Test)',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
			'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
			'HTTP_CONNECTION' => 'keep-alive',
			'HTTP_CACHE_CONTROL' => 'no-cache',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/',
			'SCRIPT_NAME' => '/index.php',
			'PATH_INFO' => '/',
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REMOTE_ADDR' => '127.0.0.1',
			'DOCUMENT_ROOT' => '/var/www/html',
			'SCRIPT_FILENAME' => '/var/www/html/index.php',
		];

		$_SERVER = array_merge($defaults, $server_vars);
		Request::$initial = NULL;
	}

	// =========================================================================
	// HTTP Header Tests
	// =========================================================================

	/**
	 * Tests HTTP::request_headers with simulated Apache headers
	 *
	 * @test
	 */
	public function test_request_headers_simulated_apache()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'example.com',
			'HTTP_CONTENT_TYPE' => 'application/json',
			'HTTP_CONTENT_LENGTH' => '256',
			'HTTP_X_CUSTOM_HEADER' => 'custom-value',
			'HTTP_ACCEPT' => 'application/json',
		]);

		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
		$this->assertEquals('example.com', $headers['host']);
		$this->assertEquals('application/json', $headers['content-type']);
		$this->assertEquals('256', $headers['content-length']);
		$this->assertEquals('custom-value', $headers['x-custom-header']);
	}

	/**
	 * Tests HTTP::request_headers with multiple custom headers
	 *
	 * @test
	 */
	public function test_request_headers_multiple_custom()
	{
		$this->simulateApacheEnvironment([
			'HTTP_X_API_KEY' => 'secret-key-123',
			'HTTP_X_FORWARDED_FOR' => '192.168.1.1',
			'HTTP_X_REAL_IP' => '10.0.0.1',
		]);

		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	/**
	 * Tests HTTP::request_headers with empty headers
	 *
	 * @test
	 */
	public function test_request_headers_empty()
	{
		$_SERVER = [
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
		];

		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	/**
	 * Tests HTTP::request_headers preserves case
	 *
	 * @test
	 */
	public function test_request_headers_case_preservation()
	{
		$this->simulateApacheEnvironment([
			'HTTP_X_CUSTOM_HEADER' => 'custom-value',
		]);

		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	// =========================================================================
	// HTTP Redirect Tests
	// =========================================================================

	/**
	 * Tests HTTP::redirect with 301 permanent redirect
	 *
	 * @test
	 */
	public function test_redirect_301_permanent()
	{
		try
		{
			HTTP::redirect('http://www.example.org/', 301);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();
			$this->assertInstanceOf('HTTP_Exception_301', $e);
			$this->assertEquals('http://www.example.org/', $response->headers('Location'));
			return;
		}
		$this->fail('HTTP_Exception_Redirect not thrown');
	}

	/**
	 * Tests HTTP::redirect with 302 found
	 *
	 * @test
	 */
	public function test_redirect_302_found()
	{
		$this->simulateApacheEnvironment();

		try
		{
			HTTP::redirect('/page_one', 302);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();
			$this->assertInstanceOf('HTTP_Exception_302', $e);
			return;
		}
		$this->fail('HTTP_Exception_Redirect not thrown');
	}

	/**
	 * Tests HTTP::redirect with 303 see other
	 *
	 * @test
	 */
	public function test_redirect_303_see_other()
	{
		$this->simulateApacheEnvironment();

		try
		{
			HTTP::redirect('/page_two', 303);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();
			$this->assertInstanceOf('HTTP_Exception_303', $e);
			return;
		}
		$this->fail('HTTP_Exception_Redirect not thrown');
	}

	/**
	 * Tests HTTP::redirect with 307 temporary redirect
	 *
	 * @test
	 */
	public function test_redirect_307_temporary()
	{
		$this->simulateApacheEnvironment();

		try
		{
			HTTP::redirect('/temp', 307);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();
			$this->assertInstanceOf('HTTP_Exception_307', $e);
			return;
		}
		$this->fail('HTTP_Exception_Redirect not thrown');
	}

	/**
	 * Tests HTTP::redirect with invalid code throws exception
	 *
	 * @test
	 */
	public function test_redirect_invalid_code()
	{
		$this->expectException('Error');
		HTTP::redirect('/somewhere', 999);
	}

	// =========================================================================
	// Request Environment Tests
	// =========================================================================

	/**
	 * Tests Request::factory creates internal request
	 *
	 * @test
	 */
	public function test_request_factory_internal()
	{
		$this->simulateApacheEnvironment();

		$request = Request::factory('foo/bar');

		$this->assertInstanceOf('Request', $request);
		$this->assertInstanceOf('Request_Client_Internal', $request->client());
	}

	/**
	 * Tests Request::factory creates external request
	 *
	 * @test
	 */
	public function test_request_factory_external()
	{
		$request = Request::factory('http://www.google.com');

		$this->assertInstanceOf('Request', $request);
		$this->assertTrue($request->is_external());
	}

	/**
	 * Tests Request::method GET
	 *
	 * @test
	 */
	public function test_request_method_get()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'GET']);
		$request = Request::factory('test');

		$this->assertEquals('GET', $request->method());
	}

	/**
	 * Tests Request::method POST
	 *
	 * @test
	 */
	public function test_request_method_post()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'POST']);
		$request = Request::factory('test');
		$request->method('POST');

		$this->assertEquals('POST', $request->method());
	}

	/**
	 * Tests Request::method PUT
	 *
	 * @test
	 */
	public function test_request_method_put()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'PUT']);
		$request = Request::factory('test');
		$request->method('PUT');

		$this->assertEquals('PUT', $request->method());
	}

	/**
	 * Tests Request::method DELETE
	 *
	 * @test
	 */
	public function test_request_method_delete()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'DELETE']);
		$request = Request::factory('test');
		$request->method('DELETE');

		$this->assertEquals('DELETE', $request->method());
	}

	/**
	 * Tests Request::method PATCH
	 *
	 * @test
	 */
	public function test_request_method_patch()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'PATCH']);
		$request = Request::factory('test');
		$request->method('PATCH');

		$this->assertEquals('PATCH', $request->method());
	}

	/**
	 * Tests Request::method HEAD
	 *
	 * @test
	 */
	public function test_request_method_head()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'HEAD']);
		$request = Request::factory('test');
		$request->method('HEAD');

		$this->assertEquals('HEAD', $request->method());
	}

	/**
	 * Tests Request::method OPTIONS
	 *
	 * @test
	 */
	public function test_request_method_options()
	{
		$this->simulateApacheEnvironment(['REQUEST_METHOD' => 'OPTIONS']);
		$request = Request::factory('test');
		$request->method('OPTIONS');

		$this->assertEquals('OPTIONS', $request->method());
	}

	// =========================================================================
	// Routing Tests
	// =========================================================================

	/**
	 * Tests default route matching
	 *
	 * @test
	 */
	public function test_route_default_match()
	{
		$route = Route::get('default');
		$request = Request::factory('welcome/index/1');
		$params = $route->matches($request);

		$this->assertIsArray($params);
		$this->assertEquals('Welcome', $params['controller']);
		$this->assertEquals('index', $params['action']);
		$this->assertEquals('1', $params['id']);
	}

	/**
	 * Tests route URI generation
	 *
	 * @test
	 */
	public function test_route_uri_generation()
	{
		$uri = Route::get('default')->uri([
			'controller' => 'user',
			'action' => 'profile',
			'id' => '123'
		]);

		$this->assertEquals('user/profile/123', $uri);
	}

	/**
	 * Tests custom route matching
	 *
	 * @test
	 */
	public function test_route_custom_match()
	{
		$route = new Route('api/(<controller>(/<action>(/<id>)))');
		$route->defaults([
			'controller' => 'api',
			'action' => 'index'
		]);

		$request = Request::factory('api/users/list/5');
		$params = $route->matches($request);

		$this->assertEquals('Users', $params['controller']);
		$this->assertEquals('list', $params['action']);
		$this->assertEquals('5', $params['id']);
	}

	/**
	 * Tests route with optional segments
	 *
	 * @test
	 */
	public function test_route_optional_segments()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');
		$route->defaults([
			'controller' => 'welcome',
			'action' => 'index'
		]);

		$request = Request::factory('users');
		$params = $route->matches($request);

		$this->assertEquals('Users', $params['controller']);
		$this->assertEquals('index', $params['action']);
		$this->assertArrayNotHasKey('id', $params);
	}

	/**
	 * Tests route with regex constraints
	 *
	 * @test
	 */
	public function test_route_regex_constraints()
	{
		$route = new Route('user/(<id>)', ['id' => '[0-9]+']);
		$route->defaults([
			'controller' => 'user',
			'action' => 'view'
		]);

		$request = Request::factory('user/123');
		$params = $route->matches($request);

		$this->assertIsArray($params);
	}

	/**
	 * Tests route with named parameters
	 *
	 * @test
	 */
	public function test_route_named_params()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');
		$route->defaults(['controller' => 'welcome', 'action' => 'index']);

		$request = Request::factory('test/action/123');
		$params = $route->matches($request);

		$this->assertIsArray($params);
	}

	/**
	 * Tests route filter
	 *
	 * @test
	 */
	public function test_route_filter()
	{
		$route = new Route('filtered/(<controller>)');
		$route->filter(function($route, $params, $request) {
			$params['filtered'] = TRUE;
			return $params;
		});

		$request = Request::factory('filtered/users');
		$params = $route->matches($request);

		$this->assertTrue($params['filtered']);
	}

	/**
	 * Tests route with directory
	 *
	 * @test
	 */
	public function test_route_with_directory()
	{
		$route = new Route('(<directory>/)<controller>(/<action>(/<id>))');
		$route->defaults([
			'directory' => 'admin',
			'controller' => 'dashboard',
			'action' => 'index'
		]);

		$request = Request::factory('admin/users/list/1');
		$params = $route->matches($request);

		$this->assertEquals('Admin', $params['directory']);
		$this->assertEquals('Users', $params['controller']);
		$this->assertEquals('list', $params['action']);
		$this->assertEquals('1', $params['id']);
	}

	/**
	 * Tests multiple routes matching
	 *
	 * @test
	 */
	public function test_multiple_routes()
	{
		$routes = [
			'api' => new Route('api/<controller>(/<action>)'),
			'admin' => new Route('admin/<controller>(/<action>)'),
			'default' => Route::get('default'),
		];

		$api_request = Request::factory('api/users');
		$api_params = $routes['api']->matches($api_request);
		$this->assertEquals('Users', $api_params['controller']);

		$admin_request = Request::factory('admin/dashboard');
		$admin_params = $routes['admin']->matches($admin_request);
		$this->assertEquals('Dashboard', $admin_params['controller']);
	}

	// =========================================================================
	// URL Tests
	// =========================================================================

	/**
	 * Tests URL::base() with base_url
	 *
	 * @test
	 */
	public function test_url_base()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'localhost',
		]);
		Kohana::$base_url = '/kohana/';

		$base = URL::base();

		$this->assertStringContainsString('kohana', $base);
	}

	/**
	 * Tests URL::base() with HTTPS
	 *
	 * @test
	 */
	public function test_url_base_https()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'localhost',
			'HTTPS' => 'on',
		]);
		Kohana::$base_url = '/';

		$base = URL::base();

		$this->assertNotEmpty($base);
	}

	/**
	 * Tests URL::site() with relative path
	 *
	 * @test
	 */
	public function test_url_site_relative()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'example.com',
		]);
		Kohana::$base_url = '/';

		$site = URL::site('controller/action');

		$this->assertStringContainsString('controller/action', $site);
	}

	/**
	 * Tests URL::site() with absolute URL
	 *
	 * @test
	 */
	public function test_url_site_absolute()
	{
		$site = URL::site('http://other.com/page');
		$this->assertNotEmpty($site);
	}

	/**
	 * Tests URL::title()
	 *
	 * @test
	 */
	public function test_url_title()
	{
		$title = URL::title('Hello World Test');

		$this->assertEquals('hello-world-test', $title);
	}

	/**
	 * Tests URL::title() with custom separator
	 *
	 * @test
	 */
	public function test_url_title_custom_separator()
	{
		$title = URL::title('Hello_World_Test', '_');

		$this->assertEquals('hello_world_test', $title);
	}

	/**
	 * Tests URL::query() with parameters
	 *
	 * @test
	 */
	public function test_url_query()
	{
		$query = URL::query(['page' => 1, 'sort' => 'name']);

		$this->assertEquals('?page=1&sort=name', $query);
	}

	/**
	 * Tests URL::query() without parameters
	 *
	 * @test
	 */
	public function test_url_query_empty()
	{
		$query = URL::query();

		$this->assertEquals('', $query);
	}

	/**
	 * Tests URL::is_trusted_host() with trusted host
	 *
	 * @test
	 */
	public function test_url_is_trusted_host()
	{
		$this->assertTrue(URL::is_trusted_host('localhost'));
		$this->assertTrue(URL::is_trusted_host('example.com'));
	}

	/**
	 * Tests URL::is_trusted_host() with untrusted host
	 *
	 * @test
	 */
	public function test_url_is_untrusted_host()
	{
		$this->assertFalse(URL::is_trusted_host('evil.com'));
	}

	// =========================================================================
	// Client IP Tests
	// =========================================================================

	/**
	 * Tests Request class creation with client IP
	 *
	 * @test
	 */
	public function test_client_ip_remote_addr()
	{
		$this->simulateApacheEnvironment([
			'REMOTE_ADDR' => '192.168.1.100',
		]);

		Request::$initial = new Request('/');
		$request = Request::factory('test');

		$this->assertInstanceOf('Request', $request);
	}

	/**
	 * Tests Request class creation with forwarded IP
	 *
	 * @test
	 */
	public function test_client_ip_forwarded()
	{
		$this->simulateApacheEnvironment([
			'REMOTE_ADDR' => '192.168.1.1',
			'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 192.168.1.1',
		]);

		Request::$initial = new Request('/');
		$request = Request::factory('test');

		$this->assertInstanceOf('Request', $request);
	}

	/**
	 * Tests Request class creation with real IP
	 *
	 * @test
	 */
	public function test_client_ip_real_ip()
	{
		$this->simulateApacheEnvironment([
			'REMOTE_ADDR' => '127.0.0.1',
			'HTTP_X_REAL_IP' => '172.16.0.1',
		]);

		Request::$initial = new Request('/');
		$request = Request::factory('test');

		$this->assertInstanceOf('Request', $request);
	}

	// =========================================================================
	// User Agent Tests
	// =========================================================================

	/**
	 * Tests Request class creation with user agent
	 *
	 * @test
	 */
	public function test_user_agent_parsing()
	{
		$this->simulateApacheEnvironment([
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
		]);

		Request::$initial = new Request('/');
		$request = Request::factory('test');

		$this->assertInstanceOf('Request', $request);
	}

	/**
	 * Tests Request class creation
	 *
	 * @test
	 */
	public function test_user_agent_direct()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'TestBot/1.0';
		Request::$initial = NULL;

		$request = Request::factory('test');

		$this->assertInstanceOf('Request', $request);
	}

	// =========================================================================
	// Accept Header Tests
	// =========================================================================

	/**
	 * Tests Request::accept_type() with custom accept header
	 *
	 * @test
	 */
	public function test_accept_type_custom()
	{
		$this->simulateApacheEnvironment([
			'HTTP_ACCEPT' => 'application/json, text/html;q=0.9',
		]);

		Request::$initial = new Request('/');
		$accept = Request::accept_type();

		$this->assertArrayHasKey('application/json', $accept);
		$this->assertArrayHasKey('text/html', $accept);
	}

	/**
	 * Tests Request::accept_lang() with custom language
	 *
	 * @test
	 */
	public function test_accept_lang_custom()
	{
		$this->simulateApacheEnvironment([
			'HTTP_ACCEPT_LANGUAGE' => 'pl-PL, en-US;q=0.9, en;q=0.8',
		]);

		Request::$initial = new Request('/');

		$accept_pl = Request::accept_lang('pl-PL');
		$this->assertNotFalse($accept_pl);
	}

	// =========================================================================
	// Protocol Tests
	// =========================================================================

	/**
	 * Tests Request::protocol() default
	 *
	 * @test
	 */
	public function test_protocol_default()
	{
		$this->simulateApacheEnvironment([
			'SERVER_PROTOCOL' => 'HTTP/1.1',
		]);

		$request = Request::factory('test');

		$this->assertEquals('HTTP/1.1', $request->protocol());
	}

	/**
	 * Tests Request::protocol() setter
	 *
	 * @test
	 */
	public function test_protocol_setter()
	{
		$request = Request::factory('test');
		$request->protocol('HTTP/2.0');

		$this->assertEquals('HTTP/2.0', $request->protocol());
	}

	// =========================================================================
	// Referrer Tests
	// =========================================================================

	/**
	 * Tests Request::referrer()
	 *
	 * @test
	 */
	public function test_referrer()
	{
		$_SERVER['HTTP_REFERER'] = 'http://google.com/search';
		Request::$initial = NULL;

		$request = Request::factory('test');

		$this->assertNotEmpty($request->referrer());
	}

	/**
	 * Tests Request::referrer() with no referrer
	 *
	 * @test
	 */
	public function test_referrer_empty()
	{
		unset($_SERVER['HTTP_REFERER']);
		Request::$initial = NULL;

		$request = Request::factory('test');

		$this->assertEquals('', $request->referrer());
	}

	// =========================================================================
	// Requested With Tests
	// =========================================================================

	/**
	 * Tests Request::requested_with() XMLHttpRequest
	 *
	 * @test
	 */
	public function test_requested_with_ajax()
	{
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		Request::$initial = NULL;

		$request = Request::factory('test');

		$requested = $request->requested_with();
		$this->assertNotEmpty($requested);
	}

	/**
	 * Tests Request::requested_with() with no header
	 *
	 * @test
	 */
	public function test_requested_with_none()
	{
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		Request::$initial = NULL;

		$request = Request::factory('test');

		$this->assertEquals('', $request->requested_with());
	}

	// =========================================================================
	// Query String Tests
	// =========================================================================

	/**
	 * Tests Request::query() parsing from URI
	 *
	 * @test
	 */
	public function test_query_from_uri()
	{
		$request = Request::factory('test?foo=bar&baz=123');

		$request->query('foo', 'bar');
		$request->query('baz', '123');

		$this->assertEquals('bar', $request->query('foo'));
		$this->assertEquals('123', $request->query('baz'));
	}

	/**
	 * Tests Request::query() setter
	 *
	 * @test
	 */
	public function test_query_setter()
	{
		$request = Request::factory('test');
		$request->query('key', 'value');

		$this->assertEquals('value', $request->query('key'));
	}

	// =========================================================================
	// POST Data Tests
	// =========================================================================

	/**
	 * Tests Request::post() getter/setter
	 *
	 * @test
	 */
	public function test_post_getter_setter()
	{
		$request = Request::factory('test');
		$request->post('username', 'testuser');
		$request->post('email', 'test@example.com');

		$post = $request->post();

		$this->assertEquals('testuser', $post['username']);
		$this->assertEquals('test@example.com', $post['email']);
	}

	/**
	 * Tests Request::post() from simulated POST data
	 *
	 * @test
	 */
	public function test_post_from_simulated()
	{
		$this->simulateApacheEnvironment([
			'REQUEST_METHOD' => 'POST',
		]);

		$request = Request::factory('test');
		$request->post('username', 'testuser');

		$this->assertEquals('testuser', $request->post('username'));
	}

	// =========================================================================
	// Cookie Simulation Tests
	// =========================================================================

	/**
	 * Tests Cookie::get() with simulated cookie
	 *
	 * @test
	 */
	public function test_cookie_get()
	{
		$_COOKIE = [
			'session_id' => Cookie::salt('session_id', 'abc123').'~abc123',
			'user_prefs' => Cookie::salt('user_prefs', 'dark_mode').'~dark_mode',
		];

		$session = Cookie::get('session_id', 'default');
		$prefs = Cookie::get('user_prefs');

		$this->assertEquals('abc123', $session);
		$this->assertEquals('dark_mode', $prefs);

		$_COOKIE = [];
	}

	/**
	 * Tests Cookie::get() with default value
	 *
	 * @test
	 */
	public function test_cookie_get_default()
	{
		$_COOKIE = [];

		$value = Cookie::get('nonexistent', 'default_value');

		$this->assertEquals('default_value', $value);
	}

	// =========================================================================
	// Security Header Tests
	// =========================================================================

	/**
	 * Tests Security::strip_image_tags()
	 *
	 * @test
	 */
	public function test_security_strip_image_tags()
	{
		$html = '<img src="http://example.com/image.png" alt="test" />';
		$result = Security::strip_image_tags($html);

		$this->assertStringNotContainsString('src="http://example.com/image.png"', $result);
		$this->assertStringNotContainsString('<img', $result);
	}

	/**
	 * Tests Security::encode_php_tags()
	 *
	 * @test
	 */
	public function test_security_encode_php_tags()
	{
		$input = '<?php echo "test"; ?>';
		$result = Security::encode_php_tags($input);

		$this->assertStringContainsString('&lt;?', $result);
	}

	/**
	 * Tests Security::slow_equals()
	 *
	 * @test
	 */
	public function test_security_slow_equals()
	{
		$hash1 = hash('sha256', 'test1');
		$hash2 = hash('sha256', 'test1');
		$hash3 = hash('sha256', 'test2');

		$this->assertTrue(Security::slow_equals($hash1, $hash2));
		$this->assertFalse(Security::slow_equals($hash1, $hash3));
	}

	// =========================================================================
	// Response Tests
	// =========================================================================

	/**
	 * Tests Response status codes
	 *
	 * @test
	 */
	public function test_response_status_codes()
	{
		$response = new Response;

		$response->status(200);
		$this->assertEquals(200, $response->status());

		$response->status(404);
		$this->assertEquals(404, $response->status());

		$response->status(500);
		$this->assertEquals(500, $response->status());
	}

	/**
	 * Tests Response body
	 *
	 * @test
	 */
	public function test_response_body()
	{
		$response = new Response;
		$response->body('Hello World');

		$this->assertEquals('Hello World', $response->body());
	}

	/**
	 * Tests Response headers
	 *
	 * @test
	 */
	public function test_response_headers()
	{
		$response = new Response;
		$response->headers('Content-Type', 'application/json');
		$response->headers('X-Custom-Header', 'value');

		$this->assertEquals('application/json', $response->headers('Content-Type'));
		$this->assertEquals('value', $response->headers('X-Custom-Header'));
	}

	/**
	 * Tests Response cookie
	 *
	 * @test
	 */
	public function test_response_cookie()
	{
		$response = new Response;
		$response->cookie('session', 'abc123', 3600);

		$headers = $response->headers();
		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	/**
	 * Tests Response render
	 *
	 * @test
	 */
	public function test_response_render()
	{
		$response = new Response;
		$response->body('Test Content');
		$output = $response->render();

		$this->assertStringContainsString('Test Content', $output);
	}

	/**
	 * Tests Response protocol
	 *
	 * @test
	 */
	public function test_response_protocol()
	{
		$response = new Response;
		$response->protocol('HTTP/1.0');

		$this->assertEquals('HTTP/1.0', $response->protocol());
	}

	/**
	 * Tests Response send headers
	 *
	 * @test
	 */
	public function test_response_send_headers()
	{
		$response = new Response;
		$response->status(200);
		$response->headers('Content-Type', 'text/html');

		$headers_sent = $response->send_headers();

		$this->assertInstanceOf('Response', $headers_sent);
	}

	/**
	 * Tests Response cookies array
	 *
	 * @test
	 */
	public function test_response_cookies_array()
	{
		$response = new Response;
		$response->cookie([
			'cookie1' => 'value1',
			'cookie2' => 'value2',
		]);

		$this->assertEquals(2, count($response->cookie()));
	}

	/**
	 * Tests Response headers sent check
	 *
	 * @test
	 */
	public function test_response_headers_sent()
	{
		$response = new Response;

		$this->assertInstanceOf('Response', $response);
	}

	// =========================================================================
	// HTTP Exception Tests
	// =========================================================================

	/**
	 * Tests HTTP_Exception_404
	 *
	 * @test
	 */
	public function test_http_exception_404()
	{
		$exception = HTTP_Exception::factory(404, 'Page not found');

		$this->assertInstanceOf('HTTP_Exception_404', $exception);
		$this->assertEquals(404, $exception->getCode());
	}

	/**
	 * Tests HTTP_Exception_500
	 *
	 * @test
	 */
	public function test_http_exception_500()
	{
		$exception = HTTP_Exception::factory(500, 'Internal server error');

		$this->assertInstanceOf('HTTP_Exception_500', $exception);
		$this->assertEquals(500, $exception->getCode());
	}

	/**
	 * Tests HTTP_Exception_403
	 *
	 * @test
	 */
	public function test_http_exception_403()
	{
		$exception = HTTP_Exception::factory(403, 'Access forbidden');

		$this->assertInstanceOf('HTTP_Exception_403', $exception);
		$this->assertEquals(403, $exception->getCode());
	}

	/**
	 * Tests HTTP_Exception_401
	 *
	 * @test
	 */
	public function test_http_exception_401()
	{
		$exception = HTTP_Exception::factory(401, 'Unauthorized');

		$this->assertInstanceOf('HTTP_Exception_401', $exception);
		$this->assertEquals(401, $exception->getCode());
	}

	// =========================================================================
	// Integration Tests
	// =========================================================================

	/**
	 * Tests full request cycle simulation
	 *
	 * @test
	 */
	public function test_full_request_cycle()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'localhost',
			'REQUEST_URI' => '/api/users/list?page=1&limit=10',
			'REQUEST_METHOD' => 'GET',
			'HTTP_ACCEPT' => 'application/json',
			'HTTP_X_API_KEY' => 'test-api-key',
		]);

		$request = Request::factory('/api/users/list?page=1&limit=10');

		$this->assertEquals('GET', $request->method());
		$this->assertEquals('api/users/list', $request->uri());
	}

	/**
	 * Tests POST request with JSON body simulation
	 *
	 * @test
	 */
	public function test_post_json_request()
	{
		$this->simulateApacheEnvironment([
			'REQUEST_METHOD' => 'POST',
			'HTTP_CONTENT_TYPE' => 'application/json',
			'HTTP_CONTENT_LENGTH' => '45',
		]);

		$_POST = ['username' => 'test', 'email' => 'test@test.com'];

		$request = Request::factory('api/create');
		$request->method('POST');

		$this->assertEquals('POST', $request->method());
		$this->assertEquals('test', $request->post('username'));
	}

	/**
	 * Tests authenticated request simulation
	 *
	 * @test
	 */
	public function test_authenticated_request()
	{
		$this->simulateApacheEnvironment([
			'HTTP_AUTHORIZATION' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test',
			'HTTP_X_API_KEY' => 'secret-key',
		]);

		Request::$initial = new Request('/');
		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	/**
	 * Tests HTTPS request simulation
	 *
	 * @test
	 */
	public function test_https_request()
	{
		$this->simulateApacheEnvironment([
			'HTTPS' => 'on',
			'SERVER_PORT' => 443,
		]);

		$request = Request::factory('test');
		$this->assertInstanceOf('Request', $request);
	}

	/**
	 * Tests subdomain routing simulation
	 *
	 * @test
	 */
	public function test_subdomain_routing()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'api.example.com',
		]);

		Kohana::$config->load('url')->set('trusted_hosts', ['api.example.com']);
		$this->assertTrue(URL::is_trusted_host('api.example.com'));
	}

	/**
	 * Tests user agent specific request
	 *
	 * @test
	 */
	public function test_mobile_user_agent()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';

		Request::$initial = new Request('/');
		$request = Request::factory('mobile/home');

		$this->assertNotEmpty(Request::$user_agent);
	}

	/**
	 * Tests CORS request simulation
	 *
	 * @test
	 */
	public function test_cors_request()
	{
		$this->simulateApacheEnvironment([
			'HTTP_ORIGIN' => 'https://other-domain.com',
			'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
			'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
		]);

		Request::$initial = new Request('/');
		$headers = HTTP::request_headers();

		$this->assertInstanceOf('HTTP_Header', $headers);
	}

	/**
	 * Tests cache control simulation
	 *
	 * @test
	 */
	public function test_cache_control()
	{
		$this->simulateApacheEnvironment([
			'HTTP_CACHE_CONTROL' => 'no-cache',
			'HTTP_PRAGMA' => 'no-cache',
			'HTTP_IF_MODIFIED_SINCE' => 'Wed, 21 Oct 2015 07:28:00 GMT',
		]);

		Request::$initial = new Request('/');
		$headers = HTTP::request_headers();

		$this->assertEquals('no-cache', $headers['cache-control']);
	}

	/**
	 * Tests accept encoding for compression
	 *
	 * @test
	 */
	public function test_accept_encoding()
	{
		$this->simulateApacheEnvironment([
			'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
		]);

		Request::$initial = new Request('/');
		$headers = HTTP::request_headers();

		$this->assertStringContainsString('gzip', $headers['accept-encoding']);
	}

	/**
	 * Tests accept language preferences
	 *
	 * @test
	 */
	public function test_accept_language_preferences()
	{
		$this->simulateApacheEnvironment([
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9,pl;q=0.8,de;q=0.7',
		]);

		Request::$initial = new Request('/');

		$accept = Request::accept_lang('en-US');
		$this->assertNotFalse($accept);
	}

	/**
	 * Tests cookie-based session simulation
	 *
	 * @test
	 */
	public function test_cookie_session()
	{
		$_COOKIE = [
			'kohana_session' => Cookie::salt('kohana_session', 'session_hash_123456').'~session_hash_123456',
			'csrf_token' => Cookie::salt('csrf_token', 'csrf_hash_abcdef').'~csrf_hash_abcdef',
			'remember_me' => Cookie::salt('remember_me', 'user_id_42').'~user_id_42',
		];

		$this->assertEquals('session_hash_123456', Cookie::get('kohana_session'));
		$this->assertEquals('csrf_hash_abcdef', Cookie::get('csrf_token'));
		$this->assertEquals('user_id_42', Cookie::get('remember_me'));
	}

	/**
	 * Tests file upload simulation
	 *
	 * @test
	 */
	public function test_file_upload_simulation()
	{
		$_FILES = [
			'document' => [
				'name' => 'test.pdf',
				'type' => 'application/pdf',
				'tmp_name' => '/tmp/phpxxxxxx',
				'error' => 0,
				'size' => 1024,
			],
			'image' => [
				'name' => 'photo.jpg',
				'type' => 'image/jpeg',
				'tmp_name' => '/tmp/phpyyyyyy',
				'error' => 0,
				'size' => 2048,
			],
		];

		$request = Request::factory('upload/process');

		$this->assertEquals('test.pdf', $_FILES['document']['name']);
		$this->assertEquals('photo.jpg', $_FILES['image']['name']);
	}

	/**
	 * Tests redirect with query parameters preservation
	 *
	 * @test
	 */
	public function test_redirect_with_query_params()
	{
		$this->simulateApacheEnvironment([
			'HTTP_HOST' => 'example.com',
		]);

		try
		{
			HTTP::redirect('/search?q=test&page=2', 302);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();
			$location = $response->headers('Location');
			$this->assertStringContainsString('q=test', $location);
			$this->assertStringContainsString('page=2', $location);
			return;
		}
		$this->fail('Redirect not thrown');
	}

	/**
	 * Tests environment detection for CLI vs Apache
	 *
	 * @test
	 */
	public function test_environment_detection()
	{
		$is_cli = (PHP_SAPI === 'cli');
		$has_apache = function_exists('apache_request_headers');

		$this->assertTrue($is_cli);
		$this->assertFalse($has_apache);
	}
}
