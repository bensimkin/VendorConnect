<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_token',
        'login_at',
        'logout_at',
        'last_activity_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Get the user that owns this session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Start a new session
     */
    public static function startSession(int $userId, string $token, string $ipAddress = null, string $userAgent = null): self
    {
        try {
            return self::create([
                'user_id' => $userId,
                'session_token' => $token,
                'login_at' => now(),
                'last_activity_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to start user session', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * End a session
     */
    public function endSession(): void
    {
        try {
            $this->logout_at = now();
            if ($this->login_at) {
                $this->duration_seconds = $this->login_at->diffInSeconds($this->logout_at);
            }
            $this->save();
        } catch (\Exception $e) {
            \Log::error('Failed to end user session', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        try {
            $this->last_activity_at = now();
            $this->save();
        } catch (\Exception $e) {
            \Log::debug('Failed to update session activity', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get active sessions (not logged out)
     */
    public static function getActiveSessions()
    {
        return self::whereNull('logout_at')->get();
    }

    /**
     * Get user's average session duration
     */
    public static function getUserAverageSessionDuration(int $userId): ?int
    {
        return self::where('user_id', $userId)
                   ->whereNotNull('duration_seconds')
                   ->avg('duration_seconds');
    }

    /**
     * Close stale sessions (no activity in 24 hours)
     */
    public static function closeStaleSessions(): int
    {
        $staleSessions = self::whereNull('logout_at')
            ->where('last_activity_at', '<', now()->subHours(24))
            ->get();

        foreach ($staleSessions as $session) {
            $session->endSession();
        }

        return $staleSessions->count();
    }

    /**
     * Find session by token
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('session_token', $token)->first();
    }
}
