<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payments</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Make payments and generate payslips</p>
      </div>
      <button @click="showMakePaymentModal = true" class="btn btn-primary">
        <Icon name="heroicons:banknotes" class="w-4 h-4 mr-2" />Make Payment
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="form-label">Search Employee</label>
            <input v-model="filters.search" @input="loadItems" type="text" placeholder="Search by name..." class="form-input" />
          </div>
          <div>
            <label class="form-label">Month</label>
            <select v-model="filters.month" @change="loadItems" class="form-select">
              <option value="">All Months</option>
              <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Year</label>
            <input v-model.number="filters.year" @change="loadItems" type="number" class="form-input" />
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.status" @change="loadItems" class="form-select">
              <option value="">All Status</option>
              <option value="1">Pending</option>
              <option value="2">Processing</option>
              <option value="3">Completed</option>
              <option value="4">Failed</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:banknotes" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No payments found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.employee_name }}</td>
                <td>{{ formatCurrency(item.amount) }}</td>
                <td>{{ formatDate(item.payment_date) }}</td>
                <td><span class="badge badge-info">{{ item.payment_method }}</span></td>
                <td class="text-sm">{{ item.reference_number || 'N/A' }}</td>
                <td>
                  <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="downloadPayslip(item)" class="btn btn-sm btn-success">
                      <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-1" />Payslip
                    </button>
                    <button @click="deletePayment(item)" class="btn btn-sm btn-error">
                      <Icon name="heroicons:trash" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Make Payment Modal -->
    <div v-if="showMakePaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeMakePaymentModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Make Payment</h2>
          <form @submit.prevent="makePayment" class="space-y-4">
            <div>
              <label class="form-label">Select Employee *</label>
              <select v-model="paymentForm.employee_id" required class="form-select">
                <option value="">Select Employee</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                  {{ emp.name }}
                </option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Amount *</label>
                <input v-model.number="paymentForm.amount" type="number" required min="0" step="0.01" class="form-input" />
              </div>
              <div>
                <label class="form-label">Payment Date *</label>
                <input v-model="paymentForm.payment_date" type="date" required class="form-input" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Payment Type *</label>
                <select v-model="paymentForm.payment_type" required class="form-select">
                  <option value="salary">Salary</option>
                  <option value="bonus">Bonus</option>
                  <option value="advance">Advance</option>
                  <option value="reimbursement">Reimbursement</option>
                </select>
              </div>
              <div>
                <label class="form-label">Payment Method *</label>
                <select v-model="paymentForm.payment_method" required class="form-select">
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash">Cash</option>
                  <option value="cheque">Cheque</option>
                  <option value="mobile_money">Mobile Money</option>
                </select>
              </div>
            </div>
            <div>
              <label class="form-label">Reference Number</label>
              <input v-model="paymentForm.reference_number" type="text" class="form-input" />
            </div>
            <div>
              <label class="form-label">Description</label>
              <textarea v-model="paymentForm.description" class="form-input" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeMakePaymentModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">
                {{ saving ? 'Processing...' : 'Make Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Payments' })

const api = useApi()
const toast = useToast()

const items = ref([])
const employees = ref([])
const loading = ref(false)
const saving = ref(false)
const showMakePaymentModal = ref(false)

const filters = ref({
  search: '',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  status: ''
})

const paymentForm = ref({
  employee_id: '',
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_type: 'salary',
  payment_method: 'bank_transfer',
  reference_number: '',
  description: ''
})

const loadItems = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.month) params.append('month', filters.value.month.toString())
  if (filters.value.year) params.append('year', filters.value.year.toString())
  if (filters.value.status) params.append('status', filters.value.status)
  
  const { data, error } = await api.get(`/payments?${params.toString()}`)
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (!error) employees.value = data.items
}

const makePayment = async () => {
  saving.value = true
  const { error } = await api.post('/payments', paymentForm.value)
  if (error) toast.error(error)
  else {
    toast.success('Payment created successfully!')
    closeMakePaymentModal()
    loadItems()
  }
  saving.value = false
}

const downloadPayslip = async (payment: any) => {
  // Find the payroll record for this payment
  const { data, error } = await api.get(`/payslips?employee_id=${payment.employee_id}&month=${filters.value.month}&year=${filters.value.year}`)
  
  if (error || !data.items || data.items.length === 0) {
    toast.error('No payslip found for this payment')
    return
  }
  
  const payslipId = data.items[0].id
  
  // Get payslip data
  const { data: payslipData, error: payslipError } = await api.get(`/payslips/${payslipId}/download`)
  
  if (payslipError) {
    toast.error('Failed to generate payslip')
    return
  }
  
  // Generate PDF (simplified - in production use a proper PDF library)
  generatePayslipPDF(payslipData)
  toast.success('Payslip downloaded!')
}

const generatePayslipPDF = (data: any) => {
  // Create a printable HTML document
  const printWindow = window.open('', '_blank')
  if (!printWindow) return
  
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Payslip - ${data.employee.name}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .total { font-weight: bold; font-size: 1.2em; }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>${data.company.name}</h1>
        <p>${data.company.address}</p>
        <h2>PAYSLIP</h2>
        <p>${data.period.month_name} ${data.period.year}</p>
      </div>
      
      <div class="section">
        <h3>Employee Details</h3>
        <table>
          <tr><td>Name:</td><td>${data.employee.name}</td></tr>
          <tr><td>Employee Number:</td><td>${data.employee.employee_number}</td></tr>
          <tr><td>Designation:</td><td>${data.employee.designation}</td></tr>
          <tr><td>KRA PIN:</td><td>${data.employee.kra_pin}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <h3>Earnings</h3>
        <table>
          ${data.earnings.map(e => `<tr><td>${e.description}</td><td>KES ${e.amount.toFixed(2)}</td></tr>`).join('')}
          <tr class="total"><td>Gross Salary</td><td>KES ${data.totals.gross_salary.toFixed(2)}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <h3>Deductions</h3>
        <table>
          ${data.deductions.map(d => `<tr><td>${d.description}</td><td>KES ${d.amount.toFixed(2)}</td></tr>`).join('')}
          <tr class="total"><td>Total Deductions</td><td>KES ${data.totals.total_deductions.toFixed(2)}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <h3>Net Pay</h3>
        <table>
          <tr class="total"><td>NET SALARY</td><td>KES ${data.totals.net_salary.toFixed(2)}</td></tr>
        </table>
      </div>
      
      <div class="section">
        <p>Payment Date: ${data.payment.date || 'Pending'}</p>
        <p>Generated: ${data.generated_at}</p>
      </div>
    </body>
    </html>
  `)
  
  printWindow.document.close()
  printWindow.print()
}

const deletePayment = async (payment: any) => {
  if (!confirm('Are you sure you want to delete this payment?')) return
  const { error } = await api.delete(`/payments/${payment.id}`)
  if (error) toast.error(error)
  else {
    toast.success('Payment deleted!')
    loadItems()
  }
}

const closeMakePaymentModal = () => {
  showMakePaymentModal.value = false
  paymentForm.value = {
    employee_id: '',
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_type: 'salary',
    payment_method: 'bank_transfer',
    reference_number: '',
    description: ''
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString()
}

const getMonthName = (month: number) => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return months[month - 1] || ''
}

const getStatusClass = (status: string) => {
  const classes = {
    'Pending': 'badge badge-warning',
    'Processing': 'badge badge-info',
    'Completed': 'badge badge-success',
    'Failed': 'badge badge-error'
  }
  return classes[status] || 'badge badge-info'
}

onMounted(() => {
  loadItems()
  loadEmployees()
})
</script>
