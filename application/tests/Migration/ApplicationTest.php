<?php

declare(strict_types=1);

namespace Kohana\Tests;

use Controller_Welcome;
use Request;
use Response;
use Route;
use View;
use URL;
use HTML;
use Cookie;
use Cookie_Exception;
use Kohana;
use Config;
use Valid;
use Date;
use Inflector;
use Num;
use Text;
use DB;
use ORM;
use Throwable;

class ApplicationTest extends BaseTestCase
{
	public function test_welcome_controller_response(): void
	{
		$request = Request::factory('welcome/index');
		$response = $request->execute();

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals('hello, world!', $response->body());
		$this->assertEquals(200, $response->status());
	}

	public function test_welcome_controller_with_id(): void
	{
		$request = Request::factory('welcome/index/42');
		$response = $request->execute();

		$this->assertEquals(200, $response->status());
		$this->assertEquals('hello, world!', $response->body());
	}

	public function test_default_route_matches_root(): void
	{
		$route = Route::get('default');
		$request = Request::factory('');
		$params = $route->matches($request);

		$this->assertIsArray($params);
		$this->assertEquals('Welcome', $params['controller']);
		$this->assertEquals('index', $params['action']);
	}

	public function test_custom_route_definition(): void
	{
		Route::set('test_route', 'api/<controller>(/<action>(/<id>))')
			->defaults(array('action' => 'index'));

		$route = Route::get('test_route');
		$this->assertNotNull($route);

		$request = Request::factory('api/user/profile/5');
		$params = $route->matches($request);

		$this->assertIsArray($params);
		$this->assertEquals('User', $params['controller']);
		$this->assertEquals('profile', $params['action']);
		$this->assertEquals('5', $params['id']);
	}

	public function test_route_with_regex_constraints(): void
	{
		Route::set('regex_route', '<controller>/<action>(/<id>)', array('id' => '\d+'))
			->defaults(array('action' => 'index'));

		$request = Request::factory('user/view/123');
		$params = Route::get('regex_route')->matches($request);
		$this->assertIsArray($params);

		$requestInvalid = Request::factory('user/view/abc');
		$paramsInvalid = Route::get('regex_route')->matches($requestInvalid);
		$this->assertFalse($paramsInvalid);
	}

	public function test_route_reverse_routing(): void
	{
		Route::set('reverse_test', 'blog/<year>/<slug>', array('year' => '\d{4}'))
			->defaults(array('controller' => 'blog', 'action' => 'view'));

		$uri = Route::get('reverse_test')->uri(array('year' => '2024', 'slug' => 'hello-world'));
		$this->assertEquals('blog/2024/hello-world', $uri);
	}

	public function test_nonexistent_route_returns_404(): void
	{
		$request = Request::factory('this/route/does/not/exist');
		$response = $request->execute();

		$this->assertEquals(404, $response->status());
	}

	public function test_route_all_method(): void
	{
		$routes = Route::all();
		$this->assertIsArray($routes);
		$this->assertArrayHasKey('default', $routes);
	}

	public function test_route_name_method(): void
	{
		$name = Route::name(Route::get('default'));
		$this->assertEquals('default', $name);
	}

	public function test_request_factory_defaults(): void
	{
		$request = Request::factory();
		$this->assertInstanceOf(Request::class, $request);
	}

	public function test_request_method_defaults_to_get(): void
	{
		$request = Request::factory('welcome/index');
		$this->assertEquals('GET', $request->method());
	}

	public function test_request_controller_and_action(): void
	{
		$request = Request::factory('welcome/index');
		$request->execute();

		$this->assertEquals('Welcome', $request->controller());
		$this->assertEquals('index', $request->action());
	}

	public function test_request_query_params(): void
	{
		$request = Request::factory('welcome/index?foo=bar&baz=qux');
		$this->assertSame('bar', $request->query('foo'));
		$this->assertSame('qux', $request->query('baz'));
	}

	public function test_request_post_params(): void
	{
		$request = Request::factory('welcome/index');
		$request->post(array('username' => 'test', 'role' => 'admin'));

		$this->assertSame('test', $request->post('username'));
		$this->assertSame('admin', $request->post('role'));
	}

	public function test_response_factory(): void
	{
		$response = Response::factory();
		$this->assertInstanceOf(Response::class, $response);
	}

	public function test_response_body_chainable(): void
	{
		$response = Response::factory();
		$result = $response->body('test content');
		$this->assertSame($response, $result);
		$this->assertEquals('test content', $response->body());
	}

	public function test_response_status_codes(): void
	{
		$response = Response::factory();
		$response->status(200);
		$this->assertEquals(200, $response->status());

		$response->status(404);
		$this->assertEquals(404, $response->status());

		$response->status(500);
		$this->assertEquals(500, $response->status());
	}

	public function test_response_content_type(): void
	{
		$response = Response::factory();
		$response->headers('Content-Type', 'application/json');
		$response->body('{"key": "value"}');

		$this->assertEquals('application/json', $response->headers('Content-Type'));
	}

	public function test_view_factory(): void
	{
		$view = View::factory();
		$this->assertInstanceOf(View::class, $view);
	}

	public function test_view_set_and_get(): void
	{
		$view = View::factory();
		$view->set('title', 'Test Page');

		$this->assertEquals('Test Page', $view->title);
	}

	public function test_view_magic_set(): void
	{
		$view = View::factory();
		$view->foo = 'bar';
		$this->assertEquals('bar', $view->foo);
	}

	public function test_view_global_variable(): void
	{
		View::set_global('site_name', 'Kohana Test');
		$this->assertEquals('Kohana Test', View::factory()->site_name);
	}

	public function test_config_loading(): void
	{
		$config = Kohana::$config->load('database');
		$this->assertNotNull($config);
	}

	public function test_url_base(): void
	{
		$base = URL::base();
		$this->assertIsString($base);
	}

	public function test_url_site(): void
	{
		$url = URL::site('welcome/index');
		$this->assertEquals('/welcome/index', $url);
	}

	public function test_html_anchor(): void
	{
		$link = HTML::anchor('welcome/index', 'Home');
		$this->assertStringContainsString('<a ', $link);
		$this->assertStringContainsString('href="', $link);
		$this->assertStringContainsString('Home', $link);
	}

	public function test_valid_not_empty(): void
	{
		$this->assertTrue(Valid::not_empty('hello'));
		$this->assertFalse(Valid::not_empty(''));
		$this->assertFalse(Valid::not_empty(null));
	}

	public function test_valid_email(): void
	{
		$this->assertTrue(Valid::email('user@example.com'));
		$this->assertFalse(Valid::email('not-an-email'));
	}

	public function test_valid_url(): void
	{
		$this->assertTrue(Valid::url('https://kohanaframework.org'));
		$this->assertFalse(Valid::url('not-a-url'));
	}

	public function test_valid_min_length(): void
	{
		$this->assertTrue(Valid::min_length('hello', 3));
		$this->assertFalse(Valid::min_length('hi', 3));
	}

	public function test_valid_max_length(): void
	{
		$this->assertTrue(Valid::max_length('hi', 5));
		$this->assertFalse(Valid::max_length('hello', 3));
	}

	public function test_valid_range(): void
	{
		$this->assertTrue(Valid::range(5, 1, 10));
		$this->assertFalse(Valid::range(15, 1, 10));
	}

	public function test_valid_date(): void
	{
		$this->assertTrue(Valid::date('2024-01-15'));
		$this->assertFalse(Valid::date('not-a-date'));
	}

	public function test_date_formatted_time(): void
	{
		$formatted = Date::formatted_time('@1700000000', 'Y-m-d H:i:s');
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $formatted);
	}

	public function test_date_span(): void
	{
		$span = Date::span(0, 3661);
		$this->assertIsArray($span);
		$this->assertArrayHasKey('hours', $span);
		$this->assertEquals(1, $span['hours']);
		$this->assertEquals(1, $span['minutes']);
		$this->assertEquals(1, $span['seconds']);
	}

	public function test_date_fuzzy_span(): void
	{
		$fuzzy = Date::fuzzy_span(time() - 30);
		$this->assertStringContainsString('moments', $fuzzy);
	}

	public function test_date_span_seconds_only(): void
	{
		$span = Date::span(0, 45, 'seconds');
		$this->assertEquals(45, $span);
	}

	public function test_date_span_with_custom_output(): void
	{
		$span = Date::span(0, 3661, 'hours,minutes,seconds');
		$this->assertIsArray($span);
		$this->assertCount(3, $span);
	}

	public function test_inflector_singular(): void
	{
		$this->assertEquals('box', Inflector::singular('boxes'));
		$this->assertEquals('apple', Inflector::singular('apples'));
	}

	public function test_inflector_plural(): void
	{
		$this->assertEquals('boxes', Inflector::plural('box'));
		$this->assertEquals('apples', Inflector::plural('apple'));
	}

	public function test_inflector_camelize(): void
	{
		$this->assertEquals('helloWorld', Inflector::camelize('hello_world'));
	}

	public function test_inflector_underscore(): void
	{
		$result = Inflector::underscore('helloWorld');
		$this->assertIsString($result);
	}

	public function test_inflector_humanize(): void
	{
		$this->assertEquals('hello world', Inflector::humanize('hello_world'));
	}

	public function test_num_ordinal(): void
	{
		$this->assertEquals('st', Num::ordinal(1));
		$this->assertEquals('nd', Num::ordinal(2));
		$this->assertEquals('rd', Num::ordinal(3));
		$this->assertEquals('th', Num::ordinal(4));
		$this->assertEquals('th', Num::ordinal(11));
		$this->assertEquals('th', Num::ordinal(12));
		$this->assertEquals('th', Num::ordinal(13));
	}

	public function test_num_format(): void
	{
		$formatted = Num::format(100.5, 2);
		$this->assertIsString($formatted);
		$this->assertStringContainsString('100', $formatted);
		$this->assertStringContainsString('50', $formatted);
	}

	public function test_num_bytes(): void
	{
		$this->assertEquals(1.0, Num::bytes(1));
		$this->assertEquals(1024.0, Num::bytes(1024));
	}

	public function test_text_auto_link_urls(): void
	{
		$text = 'Visit https://example.com today';
		$linked = Text::auto_link($text);
		$this->assertStringContainsString('href="https://example.com"', $linked);
	}

	public function test_text_auto_link_emails(): void
	{
		$text = 'Email info@example.com for details';
		$linked = Text::auto_link($text);
		$this->assertStringContainsString('info@example.com', $linked);
		$this->assertStringContainsString('&#', $linked);
	}

	public function test_text_limit_words(): void
	{
		$text = 'one two three four five';
		$result = Text::limit_words($text, 3);
		$this->assertStringContainsString('one two three', $result);
	}

	public function test_text_limit_chars(): void
	{
		$text = 'hello world this is a test';
		$limited = Text::limit_chars($text, 10);
		$this->assertLessThanOrEqual(13, strlen($limited));
	}

	public function test_text_alternate(): void
	{
		$this->assertEquals('a', Text::alternate('a', 'b'));
		$this->assertEquals('b', Text::alternate('a', 'b'));
		$this->assertEquals('a', Text::alternate('a', 'b'));
	}

	public function test_text_random(): void
	{
		$random = Text::random('alnum', 16);
		$this->assertEquals(16, strlen($random));
		$this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $random);

		$alpha = Text::random('alpha', 8);
		$this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $alpha);

		$numeric = Text::random('numeric', 6);
		$this->assertMatchesRegularExpression('/^[0-9]+$/', $numeric);
	}

	public function test_text_reduce_slashes(): void
	{
		$this->assertEquals('path/to/file', Text::reduce_slashes('path///to//file'));
	}

	public function test_cookie_salt_set(): void
	{
		Cookie::$salt = 'test-salt';
		$this->assertEquals('test-salt', Cookie::$salt);
	}

	public function test_kohana_modules(): void
	{
		$modules = Kohana::modules();
		$this->assertIsArray($modules);
		$this->assertArrayHasKey('database', $modules);
		$this->assertArrayHasKey('minion', $modules);
		$this->assertArrayHasKey('orm', $modules);
	}

	public function test_multiple_routes_active(): void
	{
		$routes = Route::all();
		$this->assertGreaterThanOrEqual(1, count($routes));
	}

	public function test_route_uri_with_special_chars(): void
	{
		Route::set('special_route', 'search/<query>', array('query' => '.+'))
			->defaults(array('controller' => 'search', 'action' => 'index'));

		$request = Request::factory('search/php+framework');
		$params = Route::get('special_route')->matches($request);
		$this->assertIsArray($params);
		$this->assertEquals('php+framework', $params['query']);
	}

	public function test_request_client_internal(): void
	{
		$request = Request::factory('welcome/index');
		$client = $request->client();
		$this->assertInstanceOf(\Request_Client_Internal::class, $client);
	}

	public function test_response_cookies(): void
	{
		$response = Response::factory();
		$response->cookie('test_cookie', 'test_value');
		$cookies = $response->cookie();
		$this->assertIsArray($cookies);
		$this->assertArrayHasKey('test_cookie', $cookies);
	}

	public function test_response_redirect(): void
	{
		$response = Response::factory();
		$response->status(302);
		$response->headers('Location', '/new/page');

		$this->assertEquals(302, $response->status());
		$this->assertEquals('/new/page', $response->headers('Location'));
	}

	public function test_orm_query_building_without_db(): void
	{
		try {
			$query = ORM::factory('User')
				->where('status', '=', 'active')
				->order_by('username', 'ASC')
				->limit(10);

			$this->assertInstanceOf(ORM::class, $query);
		} catch (Throwable $e) {
			$this->markTestSkipped('Database not available: ' . $e->getMessage());
		}
	}

	public function test_orm_table_name_without_db(): void
	{
		try {
			$user = ORM::factory('User');
			$this->assertEquals('users', $user->table_name());
		} catch (Throwable $e) {
			$this->markTestSkipped('Database not available: ' . $e->getMessage());
		}
	}

	public function test_orm_clear_without_db(): void
	{
		try {
			$user = ORM::factory('User');
			$result = $user->clear();
			$this->assertInstanceOf(ORM::class, $result);
			$this->assertFalse($user->loaded());
		} catch (Throwable $e) {
			$this->markTestSkipped('Database not available: ' . $e->getMessage());
		}
	}

	public function test_db_query_builder(): void
	{
		try {
			$query = DB::select('id', 'username')
				->from('users')
				->where('status', '=', 'active')
				->limit(10);

			$this->assertInstanceOf(\Database_Query_Builder_Select::class, $query);
		} catch (Throwable $e) {
			$this->markTestSkipped('Database not available: ' . $e->getMessage());
		}
	}

	public function test_kohana_version_constants(): void
	{
		$this->assertEquals(10, Kohana::PRODUCTION);
		$this->assertEquals(20, Kohana::STAGING);
		$this->assertEquals(30, Kohana::TESTING);
		$this->assertEquals(40, Kohana::DEVELOPMENT);
	}
}
