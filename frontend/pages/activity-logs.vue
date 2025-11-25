<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Activity Logs</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track user activities and system events</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="form-label">Module</label>
            <select v-model="filters.module" @change="loadItems" class="form-select">
              <option value="">All Modules</option>
              <option value="auth">Authentication</option>
              <option value="users">Users</option>
              <option value="payroll">Payroll</option>
              <option value="leave">Leave</option>
              <option value="attendance">Attendance</option>
            </select>
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.status" @change="loadItems" class="form-select">
              <option value="">All Status</option>
              <option value="success">Success</option>
              <option value="failed">Failed</option>
              <option value="error">Error</option>
            </select>
          </div>
          <div>
            <label class="form-label">Start Date</label>
            <input v-model="filters.start_date" @change="loadItems" type="date" class="form-input" />
          </div>
          <div>
            <label class="form-label">End Date</label>
            <input v-model="filters.end_date" @change="loadItems" type="date" class="form-input" />
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:clipboard-document-list" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No activity logs found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Date/Time</th>
                <th>User</th>
                <th>Module</th>
                <th>Action</th>
                <th>Description</th>
                <th>Status</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="text-sm">{{ formatDateTime(item.created_at) }}</td>
                <td class="font-medium">User #{{ item.user_id }}</td>
                <td><span class="badge badge-primary">{{ item.module }}</span></td>
                <td>{{ item.action }}</td>
                <td class="text-sm text-gray-600">{{ item.description || 'N/A' }}</td>
                <td>
                  <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                </td>
                <td class="text-sm">{{ item.ip_address || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Activity Logs' })

const api = useApi()
const toast = useToast()

const items = ref([])
const loading = ref(false)
const filters = ref({
  module: '',
  status: '',
  start_date: '',
  end_date: ''
})

const loadItems = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.module) params.append('module', filters.value.module)
  if (filters.value.status) params.append('status', filters.value.status)
  if (filters.value.start_date) params.append('start_date', filters.value.start_date)
  if (filters.value.end_date) params.append('end_date', filters.value.end_date)
  
  const { data, error } = await api.get(`/activity-logs?${params.toString()}`)
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const formatDateTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleString()
}

const getStatusClass = (status: string) => {
  const classes = {
    success: 'badge badge-success',
    failed: 'badge badge-warning',
    error: 'badge badge-error'
  }
  return classes[status] || 'badge badge-info'
}

onMounted(() => loadItems())
</script>
