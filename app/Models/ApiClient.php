<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key_id',
        'secret_hash',
        'ip_allowlist',
        'active',
    ];

    protected $casts = [
        'ip_allowlist' => 'array',
        'active' => 'boolean',
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
            return true; // No IP restrictions
        }

        return in_array($ip, $this->ip_allowlist);
    }

    public function verifySecret(string $secret): bool
    {
        return hash_equals($this->secret_hash, hash('sha256', $secret));
    }
}
