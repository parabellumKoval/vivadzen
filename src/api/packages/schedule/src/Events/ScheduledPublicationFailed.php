<?php

namespace Backpack\Schedule\Events;

use Backpack\Schedule\Models\ScheduledPublication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class ScheduledPublicationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ScheduledPublication $publication;
    public ?Model $schedulable;
    public string $error;

    /**
     * Create a new event instance.
     */
    public function __construct(ScheduledPublication $publication, string $error)
    {
        $this->publication = $publication;
        $this->schedulable = $publication->schedulable;
        $this->error = $error;
    }
}
