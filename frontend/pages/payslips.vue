<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payslips</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and download employee payslips</p>
      </div>
      <button @click="downloadBulkPayslips" :disabled="items.length === 0" class="btn btn-primary">
        <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />Download All Payslips
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <h3 class="text-lg font-semibold mb-4">Filter Payslips</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="form-label">Employee</label>
            <select v-model="filters.employee_id" @change="loadPayslips" class="form-select">
              <option value="">All Employees</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="form-label">Month *</label>
            <select v-model="filters.month" @change="loadPayslips" required class="form-select">
              <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Year *</label>
            <input v-model.number="filters.year" @change="loadPayslips" type="number" required class="form-input" />
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.status" @change="loadPayslips" class="form-select">
              <option value="">All Status</option>
              <option value="1">Draft</option>
              <option value="2">Processed</option>
              <option value="3">Paid</option>
              <option value="4">Cancelled</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="loadPayslips" class="btn btn-primary w-full">
              <Icon name="heroicons:magnifying-glass" class="w-4 h-4 mr-2" />Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payslips Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading payslips...</p>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:document-text" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No payslips found for the selected filters</p>
        </div>
        <div v-else>
          <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Found {{ items.length }} payslip(s) for {{ getMonthName(filters.month) }} {{ filters.year }}
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" @change="toggleSelectAll" :checked="allSelected" class="form-checkbox" />
                  </th>
                  <th>Employee</th>
                  <th>Employee Number</th>
                  <th>Period</th>
                  <th>Basic Salary</th>
                  <th>Gross Salary</th>
                  <th>Deductions</th>
                  <th>Net Salary</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in items" :key="item.id">
                  <td>
                    <input type="checkbox" v-model="selectedItems" :value="item.id" class="form-checkbox" />
                  </td>
                  <td class="font-medium">{{ item.employee_name }}</td>
                  <td>{{ item.employee_number }}</td>
                  <td>{{ getMonthName(item.month) }} {{ item.year }}</td>
                  <td>{{ formatCurrency(item.basic_salary) }}</td>
                  <td>{{ formatCurrency(item.gross_salary) }}</td>
                  <td class="text-red-600">{{ formatCurrency(item.total_deductions) }}</td>
                  <td class="font-bold text-green-600">{{ formatCurrency(item.net_salary) }}</td>
                  <td>
                    <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                  </td>
                  <td>
                    <div class="flex space-x-2">
                      <button @click="viewPayslip(item)" class="btn btn-sm btn-info" title="View Details">
                        <Icon name="heroicons:eye" class="w-4 h-4" />
                      </button>
                      <button @click="downloadPayslip(item)" class="btn btn-sm btn-success" title="Download PDF">
                        <Icon name="heroicons:document-arrow-down" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Bulk Actions -->
          <div v-if="selectedItems.length > 0" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">{{ selectedItems.length }} payslip(s) selected</span>
              <div class="flex gap-2">
                <button @click="downloadSelectedPayslips" class="btn btn-sm btn-primary">
                  <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-1" />Download Selected
                </button>
                <button @click="selectedItems = []" class="btn btn-sm btn-outline">Clear Selection</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- View Payslip Modal -->
    <div v-if="showViewModal && selectedPayslip" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeViewModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Payslip Details</h2>
            <button @click="closeViewModal" class="btn btn-sm btn-ghost">
              <Icon name="heroicons:x-mark" class="w-5 h-5" />
            </button>
          </div>
          
          <div id="payslip-content">
            <!-- Employee Info -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Employee Name</p>
                  <p class="font-semibold">{{ selectedPayslip.employee.name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Employee Number</p>
                  <p class="font-semibold">{{ selectedPayslip.employee.employee_number }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Designation</p>
                  <p class="font-semibold">{{ selectedPayslip.employee.designation || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Period</p>
                  <p class="font-semibold">{{ getMonthName(selectedPayslip.period.month) }} {{ selectedPayslip.period.year }}</p>
                </div>
              </div>
            </div>

            <!-- Earnings -->
            <div class="mb-4">
              <h3 class="text-lg font-semibold mb-2">Earnings</h3>
              <table class="table">
                <thead>
                  <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Basic Salary</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.basic_salary) }}</td>
                  </tr>
                  <tr>
                    <td>House Allowance</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.house_allowance) }}</td>
                  </tr>
                  <tr>
                    <td>Transport Allowance</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.transport_allowance) }}</td>
                  </tr>
                  <tr>
                    <td>Medical Allowance</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.medical_allowance) }}</td>
                  </tr>
                  <tr>
                    <td>Other Allowances</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.other_allowances) }}</td>
                  </tr>
                  <tr>
                    <td>Overtime</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.overtime_amount) }}</td>
                  </tr>
                  <tr class="font-bold bg-blue-50 dark:bg-blue-900/20">
                    <td>Gross Salary</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.earnings.gross_salary) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Deductions -->
            <div class="mb-4">
              <h3 class="text-lg font-semibold mb-2">Deductions</h3>
              <table class="table">
                <thead>
                  <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>NSSF</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.nssf) }}</td>
                  </tr>
                  <tr>
                    <td>NHIF</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.nhif) }}</td>
                  </tr>
                  <tr>
                    <td>PAYE</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.paye) }}</td>
                  </tr>
                  <tr>
                    <td>Loan Deduction</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.loan_deduction) }}</td>
                  </tr>
                  <tr>
                    <td>Other Deductions</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.other_deductions) }}</td>
                  </tr>
                  <tr class="font-bold bg-red-50 dark:bg-red-900/20">
                    <td>Total Deductions</td>
                    <td class="text-right">{{ formatCurrency(selectedPayslip.deductions.total_deductions) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Net Salary -->
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">NET SALARY</span>
                <span class="text-3xl font-bold text-green-600">{{ formatCurrency(selectedPayslip.net_salary) }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button @click="closeViewModal" class="btn btn-outline">Close</button>
            <button @click="downloadSinglePayslip(selectedPayslip)" class="btn btn-primary">
              <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />Download PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Payslips' })

const api = useApi()
const toast = useToast()

const items = ref([])
const employees = ref([])
const loading = ref(false)
const showViewModal = ref(false)
const selectedPayslip = ref(null)
const selectedItems = ref([])

const filters = ref({
  employee_id: '',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  status: ''
})

const allSelected = computed(() => {
  return items.value.length > 0 && selectedItems.value.length === items.value.length
})

const loadPayslips = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.employee_id) params.append('employee_id', filters.value.employee_id)
  if (filters.value.month) params.append('month', filters.value.month.toString())
  if (filters.value.year) params.append('year', filters.value.year.toString())
  if (filters.value.status) params.append('status', filters.value.status)
  
  const { data, error } = await api.get(`/payslips?${params.toString()}`)
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (!error) employees.value = data.items
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = items.value.map(item => item.id)
  }
}

const viewPayslip = async (payslip: any) => {
  const { data, error } = await api.get(`/payslips/${payslip.id}`)
  if (error) {
    toast.error('Failed to load payslip details')
    return
  }
  selectedPayslip.value = data
  showViewModal.value = true
}

const downloadPayslip = async (payslip: any) => {
  const { data, error } = await api.get(`/payslips/${payslip.id}/download`)
  if (error) {
    toast.error('Failed to generate payslip')
    return
  }
  generatePayslipPDF(data)
  toast.success('Payslip downloaded!')
}

const downloadSinglePayslip = async (payslip: any) => {
  const { data, error } = await api.get(`/payslips/${payslip.id}/download`)
  if (error) {
    toast.error('Failed to generate payslip')
    return
  }
  generatePayslipPDF(data)
  toast.success('Payslip downloaded!')
}

const downloadSelectedPayslips = async () => {
  for (const id of selectedItems.value) {
    const payslip = items.value.find(item => item.id === id)
    if (payslip) {
      await downloadPayslip(payslip)
    }
  }
  toast.success(`Downloaded ${selectedItems.value.length} payslip(s)!`)
}

const downloadBulkPayslips = async () => {
  const { data, error } = await api.get(`/payslips/bulk/download?month=${filters.value.month}&year=${filters.value.year}${filters.value.employee_id ? '&employee_id=' + filters.value.employee_id : ''}`)
  if (error) {
    toast.error('Failed to generate payslips')
    return
  }
  
  // Download each payslip
  for (const payslip of data.payslips) {
    const { data: payslipData } = await api.get(`/payslips/${payslip.payslip_id}/download`)
    if (payslipData) {
      generatePayslipPDF(payslipData)
    }
  }
  
  toast.success(`Downloaded ${data.total} payslip(s)!`)
}

const generatePayslipPDF = (data: any) => {
  const printWindow = window.open('', '_blank')
  if (!printWindow) return
  
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Payslip - ${data.employee.name}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header h2 { margin: 10px 0; color: #666; }
        .section { margin: 25px 0; }
        .section h3 { background: #f0f0f0; padding: 10px; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f8f8; font-weight: bold; }
        .amount { text-align: right; }
        .total { font-weight: bold; font-size: 1.1em; background: #f0f0f0; }
        .net-pay { background: #4CAF50; color: white; padding: 15px; text-align: center; font-size: 1.5em; margin-top: 20px; }
        .footer { margin-top: 40px; text-align: center; font-size: 0.9em; color: #666; }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>${data.company.name}</h1>
        <p>${data.company.address} | ${data.company.phone}</p>
        <h2>PAYSLIP</h2>
        <p><strong>${data.period.month_name} ${data.period.year}</strong></p>
      </div>
      
      <div class="section">
        <h3>Employee Information</h3>
        <table>
          <tr><td><strong>Name:</strong></td><td>${data.employee.name}</td></tr>
          <tr><td><strong>Employee Number:</strong></td><td>${data.employee.employee_number}</td></tr>
          <tr><td><strong>Designation:</strong></td><td>${data.employee.designation}</td></tr>
          <tr><td><strong>Department:</strong></td><td>${data.employee.department}</td></tr>
          <tr><td><strong>KRA PIN:</strong></td><td>${data.employee.kra_pin}</td></tr>
          <tr><td><strong>NSSF No:</strong></td><td>${data.employee.nssf_no}</td></tr>
          <tr><td><strong>NHIF No:</strong></td><td>${data.employee.nhif_no}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <h3>Earnings</h3>
        <table>
          ${data.earnings.filter(e => e.amount > 0).map(e => `
            <tr><td>${e.description}</td><td class="amount">KES ${e.amount.toLocaleString('en-KE', {minimumFractionDigits: 2})}</td></tr>
          `).join('')}
          <tr class="total"><td>Gross Salary</td><td class="amount">KES ${data.totals.gross_salary.toLocaleString('en-KE', {minimumFractionDigits: 2})}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <h3>Deductions</h3>
        <table>
          ${data.deductions.filter(d => d.amount > 0).map(d => `
            <tr><td>${d.description}</td><td class="amount">KES ${d.amount.toLocaleString('en-KE', {minimumFractionDigits: 2})}</td></tr>
          `).join('')}
          <tr class="total"><td>Total Deductions</td><td class="amount">KES ${data.totals.total_deductions.toLocaleString('en-KE', {minimumFractionDigits: 2})}</td></tr>
        </table>
      </div>
      
      <div class="net-pay">
        <strong>NET SALARY: KES ${data.totals.net_salary.toLocaleString('en-KE', {minimumFractionDigits: 2})}</strong>
      </div>
      
      <div class="section">
        <h3>Payment Details</h3>
        <table>
          <tr><td><strong>Bank:</strong></td><td>${data.employee.bank_name}</td></tr>
          <tr><td><strong>Account Number:</strong></td><td>${data.employee.account_number}</td></tr>
          <tr><td><strong>Payment Date:</strong></td><td>${data.payment.date || 'Pending'}</td></tr>
          <tr><td><strong>Payment Method:</strong></td><td>${data.payment.method || 'Bank Transfer'}</td></tr>
        </table>
      </div>
      
      <div class="footer">
        <p>This is a computer-generated payslip and does not require a signature.</p>
        <p>Generated on: ${data.generated_at}</p>
      </div>
    </body>
    </html>
  `)
  
  printWindow.document.close()
  setTimeout(() => printWindow.print(), 250)
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedPayslip.value = null
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
}

const getMonthName = (month: number) => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return months[month - 1] || ''
}

const getStatusClass = (status: string) => {
  const classes = {
    'Draft': 'badge badge-warning',
    'Processed': 'badge badge-info',
    'Paid': 'badge badge-success',
    'Cancelled': 'badge badge-error'
  }
  return classes[status] || 'badge badge-info'
}

onMounted(() => {
  loadPayslips()
  loadEmployees()
})
</script>
