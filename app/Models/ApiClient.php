<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'key_id',
        'secret_hash',
        'ip_allowlist',
        'active',
        'last_used_at',
        'meta',
    ];

    protected $casts = [
        'ip_allowlist' => 'array',
        'active' => 'boolean',
        'meta' => 'array',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ip_allowlist)) {
            return true; // No allowlist means all IPs are allowed
        }

        // Ensure ip_allowlist is an array
        $allowList = is_array($this->ip_allowlist) ? $this->ip_allowlist : json_decode($this->ip_allowlist, true);

        if (!is_array($allowList)) {
            return true; // Fallback if parsing fails
        }

        return in_array($ip, $allowList);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByKeyId($query, string $keyId)
    {
        return $query->where('key_id', $keyId);
    }
}
