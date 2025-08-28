<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

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
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->qty * $this->price;
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->total_price * ($this->tax_rate / 100);
    }

    public function getTotalPriceWithTaxAttribute(): float
    {
        return $this->total_price + $this->tax_amount;
    }
}
