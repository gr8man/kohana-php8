<?php
declare(strict_types=1);

namespace Kohana\Tests;

use \Route;
use \Request;

class RoutingTest extends BaseTestCase
{
    public function test_default_route(): void
    {
        $route = Route::get('default');
        $this->assertNotNull($route);
        
        $request = Request::factory('welcome/index/1');
        $params = $route->matches($request);
        
        $this->assertIsArray($params);
        $this->assertEquals('Welcome', $params['controller']);
        $this->assertEquals('index', $params['action']);
        $this->assertEquals('1', $params['id']);
    }

    public function test_uri_generation(): void
    {
        $uri = Route::get('default')->uri(['controller' => 'user', 'action' => 'profile', 'id' => '123']);
        $this->assertEquals('user/profile/123', $uri);
    }
}
