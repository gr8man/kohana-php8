<?php
declare(strict_types=1);

namespace Kohana\Tests;

use \Request;
use \Response;
use \Kohana;
use \Route;

class RequestResponseTest extends BaseTestCase
{
    public function test_request_factory(): void
    {
        $request = Request::factory('welcome/index');
        
        // In Kohana 3.3, Route is matched during execute()
        // OR we can manually match it for testing
        $request->execute();
        
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('Welcome', $request->controller(), "Controller should match 'Welcome'");
        $this->assertEquals('index', $request->action());
    }

    public function test_response_status(): void
    {
        $response = Response::factory();
        $response->status(404);
        $this->assertEquals(404, $response->status());
        
        $response->body('Not Found');
        $this->assertEquals('Not Found', $response->body());
    }

    public function test_response_headers(): void
    {
        $response = Response::factory();
        $response->headers('Content-Type', 'application/json');
        $this->assertEquals('application/json', $response->headers('Content-Type'));
    }
}
