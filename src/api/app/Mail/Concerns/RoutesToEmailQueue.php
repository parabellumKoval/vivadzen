<?php

namespace App\Mail\Concerns;

trait RoutesToEmailQueue
{
    protected function routeToEmailQueue(): void
    {
        if (method_exists($this, 'onQueue')) {
            $this->onQueue((string) config('queue.names.emails', 'emails'));
        }
    }
}
