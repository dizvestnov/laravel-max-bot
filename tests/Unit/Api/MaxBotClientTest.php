<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Unit\Api;

use Dizvestnov\LaravelMaxBot\Api\MaxBotClient;
use Dizvestnov\LaravelMaxBot\Exceptions\ApiException;
use Dizvestnov\LaravelMaxBot\Exceptions\RateLimitException;
use Dizvestnov\LaravelMaxBot\Exceptions\UnauthorizedException;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class MaxBotClientTest extends TestCase
{
    private function makeClient(array $responses, array &$container = []): MaxBotClient
    {
        $mock    = new MockHandler($responses);
        $history = Middleware::history($container);
        $stack   = HandlerStack::create($mock);
        $stack->push($history);

        $client = new MaxBotClient('test-token', [
            'base_uri' => 'https://platform-api.max.ru',
            'timeout'  => 5,
            'retry'    => ['times' => 3, 'sleep' => 1],
        ]);

        // Inject Guzzle client via reflection
        $reflection = new \ReflectionProperty($client, 'http');
        $reflection->setAccessible(true);
        $reflection->setValue($client, new Client(['handler' => $stack]));

        return $client;
    }

    public function test_get_bot_info_returns_array_on_200(): void
    {
        $container = [];
        $client    = $this->makeClient([
            new Response(200, [], json_encode(['user_id' => 1, 'name' => 'TestBot'])),
        ], $container);

        $result = $client->getBotInfo();

        $this->assertSame(['user_id' => 1, 'name' => 'TestBot'], $result);
    }

    public function test_client_sends_authorization_header(): void
    {
        $container = [];
        $client    = $this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $container);

        $client->getBotInfo();

        /** @var array $transaction */
        $transaction = $container[0];
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $transaction['request'];

        $this->assertSame('test-token', $request->getHeaderLine('Authorization'));
    }

    public function test_send_message_posts_to_messages_endpoint(): void
    {
        $container = [];
        $client    = $this->makeClient([
            new Response(200, [], json_encode(['message_id' => 'msg-123'])),
        ], $container);

        $result = $client->sendMessage(['recipient' => ['user_id' => 42], 'text' => 'Hello']);

        $this->assertSame(['message_id' => 'msg-123'], $result);

        /** @var array $transaction */
        $transaction = $container[0];
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $transaction['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('messages', (string) $request->getUri());
    }

    public function test_401_throws_unauthorized_exception(): void
    {
        $this->expectException(UnauthorizedException::class);

        $mock  = new MockHandler([
            new Response(401, [], json_encode(['message' => 'Unauthorized'])),
        ]);
        $stack = HandlerStack::create($mock);

        $client = new MaxBotClient('bad-token', ['retry' => ['times' => 1, 'sleep' => 1]]);
        $reflection = new \ReflectionProperty($client, 'http');
        $reflection->setAccessible(true);
        $reflection->setValue($client, new Client(['handler' => $stack]));

        $client->getBotInfo();
    }

    public function test_429_throws_rate_limit_exception_after_retries(): void
    {
        $this->expectException(RateLimitException::class);

        $mock = new MockHandler([
            new Response(429, ['Retry-After' => '5'], json_encode(['message' => 'Too Many Requests'])),
            new Response(429, [], json_encode(['message' => 'Too Many Requests'])),
            new Response(429, [], json_encode(['message' => 'Too Many Requests'])),
        ]);
        $stack = HandlerStack::create($mock);

        $client = new MaxBotClient('token', ['retry' => ['times' => 3, 'sleep' => 1]]);
        $reflection = new \ReflectionProperty($client, 'http');
        $reflection->setAccessible(true);
        $reflection->setValue($client, new Client(['handler' => $stack]));

        $client->getBotInfo();
    }

    public function test_503_retries_then_throws_api_exception(): void
    {
        $this->expectException(ApiException::class);

        $mock = new MockHandler([
            new Response(503, [], json_encode(['message' => 'Service Unavailable'])),
            new Response(503, [], json_encode(['message' => 'Service Unavailable'])),
            new Response(503, [], json_encode(['message' => 'Service Unavailable'])),
        ]);
        $stack = HandlerStack::create($mock);

        $client = new MaxBotClient('token', ['retry' => ['times' => 3, 'sleep' => 1]]);
        $reflection = new \ReflectionProperty($client, 'http');
        $reflection->setAccessible(true);
        $reflection->setValue($client, new Client(['handler' => $stack]));

        $client->getBotInfo();
    }
}
