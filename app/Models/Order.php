<?php

namespace App\Models;

use App\Services\OrderStateMachine;
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

    /**
     * State machine integration methods
     */
    public function canTransitionTo(string $state): bool
    {
        return app(OrderStateMachine::class)->canTransition($this, $state);
    }

    public function transitionTo(string $state, array $context = []): bool
    {
        return app(OrderStateMachine::class)->transition($this, $state, $context);
    }

    public function getAvailableTransitions(): array
    {
        return app(OrderStateMachine::class)->getAvailableTransitions($this);
    }

    /**
     * Status helper methods
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('number', $number)->exists());

        return $number;
    }

    /**
     * Generate unique PMI ID
     */
    public static function generatePmiId(): string
    {
        do {
            $pmiId = 'PMI-' . uniqid() . '-' . rand(1000, 9999);
        } while (self::where('pmi_id', $pmiId)->exists());

        return $pmiId;
    }
}
