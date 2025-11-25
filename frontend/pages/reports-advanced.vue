<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Advanced Reports</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Generate comprehensive reports for employees, payroll, tax, and leave</p>
      </div>
      <button @click="exportReport" class="btn btn-primary" :disabled="!reportData">
        <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />Export Report
      </button>
    </div>

    <!-- Report Type Selector -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <button @click="reportType = 'employee'" :class="reportType === 'employee' ? 'btn btn-primary' : 'btn btn-outline'">
            <Icon name="heroicons:users" class="w-4 h-4 mr-2" />Employee Report
          </button>
          <button @click="reportType = 'payroll'" :class="reportType === 'payroll' ? 'btn btn-primary' : 'btn btn-outline'">
            <Icon name="heroicons:currency-dollar" class="w-4 h-4 mr-2" />Payroll Report
          </button>
          <button @click="reportType = 'tax'" :class="reportType === 'tax' ? 'btn btn-primary' : 'btn btn-outline'">
            <Icon name="heroicons:document-text" class="w-4 h-4 mr-2" />Tax Report
          </button>
          <button @click="reportType = 'p9'" :class="reportType === 'p9' ? 'btn btn-primary' : 'btn btn-outline'">
            <Icon name="heroicons:clipboard-document-check" class="w-4 h-4 mr-2" />P9 Report
          </button>
          <button @click="reportType = 'leave'" :class="reportType === 'leave' ? 'btn btn-primary' : 'btn btn-outline'">
            <Icon name="heroicons:calendar-days" class="w-4 h-4 mr-2" />Leave Report
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div v-if="reportType !== 'p9'">
            <label class="form-label">Frequency</label>
            <select v-model="filters.frequency" class="form-select">
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div v-if="['payroll', 'tax', 'leave'].includes(reportType)">
            <label class="form-label">Month</label>
            <select v-model="filters.month" class="form-select">
              <option value="">All Months</option>
              <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Year *</label>
            <input v-model.number="filters.year" type="number" required class="form-input" :min="2020" :max="2030" />
          </div>
          <div v-if="reportType === 'employee'">
            <label class="form-label">Start Date</label>
            <input v-model="filters.start_date" type="date" class="form-input" />
          </div>
          <div v-if="reportType === 'employee'">
            <label class="form-label">End Date</label>
            <input v-model="filters.end_date" type="date" class="form-input" />
          </div>
          <div class="flex items-end">
            <button @click="generateReport" class="btn btn-primary w-full">
              <Icon name="heroicons:document-chart-bar" class="w-4 h-4 mr-2" />Generate Report
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Display -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Generating report...</p>
        </div>
        <div v-else-if="!reportData" class="text-center py-8">
          <Icon name="heroicons:document-chart-bar" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">Select report type and click Generate Report</p>
        </div>
        <div v-else>
          <!-- Employee Report -->
          <div v-if="reportType === 'employee'">
            <h3 class="text-lg font-semibold mb-4">Employee Report</h3>
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Designation</th>
                    <th>Attendance</th>
                    <th>Leave Days</th>
                    <th>Total Earnings</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="emp in reportData.employees" :key="emp.employee_id">
                    <td class="font-medium">{{ emp.employee_name }}</td>
                    <td>{{ emp.designation || 'N/A' }}</td>
                    <td>{{ emp.attendance.present }}/{{ emp.attendance.total_days }}</td>
                    <td>{{ emp.leave.total_days }}</td>
                    <td>{{ formatCurrency(emp.payroll.total_net) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Payroll Report -->
          <div v-if="reportType === 'payroll'">
            <h3 class="text-lg font-semibold mb-4">Detailed Payroll Report</h3>
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Gross</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(reportData.summary?.total_gross_salary) }}</p>
              </div>
              <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Deductions</p>
                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(reportData.summary?.total_deductions) }}</p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Net</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(reportData.summary?.total_net_salary) }}</p>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Basic</th>
                    <th>Allowances</th>
                    <th>Gross</th>
                    <th>Deductions</th>
                    <th>Net</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in reportData.records" :key="record.employee_id">
                    <td class="font-medium">{{ record.employee_name }}</td>
                    <td>{{ formatCurrency(record.basic_salary) }}</td>
                    <td>{{ formatCurrency(record.allowances.house + record.allowances.transport + record.allowances.medical) }}</td>
                    <td>{{ formatCurrency(record.gross_salary) }}</td>
                    <td>{{ formatCurrency(record.total_deductions) }}</td>
                    <td>{{ formatCurrency(record.net_salary) }}</td>
                    <td><span class="badge badge-success">{{ record.status }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tax Report -->
          <div v-if="reportType === 'tax'">
            <h3 class="text-lg font-semibold mb-4">Tax Report (PAYE, NSSF, NHIF)</h3>
            <div class="grid grid-cols-4 gap-4 mb-6">
              <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total NSSF</p>
                <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(reportData.summary?.total_nssf) }}</p>
              </div>
              <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total NHIF</p>
                <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(reportData.summary?.total_nhif) }}</p>
              </div>
              <div class="bg-pink-50 dark:bg-pink-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total PAYE</p>
                <p class="text-2xl font-bold text-pink-600">{{ formatCurrency(reportData.summary?.total_paye) }}</p>
              </div>
              <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Tax</p>
                <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(reportData.summary?.total_tax) }}</p>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>KRA PIN</th>
                    <th>NSSF No</th>
                    <th>NHIF No</th>
                    <th>Gross</th>
                    <th>NSSF</th>
                    <th>NHIF</th>
                    <th>PAYE</th>
                    <th>Total Tax</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in reportData.records" :key="record.employee_id">
                    <td class="font-medium">{{ record.employee_name }}</td>
                    <td>{{ record.kra_pin || 'N/A' }}</td>
                    <td>{{ record.nssf_no || 'N/A' }}</td>
                    <td>{{ record.nhif_no || 'N/A' }}</td>
                    <td>{{ formatCurrency(record.gross_salary) }}</td>
                    <td>{{ formatCurrency(record.nssf) }}</td>
                    <td>{{ formatCurrency(record.nhif) }}</td>
                    <td>{{ formatCurrency(record.paye) }}</td>
                    <td>{{ formatCurrency(record.total_tax) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- P9 Report -->
          <div v-if="reportType === 'p9'">
            <h3 class="text-lg font-semibold mb-4">P9 Tax Deduction Card - Year {{ filters.year }}</h3>
            <div v-for="emp in reportData.employees" :key="emp.employee_id" class="mb-8 border-b pb-6">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                <h4 class="font-semibold text-lg">{{ emp.employee_name }}</h4>
                <div class="grid grid-cols-3 gap-4 mt-2 text-sm">
                  <div><span class="text-gray-600">Employee No:</span> {{ emp.employee_number }}</div>
                  <div><span class="text-gray-600">KRA PIN:</span> {{ emp.kra_pin || 'N/A' }}</div>
                  <div><span class="text-gray-600">NSSF No:</span> {{ emp.nssf_no || 'N/A' }}</div>
                </div>
              </div>
              <div class="overflow-x-auto">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Month</th>
                      <th>Gross Salary</th>
                      <th>NSSF</th>
                      <th>NHIF</th>
                      <th>PAYE</th>
                      <th>Net Salary</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="month in emp.monthly_data" :key="month.month">
                      <td>{{ getMonthName(month.month) }}</td>
                      <td>{{ formatCurrency(month.gross_salary) }}</td>
                      <td>{{ formatCurrency(month.nssf) }}</td>
                      <td>{{ formatCurrency(month.nhif) }}</td>
                      <td>{{ formatCurrency(month.paye) }}</td>
                      <td>{{ formatCurrency(month.net_salary) }}</td>
                    </tr>
                    <tr class="font-bold bg-gray-100 dark:bg-gray-700">
                      <td>TOTAL</td>
                      <td>{{ formatCurrency(emp.totals.gross_salary) }}</td>
                      <td>{{ formatCurrency(emp.totals.nssf) }}</td>
                      <td>{{ formatCurrency(emp.totals.nhif) }}</td>
                      <td>{{ formatCurrency(emp.totals.paye) }}</td>
                      <td>{{ formatCurrency(emp.totals.net_salary) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Leave Report -->
          <div v-if="reportType === 'leave'">
            <h3 class="text-lg font-semibold mb-4">Detailed Leave Report</h3>
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Total Requests</th>
                    <th>Total Days</th>
                    <th>Sick Leave</th>
                    <th>Annual Leave</th>
                    <th>Casual Leave</th>
                    <th>Other</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="emp in reportData.employees" :key="emp.employee_id">
                    <td class="font-medium">{{ emp.employee_name }}</td>
                    <td>{{ emp.total_requests }}</td>
                    <td>{{ emp.total_days_taken }}</td>
                    <td>{{ emp.leave_types['Sick Leave']?.days || 0 }}</td>
                    <td>{{ emp.leave_types['Annual Leave']?.days || 0 }}</td>
                    <td>{{ emp.leave_types['Casual Leave']?.days || 0 }}</td>
                    <td>{{ calculateOtherLeave(emp.leave_types) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Advanced Reports' })

const api = useApi()
const toast = useToast()

const reportType = ref('employee')
const loading = ref(false)
const reportData = ref(null)
const filters = ref({
  frequency: 'monthly',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  start_date: '',
  end_date: ''
})

const generateReport = async () => {
  loading.value = true
  reportData.value = null
  
  let endpoint = ''
  const params = new URLSearchParams()
  
  switch (reportType.value) {
    case 'employee':
      endpoint = '/reports/employee-detailed'
      if (filters.value.start_date) params.append('start_date', filters.value.start_date)
      if (filters.value.end_date) params.append('end_date', filters.value.end_date)
      break
    case 'payroll':
      endpoint = '/reports/payroll-detailed'
      if (filters.value.month) params.append('month', filters.value.month.toString())
      params.append('year', filters.value.year.toString())
      params.append('frequency', filters.value.frequency)
      break
    case 'tax':
      endpoint = '/reports/tax-report'
      if (filters.value.month) params.append('month', filters.value.month.toString())
      params.append('year', filters.value.year.toString())
      break
    case 'p9':
      endpoint = '/reports/p9-report'
      params.append('year', filters.value.year.toString())
      break
    case 'leave':
      endpoint = '/reports/leave-detailed'
      params.append('year', filters.value.year.toString())
      break
  }
  
  const { data, error } = await api.get(`${endpoint}?${params.toString()}`)
  if (error) {
    toast.error(error)
  } else {
    reportData.value = data
    toast.success('Report generated successfully!')
  }
  loading.value = false
}

const exportReport = () => {
  if (!reportData.value) {
    toast.error('Please generate a report first')
    return
  }
  toast.info('Export functionality: Download as CSV/PDF')
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
}

const getMonthName = (month: number) => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return months[month - 1] || ''
}

const calculateOtherLeave = (leaveTypes: any) => {
  let total = 0
  for (const [key, value] of Object.entries(leaveTypes)) {
    if (!['Sick Leave', 'Annual Leave', 'Casual Leave'].includes(key)) {
      total += (value as any).days || 0
    }
  }
  return total
}
</script>
