<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'number',
        'pmi_id',
        'client_id',
        'status',
        'total_amount',
        'currency',
        'shipping_address_id',
        'billing_address_id',
        'carrier',
        'shipping_method',
        'pickup_point_id',
        'pdf_label_path',
        'dpd_shipment_id',
        'parcel_group_id',
        'meta',
        'pdf_path',
        'remote_session_id',
        'r2_photo_links',
        'local_photo_paths',
    ];

    protected $casts = [
        'meta' => 'array',
        'total_amount' => 'decimal:2',
        'r2_photo_links' => 'array',
        'local_photo_paths' => 'array',
    ];

    // Order status constants
    public const STATUS_NEW = 'new';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PAID = 'paid';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_ON_HOLD = 'on_hold';

    public const STATUS_FAILED = 'failed';

    // Carrier constants
    public const CARRIER_BALIKOVNA = 'balikovna';

    public const CARRIER_DPD = 'dpd';

    // Shipping method constants
    public const SHIPPING_METHOD_DPD_HOME = 'DPD_Home';

    public const SHIPPING_METHOD_DPD_PICKUP = 'DPD_PickupPoint';

    public const SHIPPING_METHOD_BALIKOVNA_PICKUP = 'Balikovna_PickupPoint';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingLabels(): HasMany
    {
        return $this->hasMany(ShippingLabel::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entity_id')
            ->where('entity_type', 'order');
    }

    // Status checking methods
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    // Scope methods
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ]);
    }

    public function scopeProcessable($query)
    {
        return $query->whereIn('status', [
            self::STATUS_NEW,
            self::STATUS_CONFIRMED,
            self::STATUS_PAID,
        ]);
    }
}
