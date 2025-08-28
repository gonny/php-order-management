<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

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
        'label_id',
        'meta',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingLabel(): HasOne
    {
        return $this->hasOne(ShippingLabel::class, 'order_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'entity_id')
            ->where('entity_type', 'order');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entity_id')
            ->where('entity_type', 'order');
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('qty');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->qty * $item->price);
    }
}
