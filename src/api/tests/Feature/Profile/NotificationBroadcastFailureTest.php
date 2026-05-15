<?php

namespace Tests\Feature\Profile;

use Backpack\Profile\app\Models\Notification;
use Backpack\Profile\app\Services\NotificationService;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class NotificationBroadcastFailureTest extends TestCase
{
    public function test_broadcast_failure_does_not_escape_notification_service(): void
    {
        $this->app->instance(BusDispatcher::class, new class implements BusDispatcher {
            public function dispatch($command): mixed
            {
                return null;
            }

            public function dispatchSync($command, $handler = null): mixed
            {
                return null;
            }

            public function dispatchNow($command, $handler = null): mixed
            {
                throw new BroadcastException('pusher is unavailable');
            }

            public function hasCommandHandler($command): bool
            {
                return false;
            }

            public function getCommandHandler($command): mixed
            {
                return null;
            }

            public function pipeThrough(array $pipes): static
            {
                return $this;
            }

            public function map(array $map): static
            {
                return $this;
            }
        });

        Log::spy();

        $notification = new Notification([
            'kind' => Notification::KIND_EVENT,
            'target_type' => Notification::TARGET_BROADCAST,
            'audience' => Notification::AUDIENCE_ALL,
            'variant' => Notification::VARIANT_INFO,
            'is_active' => true,
            'published_at' => now(),
        ]);
        $notification->id = 123;
        $notification->exists = true;
        $notification->setRelation('event', null);

        app(NotificationService::class)->broadcast($notification);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Notification broadcast failed.', \Mockery::on(
                fn (array $context): bool => ($context['notification_id'] ?? null) === 123
                    && ($context['exception'] ?? null) === BroadcastException::class
                    && ($context['message'] ?? null) === 'pusher is unavailable'
            ));
    }
}
