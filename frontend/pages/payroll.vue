<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payroll Processing</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage salary calculations and payments</p>
      </div>
      <button v-if="auth.hasPermission('payroll:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Process Payroll
      </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Draft</p><p class="text-2xl font-bold text-warning-600">{{ stats.draft }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Processed</p><p class="text-2xl font-bold text-primary-600">{{ stats.processed }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Paid</p><p class="text-2xl font-bold text-success-600">{{ stats.paid }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Total Amount</p><p class="text-2xl font-bold text-gray-900 dark:text-white">${{ stats.totalAmount.toLocaleString() }}</p></div></div>
    </div>
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><label class="form-label">Month</label><select v-model="filters.month" class="form-select"><option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option></select></div>
          <div><label class="form-label">Year</label><input v-model="filters.year" type="number" class="form-input" /></div>
          <div class="flex items-end"><button @click="loadPayroll" class="btn btn-outline w-full"><Icon name="heroicons:magnifying-glass" class="w-4 h-4 mr-2" />Search</button></div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8"><div class="spinner w-8 h-8 mx-auto"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-8"><Icon name="heroicons:currency-dollar" class="w-16 h-16 mx-auto text-gray-400" /><p class="mt-2 text-gray-600 dark:text-gray-400">No payroll records</p></div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead><tr><th>Employee</th><th>Period</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>Employee #{{ item.employee_id }}</td>
                <td>{{ getMonthName(item.month) }} {{ item.year }}</td>
                <td>${{ item.gross_salary?.toLocaleString() }}</td>
                <td>${{ item.total_deductions?.toLocaleString() }}</td>
                <td class="font-bold">${{ item.net_salary?.toLocaleString() }}</td>
                <td><span :class="getStatusBadge(item.status)">{{ getStatusName(item.status) }}</span></td>
                <td>
                  <div class="flex space-x-2">
                    <button v-if="item.status === 1" @click="processPayroll(item)" class="btn btn-sm btn-primary" title="Process"><Icon name="heroicons:play" class="w-4 h-4" /></button>
                    <button v-if="item.status === 2" @click="markAsPaid(item)" class="btn btn-sm btn-success" title="Mark Paid"><Icon name="heroicons:check" class="w-4 h-4" /></button>
                    <button @click="viewPayslip(item)" class="btn btn-sm btn-ghost" title="View"><Icon name="heroicons:document-text" class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Process Payroll</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div><label class="form-label">Employee ID *</label><input v-model="form.employee_id" type="number" required class="form-input" /></div>
              <div><label class="form-label">Month *</label><select v-model="form.month" required class="form-select"><option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option></select></div>
              <div><label class="form-label">Year *</label><input v-model="form.year" type="number" required class="form-input" /></div>
              <div><label class="form-label">Basic Salary *</label><input v-model="form.basic_salary" type="number" required class="form-input" step="0.01" /></div>
              <div><label class="form-label">House Allowance</label><input v-model="form.house_allowance" type="number" class="form-input" step="0.01" /></div>
              <div><label class="form-label">Transport Allowance</label><input v-model="form.transport_allowance" type="number" class="form-input" step="0.01" /></div>
              <div><label class="form-label">Tax Deduction</label><input v-model="form.tax_deduction" type="number" class="form-input" step="0.01" /></div>
              <div><label class="form-label">Other Deductions</label><input v-model="form.other_deductions" type="number" class="form-input" step="0.01" /></div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">{{ saving ? 'Saving...' : 'Save' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Payroll' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const stats = ref({ draft: 0, processed: 0, paid: 0, totalAmount: 0 })
const filters = ref({ month: new Date().getMonth() + 1, year: new Date().getFullYear() })
const form = ref({ employee_id: null, month: new Date().getMonth() + 1, year: new Date().getFullYear(), basic_salary: 0, house_allowance: 0, transport_allowance: 0, tax_deduction: 0, other_deductions: 0 })
const loadPayroll = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.month) params.append('month', filters.value.month.toString())
  if (filters.value.year) params.append('year', filters.value.year.toString())
  const { data, error } = await api.get(`/payroll?${params}`)
  if (error) toast.error(error)
  else {
    items.value = data.items
    stats.value = {
      draft: items.value.filter((i: any) => i.status === 1).length,
      processed: items.value.filter((i: any) => i.status === 2).length,
      paid: items.value.filter((i: any) => i.status === 3).length,
      totalAmount: items.value.reduce((sum: number, i: any) => sum + (i.net_salary || 0), 0)
    }
  }
  loading.value = false
}
const saveItem = async () => {
  saving.value = true
  const { error } = await api.post('/payroll', form.value)
  if (error) toast.error(error)
  else { toast.success('Payroll created!'); closeModal(); loadPayroll() }
  saving.value = false
}
const processPayroll = async (item: any) => {
  if (!confirm('Process this payroll?')) return
  const { error } = await api.post(`/payroll/${item.id}/process`, {})
  if (error) toast.error(error)
  else { toast.success('Processed!'); loadPayroll() }
}
const markAsPaid = async (item: any) => {
  const reference = prompt('Payment reference:')
  if (!reference) return
  const { error } = await api.post(`/payroll/${item.id}/pay`, { payment_method: 'Bank Transfer', payment_reference: reference })
  if (error) toast.error(error)
  else { toast.success('Marked as paid!'); loadPayroll() }
}
const viewPayslip = (item: any) => { toast.info('Payslip view coming soon') }
const closeModal = () => { showAddModal.value = false; form.value = { employee_id: null, month: new Date().getMonth() + 1, year: new Date().getFullYear(), basic_salary: 0, house_allowance: 0, transport_allowance: 0, tax_deduction: 0, other_deductions: 0 } }
const getMonthName = (month: number) => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][month - 1]
const getStatusName = (status: number) => ['', 'Draft', 'Processed', 'Paid', 'Cancelled'][status]
const getStatusBadge = (status: number) => ['', 'badge badge-warning', 'badge badge-primary', 'badge badge-success', 'badge badge-error'][status]
onMounted(() => loadPayroll())
</script>
