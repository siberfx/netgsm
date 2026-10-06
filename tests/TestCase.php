<?php

namespace Siberfx\NetGsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Message\RequestInterface;
use Siberfx\NetGsm\NetGsm;
use Siberfx\NetGsm\NetGsmServiceProvider;

abstract class TestCase extends Orchestra
{
    protected MockHandler $mockHandler;

    /**
     * @var array<int, array{request: RequestInterface}>
     */
    protected array $history = [];

    protected function getPackageProviders($app): array
    {
        return [NetGsmServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.locale', 'en');
    }

    /**
     * Creates a NetGsm instance whose http client replies with the given responses.
     */
    protected function netGsm(array $responses, array $defaults = []): NetGsm
    {
        $this->mockHandler = new MockHandler(array_map(
            fn ($response) => $response instanceof Response ? $response : $this->jsonResponse($response),
            $responses,
        ));

        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->history));

        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://api.netgsm.com.tr/',
        ]);

        return new NetGsm($client, [
            'user_code' => 'user',
            'secret' => 'secret',
            'brand_code' => 'brand',
        ], array_merge(['header' => 'DEFAULT'], $defaults));
    }

    protected function jsonResponse(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    protected function request(int $index = 0): RequestInterface
    {
        return $this->history[$index]['request'];
    }

    protected function requestBody(int $index = 0): array
    {
        return json_decode((string) $this->request($index)->getBody(), true);
    }
}
