<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send a notification to a specific user
     */
    public function sendToUser(User $user, string $title, string $message, string $type = 'info', array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * Send a notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $message, string $type = 'info', array $data = []): array
    {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'read_at' => null,
            ]);
        }

        return $notifications;
    }

    /**
     * Send a notification to all users
     */
    public function sendToAll(string $title, string $message, string $type = 'info', array $data = []): array
    {
        $userIds = User::pluck('id')->toArray();
        return $this->sendToUsers($userIds, $title, $message, $type, $data);
    }

    /**
     * Send a notification to users with a specific role
     */
    public function sendToRole(string $role, string $title, string $message, string $type = 'info', array $data = []): array
    {
        $userIds = User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->pluck('id')->toArray();

        return $this->sendToUsers($userIds, $title, $message, $type, $data);
    }

    /**
     * Send a notification to users with specific permissions
     */
    public function sendToPermission(string $permission, string $title, string $message, string $type = 'info', array $data = []): array
    {
        $userIds = User::whereHas('roles.permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->pluck('id')->toArray();

        return $this->sendToUsers($userIds, $title, $message, $type, $data);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for current user
     */
    public function getUnreadNotifications(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for current user with pagination
     */
    public function getAllNotifications(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notification::where('user_id', Auth::id())
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread notification count for current user
     */
    public function getUnreadCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(int $notificationId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->delete();
            return true;
        }

        return false;
    }

    /**
     * Clear all read notifications for current user
     */
    public function clearReadNotifications(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Send a payroll-related notification
     */
    public function sendPayrollNotification(string $type, array $data): ?Notification
    {
        $title = '';
        $message = '';
        $notificationType = 'info';
        $userIds = [];

        switch ($type) {
            case 'payroll_processed':
                $title = 'Payroll Processed';
                $message = 'Payroll for ' . $data['period'] . ' has been processed successfully.';
                $notificationType = 'success';
                $userIds = $this->getPayrollAdmins();
                break;

            case 'payroll_failed':
                $title = 'Payroll Processing Failed';
                $message = 'Payroll processing failed: ' . $data['error'];
                $notificationType = 'error';
                $userIds = $this->getPayrollAdmins();
                break;

            case 'salary_updated':
                $title = 'Salary Updated';
                $message = 'Your salary has been updated for ' . $data['effective_date'];
                $notificationType = 'success';
                $userIds = [$data['user_id']];
                break;

            case 'payslip_generated':
                $title = 'Payslip Available';
                $message = 'Your payslip for ' . $data['period'] . ' is now available.';
                $notificationType = 'info';
                $userIds = [$data['user_id']];
                break;

            case 'overtime_approved':
                $title = 'Overtime Approved';
                $message = 'Your overtime request for ' . $data['date'] . ' has been approved.';
                $notificationType = 'success';
                $userIds = [$data['user_id']];
                break;

            case 'overtime_rejected':
                $title = 'Overtime Rejected';
                $message = 'Your overtime request for ' . $data['date'] . ' has been rejected: ' . $data['reason'];
                $notificationType = 'warning';
                $userIds = [$data['user_id']];
                break;

            case 'leave_approved':
                $title = 'Leave Approved';
                $message = 'Your leave request for ' . $data['date_range'] . ' has been approved.';
                $notificationType = 'success';
                $userIds = [$data['user_id']];
                break;

            case 'leave_rejected':
                $title = 'Leave Rejected';
                $message = 'Your leave request for ' . $data['date_range'] . ' has been rejected: ' . $data['reason'];
                $notificationType = 'warning';
                $userIds = [$data['user_id']];
                break;
        }

        if (empty($userIds)) {
            return null;
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $notificationType,
                'data' => $data,
                'read_at' => null,
            ]);
        }

        return $notifications[0] ?? null;
    }

    /**
     * Send a system announcement
     */
    public function sendSystemAnnouncement(string $title, string $message, array $data = []): array
    {
        return $this->sendToAll($title, $message, 'announcement', $data);
    }

    /**
     * Send a security alert
     */
    public function sendSecurityAlert(User $user, string $type, array $data): Notification
    {
        $title = '';
        $message = '';

        switch ($type) {
            case 'login_failed':
                $title = 'Login Failed';
                $message = 'Failed login attempt detected from IP: ' . ($data['ip'] ?? 'Unknown');
                break;

            case 'password_changed':
                $title = 'Password Changed';
                $message = 'Your password was successfully changed.';
                break;

            case 'suspicious_activity':
                $title = 'Suspicious Activity';
                $message = 'Suspicious activity detected on your account.';
                break;

            case 'new_device':
                $title = 'New Device Login';
                $message = 'Your account was accessed from a new device: ' . ($data['device'] ?? 'Unknown');
                break;
        }

        return $this->sendToUser($user, $title, $message, 'security', array_merge($data, ['alert_type' => $type]));
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(): array
    {
        $userId = Auth::id();

        return [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->whereNull('read_at')->count(),
            'by_type' => Notification::where('user_id', $userId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recent' => Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get users with payroll admin role
     */
    protected function getPayrollAdmins(): array
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'payroll_admin', 'hr_manager']);
        })->pluck('id')->toArray();
    }

    /**
     * Cleanup old notifications
     */
    public function cleanupOldNotifications(int $daysToKeep = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysToKeep))
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Get notifications for broadcasting
     */
    public function getBroadcastableNotifications(User $user): array
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('broadcast_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => $notification->read_at?->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Mark notifications as broadcasted
     */
    public function markAsBroadcasted(array $notificationIds): int
    {
        return Notification::whereIn('id', $notificationIds)
            ->whereNull('broadcast_at')
            ->update(['broadcast_at' => now()]);
    }
}