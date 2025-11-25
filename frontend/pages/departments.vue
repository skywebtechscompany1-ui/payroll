<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
          Departments
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage company departments
        </p>
      </div>
      <button 
        v-if="auth.hasPermission('employees:create')"
        @click="showAddModal = true" 
        class="btn btn-primary"
      >
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
        Add Department
      </button>
    </div>

    <!-- Departments Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading departments...</p>
        </div>

        <div v-else-if="departments.length === 0" class="text-center py-8">
          <Icon name="heroicons:building-office" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No departments found</p>
          <button @click="showAddModal = true" class="btn btn-primary mt-4">
            Create First Department
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Department Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="dept in departments" :key="dept.id">
                <td class="font-medium">{{ dept.department }}</td>
                <td>{{ dept.department_description || 'N/A' }}</td>
                <td>
                  <span :class="[
                    'badge',
                    dept.publication_status === 1 ? 'badge-success' : 'badge-error'
                  ]">
                    {{ dept.publication_status === 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ formatDate(dept.created_at) }}</td>
                <td>
                  <div class="flex space-x-2">
                    <button 
                      v-if="auth.hasPermission('employees:update')"
                      @click="editDepartment(dept)" 
                      class="btn btn-sm btn-ghost"
                      title="Edit"
                    >
                      <Icon name="heroicons:pencil" class="w-4 h-4" />
                    </button>
                    <button 
                      v-if="auth.hasPermission('employees:delete')"
                      @click="deleteDepartment(dept)" 
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
            Showing {{ skip + 1 }} to {{ Math.min(skip + limit, total) }} of {{ total }} departments
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
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit Department' : 'Add Department' }}
          </h2>
          
          <form @submit.prevent="saveDepartment" class="space-y-4">
            <div>
              <label class="form-label">Department Name *</label>
              <input v-model="form.department" type="text" required class="form-input" 
                placeholder="e.g., Human Resources" />
            </div>
            <div>
              <label class="form-label">Description</label>
              <textarea v-model="form.department_description" class="form-textarea" rows="3"
                placeholder="Brief description of the department"></textarea>
            </div>
            <div v-if="showEditModal">
              <label class="form-label">Status</label>
              <select v-model="form.publication_status" class="form-select">
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
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
  title: 'Departments'
})

const auth = useAuth()
const api = useApi()
const toast = useToast()

// Types
interface Department {
  id?: number
  department: string
  department_description?: string
  publication_status: number
  created_at?: string
}

// State
const departments = ref<Department[]>([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const skip = ref(0)
const limit = ref(20)
const total = ref(0)

const form = ref({
  department: '',
  department_description: '',
  publication_status: 1
} as Department)

// Methods
const loadDepartments = async () => {
  loading.value = true
  const params = new URLSearchParams({
    skip: skip.value.toString(),
    limit: limit.value.toString()
  })
  
  const { data, error } = await api.get(`/departments?${params}`)
  
  if (error) {
    toast.error(error)
  } else {
    departments.value = data.items
    total.value = data.total
  }
  
  loading.value = false
}

const saveDepartment = async () => {
  saving.value = true
  
  const { data, error } = showEditModal.value
    ? await api.put(`/departments/${form.value.id}`, form.value)
    : await api.post('/departments', form.value)
  
  if (error) {
    toast.error(error)
  } else {
    toast.success(`Department ${showEditModal.value ? 'updated' : 'created'} successfully`)
    closeModal()
    loadDepartments()
  }
  
  saving.value = false
}

const editDepartment = (dept: Department) => {
  form.value = { ...dept }
  showEditModal.value = true
}

const deleteDepartment = async (dept: Department) => {
  if (!confirm(`Are you sure you want to delete ${dept.department}?`)) return
  
  const { error } = await api.delete(`/departments/${dept.id}`)
  
  if (error) {
    toast.error(error)
  } else {
    toast.success('Department deleted successfully')
    loadDepartments()
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    department: '',
    department_description: '',
    publication_status: 1
  }
}

const nextPage = () => {
  skip.value += limit.value
  loadDepartments()
}

const previousPage = () => {
  skip.value = Math.max(0, skip.value - limit.value)
  loadDepartments()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}

// Load on mount
onMounted(() => {
  loadDepartments()
})
</script>
