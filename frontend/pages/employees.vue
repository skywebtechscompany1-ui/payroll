<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
          Employees
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage employee records and information
        </p>
      </div>
      <button 
        v-if="auth.hasPermission('employees:create')"
        @click="showAddModal = true" 
        class="btn btn-primary"
      >
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
        Add Employee
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Search</label>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search employees..." 
              class="form-input"
            />
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filterStatus" class="form-select">
              <option value="">All</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="loadEmployees" class="btn btn-outline w-full">
              <Icon name="heroicons:magnifying-glass" class="w-4 h-4 mr-2" />
              Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Employees Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading employees...</p>
        </div>

        <div v-else-if="employees.length === 0" class="text-center py-8">
          <Icon name="heroicons:users" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No employees found</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in employees" :key="employee.id">
                <td>{{ employee.employee_id || 'N/A' }}</td>
                <td>{{ employee.name }}</td>
                <td>{{ employee.email || 'N/A' }}</td>
                <td>{{ employee.designation_id || 'N/A' }}</td>
                <td>
                  <span :class="[
                    'badge',
                    employee.activation_status === 1 ? 'badge-success' : 'badge-error'
                  ]">
                    {{ employee.activation_status === 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button 
                      @click="viewEmployee(employee)" 
                      class="btn btn-sm btn-ghost"
                      title="View"
                    >
                      <Icon name="heroicons:eye" class="w-4 h-4" />
                    </button>
                    <button 
                      v-if="auth.hasPermission('employees:update')"
                      @click="editEmployee(employee)" 
                      class="btn btn-sm btn-ghost"
                      title="Edit"
                    >
                      <Icon name="heroicons:pencil" class="w-4 h-4" />
                    </button>
                    <button 
                      v-if="auth.hasPermission('employees:delete')"
                      @click="deleteEmployee(employee)" 
                      class="btn btn-sm btn-ghost text-error-600"
                      title="Delete"
                    >
                      <Icon name="heroicons:trash" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="total > limit" class="mt-4 flex justify-between items-center">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ skip + 1 }} to {{ Math.min(skip + limit, total) }} of {{ total }} employees
          </p>
          <div class="flex space-x-2">
            <button 
              @click="previousPage" 
              :disabled="skip === 0"
              class="btn btn-sm btn-outline"
            >
              Previous
            </button>
            <button 
              @click="nextPage" 
              :disabled="skip + limit >= total"
              class="btn btn-sm btn-outline"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit Employee' : 'Add Employee' }}
          </h2>
          
          <form @submit.prevent="saveEmployee" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" type="text" required class="form-input" />
              </div>
              <div>
                <label class="form-label">Employee ID</label>
                <input v-model="form.employee_id" type="text" class="form-input" />
              </div>
              <div>
                <label class="form-label">Email *</label>
                <input v-model="form.email" type="email" required class="form-input" />
              </div>
              <div>
                <label class="form-label">Contact Number *</label>
                <input v-model="form.contact_no_one" type="tel" required class="form-input" />
              </div>
              <div>
                <label class="form-label">Gender *</label>
                <select v-model="form.gender" required class="form-select">
                  <option value="">Select Gender</option>
                  <option value="M">Male</option>
                  <option value="F">Female</option>
                  <option value="O">Other</option>
                </select>
              </div>
              <div>
                <label class="form-label">Date of Birth</label>
                <input v-model="form.date_of_birth" type="date" class="form-input" />
              </div>
              <div class="md:col-span-2">
                <label class="form-label">Present Address *</label>
                <textarea v-model="form.present_address" required class="form-textarea" rows="2"></textarea>
              </div>
              <div v-if="!showEditModal">
                <label class="form-label">Password *</label>
                <input v-model="form.password" type="password" required class="form-input" minlength="6" />
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn btn-outline">
                Cancel
              </button>
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
definePageMeta({
  middleware: 'auth'
})

useHead({
  title: 'Employees'
})

const auth = useAuth()
const api = useApi()
const toast = useToast()

// State
const employees = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const searchQuery = ref('')
const filterStatus = ref('')
const skip = ref(0)
const limit = ref(20)
const total = ref(0)

const form = ref({
  name: '',
  employee_id: '',
  email: '',
  contact_no_one: '',
  gender: '',
  date_of_birth: '',
  present_address: '',
  password: '',
  access_label: 5
})

// Methods
const loadEmployees = async () => {
  loading.value = true
  const params = new URLSearchParams({
    skip: skip.value.toString(),
    limit: limit.value.toString()
  })
  
  if (filterStatus.value) params.append('status', filterStatus.value)
  
  const { data, error } = await api.get(`/employees?${params}`)
  
  if (error) {
    toast.error(error)
  } else {
    employees.value = data.items
    total.value = data.total
  }
  
  loading.value = false
}

const saveEmployee = async () => {
  saving.value = true
  
  const { data, error } = showEditModal.value
    ? await api.put(`/employees/${form.value.id}`, form.value)
    : await api.post('/employees', form.value)
  
  if (error) {
    toast.error(error)
  } else {
    toast.success(`Employee ${showEditModal.value ? 'updated' : 'created'} successfully`)
    closeModal()
    loadEmployees()
  }
  
  saving.value = false
}

const editEmployee = (employee: any) => {
  form.value = { ...employee }
  showEditModal.value = true
}

const viewEmployee = (employee: any) => {
  navigateTo(`/employees/${employee.id}`)
}

const deleteEmployee = async (employee: any) => {
  if (!confirm(`Are you sure you want to delete ${employee.name}?`)) return
  
  const { error } = await api.delete(`/employees/${employee.id}`)
  
  if (error) {
    toast.error(error)
  } else {
    toast.success('Employee deleted successfully')
    loadEmployees()
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    name: '',
    employee_id: '',
    email: '',
    contact_no_one: '',
    gender: '',
    date_of_birth: '',
    present_address: '',
    password: '',
    access_label: 5
  }
}

const nextPage = () => {
  skip.value += limit.value
  loadEmployees()
}

const previousPage = () => {
  skip.value = Math.max(0, skip.value - limit.value)
  loadEmployees()
}

// Load on mount
onMounted(() => {
  loadEmployees()
})
</script>
