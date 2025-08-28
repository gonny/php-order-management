<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'order_id',
        'sku',
        'name',
        'qty',
        'price',
        'tax_rate',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'qty' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->qty * $this->price;
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->getSubtotalAttribute() * $this->tax_rate;
    }

    public function getTotalAttribute(): float
    {
        return $this->getSubtotalAttribute() + $this->getTaxAmountAttribute();
    }
}
