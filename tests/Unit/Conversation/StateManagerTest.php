<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Unit\Conversation;

use Dizvestnov\LaravelMaxBot\Conversation\StateManager;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

class StateManagerTest extends TestCase
{
    private StateManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $cache         = new Repository(new ArrayStore());
        $this->manager = new StateManager($cache);
    }

    public function test_get_state_returns_null_initially(): void
    {
        $this->assertNull($this->manager->getState(1));
    }

    public function test_set_and_get_state(): void
    {
        $this->manager->setState(1, 'waiting_name');

        $this->assertSame('waiting_name', $this->manager->getState(1));
    }

    public function test_clear_state_removes_state_and_data(): void
    {
        $this->manager->setState(1, 'some_state');
        $this->manager->setData(1, ['key' => 'value']);

        $this->manager->clearState(1);

        $this->assertNull($this->manager->getState(1));
        $this->assertSame([], $this->manager->getData(1));
    }

    public function test_get_data_returns_empty_array_initially(): void
    {
        $this->assertSame([], $this->manager->getData(1));
    }

    public function test_set_and_get_data(): void
    {
        $this->manager->setData(1, ['name' => 'John', 'age' => 30]);

        $this->assertSame(['name' => 'John', 'age' => 30], $this->manager->getData(1));
    }

    public function test_merge_data_preserves_existing(): void
    {
        $this->manager->setData(1, ['name' => 'John']);
        $this->manager->mergeData(1, ['age' => 30]);

        $this->assertSame(['name' => 'John', 'age' => 30], $this->manager->getData(1));
    }

    public function test_different_users_have_separate_state(): void
    {
        $this->manager->setState(1, 'state_user_1');
        $this->manager->setState(2, 'state_user_2');

        $this->assertSame('state_user_1', $this->manager->getState(1));
        $this->assertSame('state_user_2', $this->manager->getState(2));
    }
}
