<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'carrier',
        'carrier_shipment_id',
        'tracking_number',
        'file_path',
        'format',
        'status',
        'raw_response',
        'meta',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isGenerated(): bool
    {
        return $this->status === 'generated';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }
}
