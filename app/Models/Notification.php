<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Notification extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'sender_id',
        'title',
        'message',
        'type',
        'data',
        'read_at',
        'broadcast_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'broadcast_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sender of the notification.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include recent notifications.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return false;
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Get the notification icon based on type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'success' => 'bi-check-circle-fill text-success',
            'warning' => 'bi-exclamation-triangle-fill text-warning',
            'error' => 'bi-x-circle-fill text-danger',
            'security' => 'bi-shield-fill text-danger',
            'announcement' => 'bi-megaphone-fill text-primary',
            default => 'bi-info-circle-fill text-info',
        };
    }

    /**
     * Get the time ago in human readable format.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the formatted created at time.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M j, Y \a\t g:i A');
    }

    /**
     * Get the notification action URL if available.
     */
    public function getActionUrlAttribute(): ?string
    {
        if (!$this->data) {
            return null;
        }

        return $this->data['action_url'] ?? null;
    }

    /**
     * Get the notification action text if available.
     */
    public function getActionTextAttribute(): ?string
    {
        if (!$this->data) {
            return null;
        }

        return $this->data['action_text'] ?? 'View Details';
    }

    /**
     * Scope a query to only include notifications that need broadcasting.
     */
    public function scopeToBroadcast($query)
    {
        return $query->whereNull('broadcast_at');
    }

    /**
     * Mark the notification as broadcasted.
     */
    public function markAsBroadcasted(): bool
    {
        if ($this->broadcast_at) {
            return false;
        }

        return $this->update(['broadcast_at' => now()]);
    }

    /**
     * Get notification statistics for dashboard.
     */
    public static function getDashboardStats(): array
    {
        $userId = auth()->id();

        return [
            'total' => static::where('user_id', $userId)->count(),
            'unread' => static::where('user_id', $userId)->unread()->count(),
            'today' => static::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count(),
            'this_week' => static::where('user_id', $userId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'by_type' => static::where('user_id', $userId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Get recent notifications for user.
     */
    public static function getRecentForUser(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}