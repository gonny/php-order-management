<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'client_id',
        'name',
        'street1',
        'street2',
        'city',
        'postal_code',
        'country_code',
        'state',
        'company',
        'phone',
        'email',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->street1;
        if ($this->street2) {
            $address .= ', ' . $this->street2;
        }
        $address .= ', ' . $this->city;
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        $address .= ', ' . $this->postal_code;
        $address .= ', ' . strtoupper($this->country_code);
        
        return $address;
    }
}
