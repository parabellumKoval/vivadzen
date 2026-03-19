<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerationRun extends Model
{
    use HasFactory;

    public const TYPE_BOT_USERS = 'bot_users';
    public const TYPE_PRODUCT_REVIEWS = 'product_reviews';
    public const TYPE_PRODUCT_REVIEW_PHOTOS = 'product_review_photos';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'ak_generation_runs';

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'meta' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getProgressPercentAttribute(): int
    {
        $total = (int) $this->progress_total;
        $current = (int) $this->progress_current;

        if ($total < 1) {
            return 0;
        }

        return (int) max(0, min(100, round(($current / $total) * 100)));
    }

    public function markQueued(): void
    {
        $this->forceFill([
            'status' => self::STATUS_QUEUED,
            'started_at' => null,
            'finished_at' => null,
            'error_message' => null,
        ])->save();
    }

    public function markRunning(): void
    {
        $this->forceFill([
            'status' => self::STATUS_RUNNING,
            'started_at' => $this->started_at ?: now(),
            'finished_at' => null,
            'error_message' => null,
        ])->save();
    }

    public function markCompleted(?string $output = null, ?array $result = null): void
    {
        $payload = [
            'status' => self::STATUS_COMPLETED,
            'finished_at' => now(),
            'error_message' => null,
        ];

        if ($output !== null) {
            $payload['output'] = $output;
        }

        if ($result !== null) {
            $payload['result'] = array_replace_recursive($this->result ?? [], $result);
        }

        $this->forceFill($payload)->save();
    }

    public function markFailed(string $message, ?string $output = null, ?array $result = null): void
    {
        $payload = [
            'status' => self::STATUS_FAILED,
            'finished_at' => now(),
            'error_message' => $message,
        ];

        if ($output !== null) {
            $payload['output'] = $output;
        }

        if ($result !== null) {
            $payload['result'] = array_replace_recursive($this->result ?? [], $result);
        }

        $this->forceFill($payload)->save();
    }
}
