<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class ApiTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://nginx:80',
            'timeout' => 5.0,
            'http_errors' => false
        ]);
    }

    public function testIndexPageReturns200(): void
    {
        $response = $this->client->get('/index.php');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFormPageReturns200(): void
    {
        $response = $this->client->get('/form.html');
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString('Викторина', $body);
    }

    public function testPostValidData(): void
    {
        $response = $this->client->post('/process.php', [
            'form_params' => [
                'username' => 'Тестовый Пользователь',
                'age' => 25,
                'topic' => 'IT',
                'prize' => 'yes',
                'difficulty' => 'easy'
            ],
            'allow_redirects' => false
        ]);
        
        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeader('Location');
        $this->assertNotEmpty($location);
        $this->assertStringContainsString('index.php', $location[0]);
    }

    public function testPostInvalidData(): void
    {
        $response = $this->client->post('/process.php', [
            'form_params' => [
                'username' => '',
                'age' => 999,
                'topic' => '',
                'difficulty' => ''
            ],
            'allow_redirects' => false
        ]);
        
        $this->assertEquals(302, $response->getStatusCode());
    }
}