<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Salary Management</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage employee salary structures</p>
      </div>
      <button v-if="auth.hasPermission('salary:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Add Salary Structure
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Employee</label>
            <select v-model="filters.employee_id" @change="loadItems" class="form-select">
              <option value="">All Employees</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.is_active" @change="loadItems" class="form-select">
              <option value="">All</option>
              <option value="true">Active</option>
              <option value="false">Inactive</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Salary Structures Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:currency-dollar" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No salary structures found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Basic Salary</th>
                <th>Gross Salary</th>
                <th>Net Salary</th>
                <th>Frequency</th>
                <th>Effective From</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">Employee #{{ item.employee_id }}</td>
                <td>{{ formatCurrency(item.basic_salary) }}</td>
                <td>{{ formatCurrency(item.gross_salary) }}</td>
                <td>{{ formatCurrency(item.net_salary) }}</td>
                <td><span class="badge badge-primary">{{ item.payment_frequency }}</span></td>
                <td>{{ formatDate(item.effective_from) }}</td>
                <td>
                  <span :class="item.is_active ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="viewItem(item)" class="btn btn-sm btn-ghost">
                      <Icon name="heroicons:eye" class="w-4 h-4" />
                    </button>
                    <button @click="editItem(item)" class="btn btn-sm btn-ghost">
                      <Icon name="heroicons:pencil" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit' : 'Add' }} Salary Structure
          </h2>
          <form @submit.prevent="saveItem" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Employee *</label>
                <select v-model="form.employee_id" required class="form-select" :disabled="showEditModal">
                  <option value="">Select Employee</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                </select>
              </div>
              <div>
                <label class="form-label">Effective From *</label>
                <input v-model="form.effective_from" type="date" required class="form-input" />
              </div>
            </div>

            <div>
              <h3 class="text-lg font-semibold mb-3">Salary Components</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Basic Salary *</label>
                  <input v-model.number="form.basic_salary" type="number" required min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">House Allowance</label>
                  <input v-model.number="form.house_allowance" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Transport Allowance</label>
                  <input v-model.number="form.transport_allowance" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Medical Allowance</label>
                  <input v-model.number="form.medical_allowance" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Communication Allowance</label>
                  <input v-model.number="form.communication_allowance" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Meal Allowance</label>
                  <input v-model.number="form.meal_allowance" type="number" min="0" step="0.01" class="form-input" />
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-semibold mb-3">Deductions</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="form-label">NSSF Rate (%)</label>
                  <input v-model.number="form.nssf_rate" type="number" min="0" max="100" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">NHIF Amount</label>
                  <input v-model.number="form.nhif_amount" type="number" min="0" step="0.01" class="form-input" />
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Payment Frequency *</label>
                <select v-model="form.payment_frequency" required class="form-select">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>
              <div>
                <label class="form-label">Payment Method</label>
                <input v-model="form.payment_method" type="text" class="form-input" />
              </div>
            </div>

            <div>
              <label class="form-label">Notes</label>
              <textarea v-model="form.notes" class="form-input" rows="3"></textarea>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">
                {{ saving ? 'Saving...' : 'Save' }}
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
useHead({ title: 'Salary Management' })

const auth = useAuth()
const api = useApi()
const toast = useToast()

const items = ref([])
const employees = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const filters = ref({
  employee_id: '',
  is_active: ''
})
const form = ref({
  employee_id: '',
  basic_salary: 0,
  house_allowance: 0,
  transport_allowance: 0,
  medical_allowance: 0,
  communication_allowance: 0,
  meal_allowance: 0,
  other_allowances: 0,
  nssf_rate: 6.00,
  nhif_amount: 0,
  payment_frequency: 'monthly',
  payment_method: 'bank_transfer',
  effective_from: new Date().toISOString().split('T')[0],
  notes: ''
})

const loadItems = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.employee_id) params.append('employee_id', filters.value.employee_id)
  if (filters.value.is_active) params.append('is_active', filters.value.is_active)
  
  const { data, error } = await api.get(`/salary-structures?${params.toString()}`)
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (!error) employees.value = data.items
}

const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value
    ? await api.put(`/salary-structures/${form.value.id}`, form.value)
    : await api.post('/salary-structures', form.value)
  if (error) toast.error(error)
  else {
    toast.success('Salary structure saved successfully!')
    closeModal()
    loadItems()
  }
  saving.value = false
}

const editItem = (item: any) => {
  form.value = { ...item }
  showEditModal.value = true
}

const viewItem = (item: any) => {
  // Implement view details modal
  alert(`Viewing salary structure for Employee #${item.employee_id}`)
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString()
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    employee_id: '',
    basic_salary: 0,
    house_allowance: 0,
    transport_allowance: 0,
    medical_allowance: 0,
    communication_allowance: 0,
    meal_allowance: 0,
    other_allowances: 0,
    nssf_rate: 6.00,
    nhif_amount: 0,
    payment_frequency: 'monthly',
    payment_method: 'bank_transfer',
    effective_from: new Date().toISOString().split('T')[0],
    notes: ''
  }
}

onMounted(() => {
  loadItems()
  loadEmployees()
})
</script>
