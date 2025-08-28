<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingLabel extends Model
{
    use HasFactory, HasUlids;

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

    // Status constants
    public const STATUS_GENERATED = 'generated';
    public const STATUS_FAILED = 'failed';
    public const STATUS_VOIDED = 'voided';

    // Format constants
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PNG = 'png';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isGenerated(): bool
    {
        return $this->status === self::STATUS_GENERATED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_GENERATED);
    }
}
