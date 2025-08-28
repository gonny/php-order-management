<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'event_type',
        'signature_valid',
        'http_status',
        'headers',
        'payload',
        'processed_at',
        'processing_result',
        'retry_count',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];

    public function isProcessed(): bool
    {
        return !is_null($this->processed_at);
    }

    public function needsRetry(): bool
    {
        return !$this->isProcessed() && $this->retry_count < 6;
    }

    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    public function markAsProcessed(string $result = null): void
    {
        $this->update([
            'processed_at' => now(),
            'processing_result' => $result,
        ]);
    }
}
