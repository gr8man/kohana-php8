<?php
declare(strict_types=1);

namespace Kohana\Tests;

use \Request;
use \Response;
use \Request_Client_External;

class ExternalRequestTest extends BaseTestCase
{
    public function test_external_request_mocking(): void
    {
        // Mocking Request_Client_External to avoid actual network calls
        $mockClient = $this->getMockBuilder(Request_Client_External::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', '_send_message'])
            ->getMock();
        
        $fakeResponse = Response::factory()
            ->status(200)
            ->body('{"status": "ok"}')
            ->headers('Content-Type', 'application/json');

        $mockClient->method('execute')
            ->willReturn($fakeResponse);

        $request = Request::factory('https://api.example.com/v1/status');
        
        // Use Reflection or setter if available to inject the client
        // Request has a public property/method for client usually
        $request->client($mockClient);

        $response = $request->execute();
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"status": "ok"}', $response->body());
        $this->assertEquals('application/json', $response->headers('Content-Type'));
    }
}
