<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class MockHttpTest extends TestCase
{
    public function testMockRequestReturns200(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"status":"ok"}')
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $response = $client->get('/api/test');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('ok', $body['status']);
    }

    public function testMockRequestReturns404(): void
    {
        $mock = new MockHandler([
            new Response(404, [], 'Not Found')
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $response = $client->get('/api/notfound');
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMockRequestException(): void
    {
        $mock = new MockHandler([
            new RequestException('Ошибка соединения', new Request('GET', 'test'))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $this->expectException(RequestException::class);
        
        $client->get('/api/error');
    }

    public function testMockMultipleResponses(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'First response'),
            new Response(201, [], 'Second response'),
            new Response(400, [], 'Bad request')
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $response1 = $client->get('/first');
        $response2 = $client->post('/second');
        $response3 = $client->get('/third');
        
        $this->assertEquals(200, $response1->getStatusCode());
        $this->assertEquals(201, $response2->getStatusCode());
        $this->assertEquals(400, $response3->getStatusCode());
    }
}