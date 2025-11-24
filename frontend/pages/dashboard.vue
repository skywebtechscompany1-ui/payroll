<template>
  <div>
    <!-- Page header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Dashboard
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Welcome back, {{ auth.user?.name }}!
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <button class="btn btn-outline">
            <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />
            Export Report
          </button>
          <button class="btn btn-primary">
            <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
            Quick Actions
          </button>
        </div>
      </div>
    </template>

    <!-- Stats grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.name" class="card">
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center',
                stat.iconBg
              ]">
                <Icon :name="stat.icon" :class="['w-4 h-4', stat.iconColor]" />
              </div>
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
            </div>
            <div class="flex-shrink-0">
              <span :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                stat.trend > 0 ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' :
                stat.trend < 0 ? 'bg-error-100 text-error-800 dark:bg-error-900 dark:text-error-200' :
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
              ]">
                <Icon :name="stat.trend > 0 ? 'heroicons:arrow-trending-up' : stat.trend < 0 ? 'heroicons:arrow-trending-down' : 'heroicons:minus'" class="w-3 h-3 mr-1" />
                {{ Math.abs(stat.trend) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and recent activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Payroll trend chart -->
      <div class="lg:col-span-2 card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Payroll Trend
          </h3>
          <div class="flex items-center space-x-2">
            <button class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
              Last 6 months
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="h-64">
            <LineChart
              :chart-data="payrollChartData"
              :chart-options="chartOptions"
            />
          </div>
        </div>
      </div>

      <!-- Department distribution -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Department Distribution
          </h3>
        </div>
        <div class="card-body">
          <div class="h-64">
            <DoughnutChart
              :chart-data="departmentChartData"
              :chart-options="doughnutChartOptions"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Recent activity and quick actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent activity -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Recent Activity
          </h3>
          <NuxtLink to="/activity" class="text-sm text-primary-600 hover:text-primary-500">
            View all
          </NuxtLink>
        </div>
        <div class="card-body">
          <div class="space-y-4">
            <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-3">
              <div :class="[
                'w-2 h-2 rounded-full mt-2',
                activity.type === 'success' ? 'bg-success-400' :
                activity.type === 'warning' ? 'bg-warning-400' :
                activity.type === 'error' ? 'bg-error-400' :
                'bg-primary-400'
              ]"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 dark:text-white">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatTime(activity.timestamp) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Quick Actions
          </h3>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="action in quickActions"
              :key="action.name"
              @click="handleQuickAction(action)"
              :class="[
                'p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200 text-center',
                action.enabled ? 'cursor-pointer hover:border-primary-300 dark:hover:border-primary-600' : 'cursor-not-allowed opacity-50'
              ]"
              :disabled="!action.enabled"
            >
              <Icon :name="action.icon" class="w-8 h-8 mx-auto mb-2 text-gray-600 dark:text-gray-400" />
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ action.name }}</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const auth = useAuth()

// Reactive data
const stats = ref([
  {
    name: 'Total Employees',
    value: '156',
    icon: 'heroicons:users',
    iconBg: 'bg-primary-100 dark:bg-primary-900',
    iconColor: 'text-primary-600 dark:text-primary-400',
    trend: 5.2
  },
  {
    name: 'Active Today',
    value: '142',
    icon: 'heroicons:user-check',
    iconBg: 'bg-success-100 dark:bg-success-900',
    iconColor: 'text-success-600 dark:text-success-400',
    trend: 2.1
  },
  {
    name: 'Pending Leaves',
    value: '8',
    icon: 'heroicons:calendar',
    iconBg: 'bg-warning-100 dark:bg-warning-900',
    iconColor: 'text-warning-600 dark:text-warning-400',
    trend: -12.5
  },
  {
    name: 'Monthly Payroll',
    value: 'KES 2.4M',
    icon: 'heroicons:banknotes',
    iconBg: 'bg-secondary-100 dark:bg-secondary-900',
    iconColor: 'text-secondary-600 dark:text-secondary-400',
    trend: 8.7
  }
])

const recentActivity = ref([
  {
    id: 1,
    description: 'New employee John Doe joined the IT department',
    type: 'success',
    timestamp: new Date(Date.now() - 1000 * 60 * 5)
  },
  {
    id: 2,
    description: 'Payroll processing for November completed',
    type: 'success',
    timestamp: new Date(Date.now() - 1000 * 60 * 30)
  },
  {
    id: 3,
    description: 'Leave request from Jane Smith needs approval',
    type: 'warning',
    timestamp: new Date(Date.now() - 1000 * 60 * 60)
  },
  {
    id: 4,
    description: 'System backup completed successfully',
    type: 'success',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2)
  },
  {
    id: 5,
    description: 'Failed to send email notifications',
    type: 'error',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 3)
  }
])

const quickActions = ref([
  {
    name: 'Add Employee',
    icon: 'heroicons:user-plus',
    enabled: auth.hasPermission('employees:create'),
    route: '/employees/create'
  },
  {
    name: 'Process Payroll',
    icon: 'heroicons:banknotes',
    enabled: auth.hasPermission('payroll:process'),
    route: '/payroll/process'
  },
  {
    name: 'Generate Report',
    icon: 'heroicons:document-text',
    enabled: auth.hasPermission('reports:create'),
    route: '/reports/create'
  },
  {
    name: 'Approve Leave',
    icon: 'heroicons:check-circle',
    enabled: auth.hasPermission('leave:approve'),
    route: '/leave/approvals'
  }
])

// Chart data
const payrollChartData = ref({
  labels: ['June', 'July', 'August', 'September', 'October', 'November'],
  datasets: [
    {
      label: 'Gross Payroll',
      data: [2200000, 2350000, 2400000, 2300000, 2450000, 2400000],
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4
    },
    {
      label: 'Net Payroll',
      data: [1800000, 1900000, 1950000, 1850000, 2000000, 1950000],
      borderColor: 'rgb(5, 150, 105)',
      backgroundColor: 'rgba(5, 150, 105, 0.1)',
      tension: 0.4
    }
  ]
})

const departmentChartData = ref({
  labels: ['IT', 'HR', 'Finance', 'Operations', 'Sales'],
  datasets: [
    {
      data: [35, 15, 20, 25, 5],
      backgroundColor: [
        'rgba(59, 130, 246, 0.8)',
        'rgba(5, 150, 105, 0.8)',
        'rgba(217, 119, 6, 0.8)',
        'rgba(100, 116, 139, 0.8)',
        'rgba(220, 38, 38, 0.8)'
      ]
    }
  ]
})

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
      labels: {
        color: '#6b7280'
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: '#6b7280'
      }
    },
    x: {
      ticks: {
        color: '#6b7280'
      }
    }
  }
})

const doughnutChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right' as const,
      labels: {
        color: '#6b7280'
      }
    }
  }
})

// Methods
const formatTime = (timestamp: Date) => {
  const now = new Date()
  const diff = now.getTime() - timestamp.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 60) {
    return `${minutes} minute${minutes > 1 ? 's' : ''} ago`
  } else if (hours < 24) {
    return `${hours} hour${hours > 1 ? 's' : ''} ago`
  } else {
    return `${days} day${days > 1 ? 's' : ''} ago`
  }
}

const handleQuickAction = (action: any) => {
  if (action.enabled) {
    navigateTo(action.route)
  }
}

// Page metadata
useHead({
  title: 'Dashboard'
})
</script>