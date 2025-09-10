<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'description',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the API key
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new API key
     */
    public static function generateKey(User $user, ?array $permissions = null, ?Carbon $expiresAt = null): string
    {
        return 'vck_' . Str::random(40);
    }

    /**
     * Create a new API key for a user
     */
    public static function createForUser(User $user, string $name, ?string $description = null, ?array $permissions = null, ?Carbon $expiresAt = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'name' => $name,
            'key' => self::generateKey($user, $permissions, $expiresAt),
            'description' => $description,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'is_active' => true
        ]);
    }

    /**
     * Check if the API key is valid and not expired
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Update the last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if the API key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return true; // If no permissions specified, allow all
        }

        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    /**
     * Get the masked key for display purposes
     */
    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 8) . '...' . substr($this->key, -4);
    }

    /**
     * Scope to get only active API keys
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope to get API keys for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}