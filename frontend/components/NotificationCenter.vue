<template>
  <div>
    <!-- Notification Bell Button -->
    <button
      @click="toggleNotifications"
      class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 relative"
    >
      <Icon name="heroicons:bell" class="w-6 h-6" />
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex items-center justify-center h-5 w-5 text-xs font-bold text-white bg-error-500 rounded-full animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Popup -->
    <div
      v-if="showNotifications"
      class="fixed inset-0 z-50 overflow-hidden"
      @click.self="closeNotifications"
    >
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeNotifications"></div>
      
      <div class="absolute right-4 top-20 w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-2">
            <Icon name="heroicons:bell" class="w-5 h-5 text-primary-600" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
            <span v-if="unreadCount > 0" class="px-2 py-0.5 text-xs font-bold text-white bg-error-500 rounded-full">
              {{ unreadCount }}
            </span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-primary-600 hover:text-primary-700 font-medium"
            >
              Mark all read
            </button>
            <button @click="closeNotifications" class="text-gray-400 hover:text-gray-600">
              <Icon name="heroicons:x-mark" class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-700">
          <button
            @click="activeTab = 'all'"
            :class="[
              'flex-1 px-4 py-3 text-sm font-medium transition-colors',
              activeTab === 'all'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
            ]"
          >
            All ({{ notifications.length }})
          </button>
          <button
            @click="activeTab = 'unread'"
            :class="[
              'flex-1 px-4 py-3 text-sm font-medium transition-colors',
              activeTab === 'unread'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
            ]"
          >
            Unread ({{ unreadCount }})
          </button>
          <button
            v-if="auth.isAdmin"
            @click="activeTab = 'activities'"
            :class="[
              'flex-1 px-4 py-3 text-sm font-medium transition-colors',
              activeTab === 'activities'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'
            ]"
          >
            Activities
          </button>
        </div>

        <!-- Notifications List -->
        <div class="flex-1 overflow-y-auto p-2">
          <div v-if="loading" class="flex items-center justify-center py-12">
            <Icon name="heroicons:arrow-path" class="w-8 h-8 text-gray-400 animate-spin" />
          </div>

          <div v-else-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-12">
            <Icon name="heroicons:bell-slash" class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
            <p class="text-gray-500 dark:text-gray-400 text-sm">No notifications</p>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="notification in filteredNotifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              :class="[
                'p-4 rounded-lg cursor-pointer transition-all hover:shadow-md',
                notification.read
                  ? 'bg-gray-50 dark:bg-gray-700/50'
                  : 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500'
              ]"
            >
              <div class="flex items-start space-x-3">
                <!-- Icon -->
                <div :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
                  getNotificationColor(notification.type)
                ]">
                  <Icon :name="getNotificationIcon(notification.type)" class="w-5 h-5" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ notification.title }}
                    </p>
                    <button
                      @click.stop="toggleRead(notification)"
                      class="ml-2 text-gray-400 hover:text-gray-600"
                    >
                      <Icon
                        :name="notification.read ? 'heroicons:envelope-open' : 'heroicons:envelope'"
                        class="w-4 h-4"
                      />
                    </button>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ notification.message }}
                  </p>
                  <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                      {{ formatTime(notification.timestamp) }}
                    </span>
                    <span
                      v-if="notification.action"
                      class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                    >
                      {{ notification.action }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="viewAllNotifications"
            class="w-full text-center text-sm text-primary-600 hover:text-primary-700 font-medium"
          >
            View All Notifications
          </button>
        </div>
      </div>
    </div>

    <!-- Activity Logs Popup (Admin Only) -->
    <div
      v-if="showActivityLogs && auth.isAdmin"
      class="fixed inset-0 z-50 overflow-hidden"
      @click.self="closeActivityLogs"
    >
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeActivityLogs"></div>
      
      <div class="absolute right-4 top-20 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-2">
            <Icon name="heroicons:clipboard-document-list" class="w-5 h-5 text-primary-600" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activity Logs</h3>
          </div>
          <button @click="closeActivityLogs" class="text-gray-400 hover:text-gray-600">
            <Icon name="heroicons:x-mark" class="w-5 h-5" />
          </button>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-3">
            <select v-model="activityFilter" class="form-select text-sm">
              <option value="all">All Activities</option>
              <option value="login">Login Attempts</option>
              <option value="success">Successful Logins</option>
              <option value="failed">Failed Logins</option>
              <option value="logout">Logouts</option>
              <option value="create">Create Actions</option>
              <option value="update">Update Actions</option>
              <option value="delete">Delete Actions</option>
            </select>
            <button @click="refreshActivityLogs" class="btn btn-outline btn-sm">
              <Icon name="heroicons:arrow-path" class="w-4 h-4 mr-1" />
              Refresh
            </button>
          </div>
        </div>

        <!-- Activity List -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="loadingActivities" class="flex items-center justify-center py-12">
            <Icon name="heroicons:arrow-path" class="w-8 h-8 text-gray-400 animate-spin" />
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="activity in filteredActivities"
              :key="activity.id"
              class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
              <div class="flex items-start space-x-3">
                <!-- Status Icon -->
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                  getActivityColor(activity.type)
                ]">
                  <Icon :name="getActivityIcon(activity.type)" class="w-4 h-4" />
                </div>

                <!-- Content -->
                <div class="flex-1">
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ activity.user }}
                      </p>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ activity.action }}
                      </p>
                    </div>
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      activity.status === 'success' ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' :
                      activity.status === 'failed' ? 'bg-error-100 text-error-800 dark:bg-error-900 dark:text-error-200' :
                      'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'
                    ]">
                      {{ activity.status }}
                    </span>
                  </div>

                  <!-- Details -->
                  <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center">
                      <Icon name="heroicons:globe-alt" class="w-3 h-3 mr-1" />
                      {{ activity.ip_address }}
                    </div>
                    <div class="flex items-center">
                      <Icon name="heroicons:device-phone-mobile" class="w-3 h-3 mr-1" />
                      {{ activity.device }}
                    </div>
                    <div class="flex items-center">
                      <Icon name="heroicons:window" class="w-3 h-3 mr-1" />
                      {{ activity.browser }}
                    </div>
                    <div class="flex items-center">
                      <Icon name="heroicons:clock" class="w-3 h-3 mr-1" />
                      {{ formatTime(activity.timestamp) }}
                    </div>
                  </div>

                  <!-- Location -->
                  <div v-if="activity.location" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <Icon name="heroicons:map-pin" class="w-3 h-3 inline mr-1" />
                    {{ activity.location }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const auth = useAuth()
const api = useApi()
const toast = useToast()

// State
const showNotifications = ref(false)
const showActivityLogs = ref(false)
const activeTab = ref('all')
const loading = ref(false)
const loadingActivities = ref(false)
const activityFilter = ref('all')

const notifications = ref([
  {
    id: 1,
    type: 'success',
    title: 'Leave Approved',
    message: 'Your annual leave request has been approved',
    timestamp: new Date(Date.now() - 1000 * 60 * 5),
    read: false,
    action: 'View Details'
  },
  {
    id: 2,
    type: 'warning',
    title: 'Payroll Processing',
    message: 'Monthly payroll will be processed tomorrow',
    timestamp: new Date(Date.now() - 1000 * 60 * 30),
    read: false
  },
  {
    id: 3,
    type: 'info',
    title: 'New Employee',
    message: 'John Doe joined the IT department',
    timestamp: new Date(Date.now() - 1000 * 60 * 60),
    read: true
  }
])

const activityLogs = ref([
  {
    id: 1,
    user: 'admin@jafasol.com',
    action: 'Successful login',
    type: 'login',
    status: 'success',
    ip_address: '192.168.1.100',
    device: 'Desktop',
    browser: 'Chrome 120',
    location: 'Nairobi, Kenya',
    timestamp: new Date(Date.now() - 1000 * 60 * 2)
  },
  {
    id: 2,
    user: 'user@example.com',
    action: 'Failed login attempt',
    type: 'login',
    status: 'failed',
    ip_address: '192.168.1.105',
    device: 'Mobile',
    browser: 'Safari 17',
    location: 'Mombasa, Kenya',
    timestamp: new Date(Date.now() - 1000 * 60 * 15)
  }
])

// Computed
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

const filteredNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notifications.value.filter(n => !n.read)
  }
  return notifications.value
})

const filteredActivities = computed(() => {
  if (activityFilter.value === 'all') return activityLogs.value
  return activityLogs.value.filter(a => a.type === activityFilter.value || a.status === activityFilter.value)
})

// Methods
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    loadNotifications()
  }
}

const closeNotifications = () => {
  showNotifications.value = false
}

const closeActivityLogs = () => {
  showActivityLogs.value = false
}

const loadNotifications = async () => {
  loading.value = true
  try {
    const { data, error } = await api.get('/notifications')
    if (!error && data) {
      notifications.value = data.map((n: any) => ({
        ...n,
        timestamp: new Date(n.timestamp)
      }))
    }
  } catch (err) {
    console.error('Failed to load notifications:', err)
  } finally {
    loading.value = false
  }
}

const loadActivityLogs = async () => {
  loadingActivities.value = true
  try {
    const { data, error } = await api.get('/activity-logs')
    if (!error && data) {
      activityLogs.value = data.map((a: any) => ({
        ...a,
        timestamp: new Date(a.timestamp)
      }))
    }
  } catch (err) {
    console.error('Failed to load activity logs:', err)
  } finally {
    loadingActivities.value = false
  }
}

const refreshActivityLogs = () => {
  loadActivityLogs()
  toast.success('Activity logs refreshed')
}

const handleNotificationClick = (notification: any) => {
  if (!notification.read) {
    toggleRead(notification)
  }
  // Handle navigation based on notification type
  if (notification.link) {
    navigateTo(notification.link)
    closeNotifications()
  }
}

const toggleRead = async (notification: any) => {
  notification.read = !notification.read
  try {
    await api.post(`/notifications/${notification.id}/toggle-read`)
  } catch (err) {
    console.error('Failed to toggle read status:', err)
  }
}

const markAllAsRead = async () => {
  notifications.value.forEach(n => n.read = true)
  try {
    await api.post('/notifications/mark-all-read')
    toast.success('All notifications marked as read')
  } catch (err) {
    console.error('Failed to mark all as read:', err)
  }
}

const viewAllNotifications = () => {
  navigateTo('/notifications')
  closeNotifications()
}

const formatTime = (timestamp: Date) => {
  const now = new Date()
  const diff = now.getTime() - timestamp.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return timestamp.toLocaleDateString()
}

const getNotificationIcon = (type: string) => {
  const icons = {
    success: 'heroicons:check-circle',
    error: 'heroicons:x-circle',
    warning: 'heroicons:exclamation-triangle',
    info: 'heroicons:information-circle'
  }
  return icons[type as keyof typeof icons] || 'heroicons:bell'
}

const getNotificationColor = (type: string) => {
  const colors = {
    success: 'bg-success-100 text-success-600 dark:bg-success-900 dark:text-success-400',
    error: 'bg-error-100 text-error-600 dark:bg-error-900 dark:text-error-400',
    warning: 'bg-warning-100 text-warning-600 dark:bg-warning-900 dark:text-warning-400',
    info: 'bg-primary-100 text-primary-600 dark:bg-primary-900 dark:text-primary-400'
  }
  return colors[type as keyof typeof colors] || colors.info
}

const getActivityIcon = (type: string) => {
  const icons = {
    login: 'heroicons:arrow-right-on-rectangle',
    logout: 'heroicons:arrow-left-on-rectangle',
    create: 'heroicons:plus-circle',
    update: 'heroicons:pencil-square',
    delete: 'heroicons:trash'
  }
  return icons[type as keyof typeof icons] || 'heroicons:clipboard-document-list'
}

const getActivityColor = (type: string) => {
  const colors = {
    login: 'bg-success-100 text-success-600',
    logout: 'bg-gray-100 text-gray-600',
    create: 'bg-primary-100 text-primary-600',
    update: 'bg-warning-100 text-warning-600',
    delete: 'bg-error-100 text-error-600'
  }
  return colors[type as keyof typeof colors] || 'bg-gray-100 text-gray-600'
}

// Load data on mount
onMounted(() => {
  if (auth.isAdmin) {
    loadActivityLogs()
  }
})

// Expose methods for parent components
defineExpose({
  showActivityLogs: () => {
    showActivityLogs.value = true
    loadActivityLogs()
  }
})
</script>
