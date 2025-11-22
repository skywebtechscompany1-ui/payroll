<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErrorLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'user_id',
        'request_data',
        'status_code',
        'severity',
        'resolved',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_data' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that caused the error.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the error.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope a query to only include unresolved errors.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope a query to only include resolved errors.
     */
    public function scopeResolved($query)
    {
        return $query->where('resolved', true);
    }

    /**
     * Scope a query to only include critical errors.
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('severity', ['critical', 'error']);
    }

    /**
     * Scope a query to only include errors from the last N days.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to filter by exception class.
     */
    public function scopeByException($query, string $exceptionClass)
    {
        return $query->where('exception_class', $exceptionClass);
    }

    /**
     * Get the short file path (relative to project root).
     */
    public function getShortFilePathAttribute(): string
    {
        return str_replace(base_path(), '', $this->file);
    }

    /**
     * Get the severity color for display.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'danger',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get the formatted request data for display.
     */
    public function getFormattedRequestDataAttribute(): string
    {
        if (empty($this->request_data)) {
            return 'N/A';
        }

        return json_encode($this->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mark the error as resolved.
     */
    public function markAsResolved(?string $notes = null, ?int $resolvedBy = null): void
    {
        $this->update([
            'resolved' => true,
            'resolution_notes' => $notes,
            'resolved_by' => $resolvedBy ?? auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark the error as unresolved.
     */
    public function markAsUnresolved(): void
    {
        $this->update([
            'resolved' => false,
            'resolution_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * Get similar errors (same exception class and similar message).
     */
    public function getSimilarErrors()
    {
        return static::where('exception_class', $this->exception_class)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('message', 'like', '%' . substr($this->message, 0, 50) . '%')
                      ->orWhere('file', $this->file)
                      ->orWhere('line', $this->line);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get error statistics for the dashboard.
     */
    public static function getStatistics()
    {
        $now = now();
        $last24Hours = $now->copy()->subHours(24);
        $last7Days = $now->copy()->subDays(7);
        $last30Days = $now->copy()->subDays(30);

        return [
            'total' => static::count(),
            'unresolved' => static::unresolved()->count(),
            'critical' => static::critical()->count(),
            'last_24_hours' => static::where('created_at', '>=', $last24Hours)->count(),
            'last_7_days' => static::where('created_at', '>=', $last7Days)->count(),
            'last_30_days' => static::where('created_at', '>=', $last30Days)->count(),
            'by_severity' => static::selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->orderByRaw('FIELD(severity, "critical", "error", "warning", "info")')
                ->pluck('count', 'severity')
                ->toArray(),
            'top_exception_classes' => static::selectRaw('exception_class, COUNT(*) as count')
                ->groupBy('exception_class')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'exception_class')
                ->toArray(),
            'top_files' => static::selectRaw('file, COUNT(*) as count')
                ->groupBy('file')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'file')
                ->toArray(),
        ];
    }

    /**
     * Clean up old error logs.
     */
    public static function cleanup(int $daysToKeep = 90): int
    {
        return static::where('created_at', '<', now()->subDays($daysToKeep))
            ->where('resolved', true)
            ->delete();
    }

    /**
     * Get error trends for the last N days.
     */
    public static function getTrends(int $days = 30): array
    {
        $trends = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->addDay();

            $count = static::whereBetween('created_at', [$date, $nextDate])->count();
            $criticalCount = static::whereBetween('created_at', [$date, $nextDate])
                ->critical()
                ->count();

            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $count,
                'critical' => $criticalCount,
            ];
        }

        return $trends;
    }
}