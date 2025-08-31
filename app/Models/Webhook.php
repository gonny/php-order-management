<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory, HasUlids;

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
        'headers' => 'array',
        'signature_valid' => 'boolean',
        'http_status' => 'integer',
        'retry_count' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function isProcessed(): bool
    {
        return !is_null($this->processed_at);
    }

    public function isValid(): bool
    {
        return $this->signature_valid;
    }

    public function canRetry(): bool
    {
        return $this->retry_count < 6; // Max 6 attempts as per spec
    }

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }

    public function scopeValid($query)
    {
        return $query->where('signature_valid', true);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
