<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reports & Analytics</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View system reports and statistics</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="card cursor-pointer hover:shadow-lg transition-shadow" @click="selectedReport = 'payroll'">
        <div class="card-body">
          <div class="flex items-center">
            <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-lg"><Icon name="heroicons:currency-dollar" class="w-8 h-8 text-primary-600" /></div>
            <div class="ml-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payroll Report</h3><p class="text-sm text-gray-600 dark:text-gray-400">Monthly payroll summary</p></div>
          </div>
        </div>
      </div>
      <div class="card cursor-pointer hover:shadow-lg transition-shadow" @click="selectedReport = 'attendance'">
        <div class="card-body">
          <div class="flex items-center">
            <div class="p-3 bg-success-100 dark:bg-success-900 rounded-lg"><Icon name="heroicons:calendar-days" class="w-8 h-8 text-success-600" /></div>
            <div class="ml-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance Report</h3><p class="text-sm text-gray-600 dark:text-gray-400">Employee attendance stats</p></div>
          </div>
        </div>
      </div>
      <div class="card cursor-pointer hover:shadow-lg transition-shadow" @click="selectedReport = 'leave'">
        <div class="card-body">
          <div class="flex items-center">
            <div class="p-3 bg-warning-100 dark:bg-warning-900 rounded-lg"><Icon name="heroicons:document-text" class="w-8 h-8 text-warning-600" /></div>
            <div class="ml-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leave Report</h3><p class="text-sm text-gray-600 dark:text-gray-400">Leave statistics</p></div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="selectedReport" class="card">
      <div class="card-body">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ reportTitle }}</h2>
          <button @click="generateReport" class="btn btn-primary"><Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />Export</button>
        </div>
        <div v-if="loading" class="text-center py-8"><div class="spinner w-8 h-8 mx-auto"></div></div>
        <div v-else-if="reportData" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="(stat, key) in reportData.stats" :key="key" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatKey(key) }}</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat }}</p>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead><tr><th v-for="col in reportData.columns" :key="col">{{ col }}</th></tr></thead>
              <tbody><tr v-for="(row, idx) in reportData.rows" :key="idx"><td v-for="(val, key) in row" :key="key">{{ val }}</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Reports' })
const api = useApi()
const toast = useToast()
const selectedReport = ref('')
const loading = ref(false)
const reportData = ref(null)
const reportTitle = computed(() => {
  const titles = { payroll: 'Payroll Summary Report', attendance: 'Attendance Summary Report', leave: 'Leave Summary Report' }
  return titles[selectedReport.value] || 'Report'
})
const generateReport = async () => {
  loading.value = true
  const endpoints = { payroll: '/reports/payroll-summary?month=1&year=2024', attendance: '/reports/attendance-summary?start_date=2024-01-01&end_date=2024-12-31', leave: '/reports/leave-summary?year=2024' }
  const { data, error } = await api.get(endpoints[selectedReport.value])
  if (error) toast.error(error)
  else {
    reportData.value = {
      stats: data,
      columns: Object.keys(data),
      rows: [data]
    }
    toast.success('Report generated!')
  }
  loading.value = false
}
const formatKey = (key: string) => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
watch(selectedReport, () => { if (selectedReport.value) generateReport() })
</script>
