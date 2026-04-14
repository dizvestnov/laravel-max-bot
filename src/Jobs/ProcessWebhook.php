<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Jobs;

use Dizvestnov\LaravelMaxBot\Events\UpdateEventFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $update;

    public function __construct(array $update)
    {
        $this->update = $update;
    }

    public function handle(UpdateEventFactory $factory): void
    {
        $event = $factory->make($this->update);

        if ($event !== null) {
            event($event);
        }
    }
}
