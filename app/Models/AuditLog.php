<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUlids;

    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'before',
        'after',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'created_at' => 'datetime',
    ];

    // Actor type constants
    public const ACTOR_USER = 'user';
    public const ACTOR_API = 'api';

    public function scopeByEntity($query, string $entityType, string $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    public function scopeByActor($query, string $actorType, string $actorId)
    {
        return $query->where('actor_type', $actorType)
            ->where('actor_id', $actorId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function getChangesAttribute(): array
    {
        $changes = [];
        
        if ($this->before && $this->after) {
            foreach ($this->after as $key => $newValue) {
                $oldValue = $this->before[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }
}
