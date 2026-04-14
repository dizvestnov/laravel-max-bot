<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Conversation;

use Illuminate\Contracts\Cache\Repository;

final class StateManager
{
    public function __construct(
        private readonly Repository $cache,
        private readonly string $prefix = 'max-bot',
    ) {
    }

    public function getState(int $userId): ?string
    {
        return $this->cache->get($this->stateKey($userId));
    }

    public function setState(int $userId, string $state, int $ttl = 3600): void
    {
        $this->cache->put($this->stateKey($userId), $state, $ttl);
    }

    public function clearState(int $userId): void
    {
        $this->cache->forget($this->stateKey($userId));
        $this->cache->forget($this->dataKey($userId));
    }

    public function getData(int $userId): array
    {
        return $this->cache->get($this->dataKey($userId), []);
    }

    public function setData(int $userId, array $data, int $ttl = 3600): void
    {
        $this->cache->put($this->dataKey($userId), $data, $ttl);
    }

    public function mergeData(int $userId, array $data): void
    {
        $existing = $this->getData($userId);
        $this->setData($userId, array_merge($existing, $data));
    }

    private function stateKey(int $userId): string
    {
        return "{$this->prefix}:state:{$userId}";
    }

    private function dataKey(int $userId): string
    {
        return "{$this->prefix}:data:{$userId}";
    }
}
