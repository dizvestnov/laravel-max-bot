<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Feature;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Dizvestnov\LaravelMaxBot\Facades\MaxBot;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_proxies_to_client_interface(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('getBotInfo')
                ->once()
                ->andReturn(['user_id' => 1, 'name' => 'TestBot']);
        });

        $result = MaxBot::getBotInfo();

        $this->assertSame(['user_id' => 1, 'name' => 'TestBot'], $result);
    }

    public function test_facade_send_message_proxies_correctly(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with(['recipient' => ['user_id' => 42], 'text' => 'Hi'])
                ->andReturn(['message_id' => 'msg-1']);
        });

        $result = MaxBot::sendMessage(['recipient' => ['user_id' => 42], 'text' => 'Hi']);

        $this->assertSame(['message_id' => 'msg-1'], $result);
    }
}
