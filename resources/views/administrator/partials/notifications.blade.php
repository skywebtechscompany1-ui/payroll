@php
    $notificationPosition = $notificationPosition ?? 'top-right';
    $maxNotifications = $maxNotifications ?? 5;
    $autoHideDelay = $autoHideDelay ?? 5000;
@endphp

<!-- Real-time Notification System -->
<div id="notification-container" class="notification-container notification-{{ $notificationPosition }}">
    <!-- Notifications will be dynamically added here -->
</div>

<!-- Notification Dropdown in Navigation -->
@if(Auth::check())
<div class="dropdown me-3">
    <button class="btn btn-link text-white position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notification-count">0</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px;" aria-labelledby="notificationDropdown">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <strong>Notifications</strong>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="mark-all-read" title="Mark all as read">
                    <i class="bi bi-check-all"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-notifications" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
        <div id="notification-list" class="notification-list">
            <!-- Notifications will be loaded here -->
        </div>
        <div class="dropdown-footer text-center p-2">
            <a href="{{ url('/notifications') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
</div>
@endif

<!-- Notification Styles -->
<style>
/* Notification Container Positions */
.notification-container {
    position: fixed;
    z-index: 1060;
    max-width: 400px;
    width: 100%;
    pointer-events: none;
}

.notification-top-right {
    top: 20px;
    right: 20px;
}

.notification-top-left {
    top: 20px;
    left: 20px;
}

.notification-bottom-right {
    bottom: 20px;
    right: 20px;
}

.notification-bottom-left {
    bottom: 20px;
    left: 20px;
}

/* Notification Styles */
.notification {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    margin-bottom: 10px;
    overflow: hidden;
    pointer-events: all;
    animation: slideIn 0.3s ease-out;
    position: relative;
    border-left: 4px solid var(--notification-color, #007bff);
}

.notification.notification-success {
    --notification-color: #28a745;
}

.notification.notification-warning {
    --notification-color: #ffc107;
}

.notification.notification-error {
    --notification-color: #dc3545;
}

.notification.notification-info {
    --notification-color: #17a2b8;
}

.notification.notification-security {
    --notification-color: #dc3545;
}

.notification.notification-announcement {
    --notification-color: #007bff;
}

.notification-header {
    display: flex;
    align-items: center;
    padding: 12px 15px 8px;
    border-bottom: 1px solid #f1f3f4;
}

.notification-icon {
    margin-right: 10px;
    font-size: 18px;
}

.notification-title {
    flex: 1;
    font-weight: 600;
    color: #333;
    margin: 0;
    font-size: 14px;
}

.notification-close {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    font-size: 16px;
    line-height: 1;
}

.notification-close:hover {
    color: #495057;
}

.notification-body {
    padding: 8px 15px 12px;
}

.notification-message {
    color: #666;
    font-size: 14px;
    line-height: 1.4;
    margin: 0;
}

.notification-actions {
    padding: 8px 15px 12px;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.notification-actions .btn {
    font-size: 12px;
    padding: 4px 8px;
}

/* Notification Dropdown Styles */
.notification-dropdown {
    padding: 0;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f8ff;
    border-left: 3px solid #007bff;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 8px;
    width: 6px;
    height: 6px;
    background: #007bff;
    border-radius: 50%;
    transform: translateY(-50%);
}

.notification-item-content {
    display: flex;
    align-items: flex-start;
}

.notification-item-icon {
    margin-right: 10px;
    margin-top: 2px;
    font-size: 16px;
}

.notification-item-text {
    flex: 1;
}

.notification-item-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
    font-size: 13px;
}

.notification-item-message {
    color: #666;
    font-size: 12px;
    line-height: 1.3;
    margin-bottom: 4px;
}

.notification-item-time {
    color: #999;
    font-size: 11px;
}

.notification-badge {
    font-size: 10px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
}

.dropdown-footer {
    border-top: 1px solid #f1f3f4;
}

/* Animations */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInLeft {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-top-left .notification {
    animation: slideInLeft 0.3s ease-out;
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.notification.removing {
    animation: slideOut 0.3s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
    .notification-container {
        left: 10px !important;
        right: 10px !important;
        top: auto !important;
        bottom: 10px !important;
        max-width: none;
    }

    .notification-dropdown {
        width: 300px !important;
    }
}

/* Dark mode */
[data-bs-theme="dark"] .notification {
    background: #2d3748;
    color: #e2e8f0;
}

[data-bs-theme="dark"] .notification-title {
    color: #e2e8f0;
}

[data-bs-theme="dark"] .notification-message {
    color: #a0aec0;
}

[data-bs-theme="dark"] .notification-header {
    border-bottom-color: #4a5568;
}

[data-bs-theme="dark"] .notification-item {
    border-bottom-color: #4a5568;
}

[data-bs-theme="dark"] .notification-item:hover {
    background-color: #4a5568;
}

[data-bs-theme="dark"] .notification-item.unread {
    background-color: #2c5282;
}

[data-bs-theme="dark"] .notification-item-title {
    color: #e2e8f0;
}

[data-bs-theme="dark"] .notification-item-message {
    color: #a0aec0;
}

[data-bs-theme="dark"] .notification-dropdown {
    background: #2d3748;
    border-color: #4a5568;
}

[data-bs-theme="dark"] .dropdown-header {
    background: #4a5568;
    color: #e2e8f0;
    border-bottom-color: #4a5568;
}

[data-bs-theme="dark"] .dropdown-footer {
    border-top-color: #4a5568;
}

/* Loading state */
.notification-loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.notification-empty {
    text-align: center;
    padding: 20px;
    color: #6c757d;
    font-style: italic;
}
</style>

<!-- Notification JavaScript -->
<script>
class NotificationManager {
    constructor() {
        this.container = document.getElementById('notification-container');
        this.dropdown = document.getElementById('notificationDropdown');
        this.list = document.getElementById('notification-list');
        this.badge = document.getElementById('notification-count');
        this.notifications = [];
        this.maxNotifications = {{ $maxNotifications }};
        this.autoHideDelay = {{ $autoHideDelay }};
        this.userId = {{ Auth::id() ?? 'null' }};
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadNotifications();
        this.initWebSocket();
        this.setupPolling();
    }

    setupEventListeners() {
        // Mark all as read
        document.getElementById('mark-all-read')?.addEventListener('click', () => {
            this.markAllAsRead();
        });

        // Refresh notifications
        document.getElementById('refresh-notifications')?.addEventListener('click', () => {
            this.loadNotifications();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeDropdown();
            }
        });

        // Handle keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDropdown();
            }
        });
    }

    async loadNotifications() {
        try {
            this.showLoading();

            const response = await fetch('/api/notifications', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load notifications');

            const data = await response.json();
            this.notifications = data.notifications || [];
            this.renderNotificationList();
            this.updateBadge();
            this.closeLoading();

        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError();
        }
    }

    renderNotificationList() {
        if (!this.list) return;

        if (this.notifications.length === 0) {
            this.list.innerHTML = `
                <div class="notification-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }

        this.list.innerHTML = this.notifications.map(notification => this.renderNotificationItem(notification)).join('');

        // Add click handlers
        this.list.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const id = parseInt(item.dataset.id);
                this.handleNotificationClick(id, e);
            });
        });
    }

    renderNotificationItem(notification) {
        const unreadClass = notification.read_at ? '' : 'unread';
        const iconClass = this.getIconClass(notification.type);

        return `
            <div class="notification-item ${unreadClass}" data-id="${notification.id}">
                <div class="notification-item-content">
                    <div class="notification-item-icon">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notification-item-text">
                        <div class="notification-item-title">${this.escapeHtml(notification.title)}</div>
                        <div class="notification-item-message">${this.escapeHtml(notification.message)}</div>
                        <div class="notification-item-time">${this.formatTime(notification.created_at)}</div>
                    </div>
                </div>
            </div>
        `;
    }

    showNotification(notification, options = {}) {
        const autoHide = options.autoHide !== false;
        const duration = options.duration || this.autoHideDelay;
        const id = `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

        const notificationEl = document.createElement('div');
        notificationEl.className = `notification notification-${notification.type}`;
        notificationEl.id = id;

        const iconClass = this.getIconClass(notification.type);

        notificationEl.innerHTML = `
            <div class="notification-header">
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                <button class="notification-close" onclick="notificationManager.closeNotification('${id}')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="notification-body">
                <p class="notification-message">${this.escapeHtml(notification.message)}</p>
            </div>
            ${notification.action_url ? `
                <div class="notification-actions">
                    <button class="btn btn-sm btn-primary" onclick="notificationManager.handleAction('${notification.action_url}')">
                        ${notification.action_text || 'View Details'}
                    </button>
                </div>
            ` : ''}
        `;

        // Add to container
        this.container.appendChild(notificationEl);

        // Remove old notifications if we have too many
        const notifications = this.container.querySelectorAll('.notification');
        if (notifications.length > this.maxNotifications) {
            const oldNotification = notifications[0];
            this.closeNotification(oldNotification.id);
        }

        // Auto hide
        if (autoHide) {
            setTimeout(() => {
                this.closeNotification(id);
            }, duration);
        }

        // Play sound if enabled
        if (options.sound !== false && this.canPlaySound()) {
            this.playNotificationSound(notification.type);
        }

        // Request browser permission for desktop notifications
        this.requestBrowserPermission(notification);
    }

    closeNotification(id) {
        const notification = document.getElementById(id);
        if (notification) {
            notification.classList.add('removing');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }

    async handleNotificationClick(id, event) {
        event.preventDefault();
        event.stopPropagation();

        const notification = this.notifications.find(n => n.id === id);
        if (!notification) return;

        // Mark as read
        if (!notification.read_at) {
            await this.markAsRead(id);
        }

        // Handle action URL
        if (notification.action_url) {
            window.location.href = notification.action_url;
        }

        // Close dropdown
        this.closeDropdown();
    }

    async markAsRead(id) {
        try {
            const response = await fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update local notification
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                    this.renderNotificationList();
                    this.updateBadge();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                this.notifications.forEach(n => n.read_at = new Date().toISOString());
                this.renderNotificationList();
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    updateBadge() {
        if (!this.badge) return;

        const unreadCount = this.notifications.filter(n => !n.read_at).length;

        if (unreadCount > 0) {
            this.badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            this.badge.style.display = 'inline-block';
        } else {
            this.badge.style.display = 'none';
        }
    }

    initWebSocket() {
        if (!this.userId) return;

        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws/notifications/${this.userId}`;

        try {
            this.ws = new WebSocket(wsUrl);

            this.ws.onopen = () => {
                console.log('WebSocket connected for notifications');
                this.reconnectAttempts = 0;
            };

            this.ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleWebSocketMessage(data);
            };

            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.attemptReconnect();
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };

        } catch (error) {
            console.error('Failed to initialize WebSocket:', error);
        }
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = Math.pow(2, this.reconnectAttempts) * 1000; // Exponential backoff

            setTimeout(() => {
                console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
                this.initWebSocket();
            }, delay);
        }
    }

    handleWebSocketMessage(data) {
        if (data.type === 'notification') {
            this.showNotification(data.notification, { sound: true });
            this.notifications.unshift(data.notification);
            this.renderNotificationList();
            this.updateBadge();
        }
    }

    setupPolling() {
        // Fallback polling method
        setInterval(() => {
            if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
                this.loadNotifications();
            }
        }, 30000); // Poll every 30 seconds
    }

    getIconClass(type) {
        const iconClasses = {
            'success': 'bi-check-circle-fill text-success',
            'warning': 'bi-exclamation-triangle-fill text-warning',
            'error': 'bi-x-circle-fill text-danger',
            'info': 'bi-info-circle-fill text-info',
            'security': 'bi-shield-fill text-danger',
            'announcement': 'bi-megaphone-fill text-primary'
        };

        return iconClasses[type] || iconClasses.info;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;

        return date.toLocaleDateString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    closeDropdown() {
        const dropdown = bootstrap.Dropdown.getInstance(this.dropdown);
        if (dropdown) {
            dropdown.hide();
        }
    }

    showLoading() {
        if (this.list) {
            this.list.innerHTML = '<div class="notification-loading">Loading...</div>';
        }
    }

    closeLoading() {
        // Loading state will be replaced by renderNotificationList
    }

    showError() {
        if (this.list) {
            this.list.innerHTML = '<div class="notification-empty">Error loading notifications</div>';
        }
    }

    canPlaySound() {
        // Check if user prefers reduced motion
        return !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    playNotificationSound(type) {
        try {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignore errors (user may not have interacted with page yet)
            });
        } catch (error) {
            // Ignore audio errors
        }
    }

    requestBrowserPermission(notification) {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showBrowserNotification(notification);
                }
            });
        } else if ('Notification' in window && Notification.permission === 'granted') {
            this.showBrowserNotification(notification);
        }
    }

    showBrowserNotification(notification) {
        try {
            const browserNotification = new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.ico',
                tag: 'payroll-notification',
                requireInteraction: notification.type === 'error' || notification.type === 'security'
            });

            browserNotification.onclick = () => {
                window.focus();
                browserNotification.close();
                if (notification.action_url) {
                    window.location.href = notification.action_url;
                }
            };

            // Auto close after 5 seconds (unless requireInteraction is true)
            setTimeout(() => {
                browserNotification.close();
            }, 5000);
        } catch (error) {
            console.error('Error showing browser notification:', error);
        }
    }

    handleAction(url) {
        window.location.href = url;
    }
}

// Initialize notification manager
let notificationManager;
document.addEventListener('DOMContentLoaded', function() {
    notificationManager = new NotificationManager();
});

// Make available globally
window.notificationManager = notificationManager;
</script>