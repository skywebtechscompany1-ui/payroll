<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Roles & Permissions</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create roles and assign permissions & module access</p>
      </div>
      <button @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Create Role
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:shield-check" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No roles found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Role Name</th>
                <th>Display Name</th>
                <th>Permissions</th>
                <th>Modules</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.name }}</td>
                <td>{{ item.display_name }}</td>
                <td>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="perm in getPermissionsList(item.permissions)" :key="perm" class="badge badge-sm badge-primary">
                      {{ perm }}
                    </span>
                  </div>
                </td>
                <td>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="mod in getModulesList(item.modules)" :key="mod" class="badge badge-sm badge-info">
                      {{ mod }}
                    </span>
                  </div>
                </td>
                <td>
                  <span :class="item.is_active ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                  <span v-if="item.is_system" class="badge badge-warning ml-2">System</span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editItem(item)" class="btn btn-sm btn-primary" :disabled="item.is_system">
                      <Icon name="heroicons:pencil" class="w-4 h-4 mr-1" />Edit
                    </button>
                    <button @click="deleteItem(item)" class="btn btn-sm btn-error" :disabled="item.is_system">
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

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit' : 'Create' }} Role
          </h2>
          <form @submit.prevent="saveItem" class="space-y-6">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="form-label">Role Name (slug) *</label>
                <input v-model="form.name" type="text" required class="form-input" placeholder="e.g., hr_manager" :disabled="showEditModal" />
                <p class="text-xs text-gray-500 mt-1">Lowercase, no spaces (use underscores)</p>
              </div>
              <div>
                <label class="form-label">Display Name *</label>
                <input v-model="form.display_name" type="text" required class="form-input" placeholder="e.g., HR Manager" />
              </div>
            </div>
            <div>
              <label class="form-label">Description</label>
              <textarea v-model="form.description" class="form-input" rows="2" placeholder="Describe this role's responsibilities"></textarea>
            </div>
            <div>
              <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
                <span class="ml-2 font-medium">Active Role</span>
              </label>
            </div>

            <!-- Permissions Section -->
            <div class="border-t pt-4">
              <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Assign Permissions</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="category in permissionCategories" :key="category.name" class="border rounded-lg p-4">
                  <h4 class="font-semibold mb-2 text-gray-900 dark:text-white">{{ category.name }}</h4>
                  <div class="space-y-2">
                    <label v-for="perm in category.permissions" :key="perm.key" class="flex items-center">
                      <input v-model="form.permissions[perm.key]" type="checkbox" class="form-checkbox" />
                      <span class="ml-2 text-sm">{{ perm.label }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modules Section -->
            <div class="border-t pt-4">
              <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Assign Module Access</h3>
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <label v-for="module in availableModules" :key="module.key" class="flex items-center p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                  <input v-model="form.modules[module.key]" type="checkbox" class="form-checkbox" />
                  <div class="ml-3">
                    <Icon :name="module.icon" class="w-5 h-5 text-primary-600" />
                    <span class="ml-2 text-sm font-medium">{{ module.label }}</span>
                  </div>
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">
                <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                {{ saving ? 'Saving...' : (showEditModal ? 'Update Role' : 'Create Role') }}
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
useHead({ title: 'Roles' })

const auth = useAuth()
const api = useApi()
const toast = useToast()

const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({
  name: '',
  display_name: '',
  description: '',
  is_active: true,
  permissions: {},
  modules: {}
})

// Permission Categories
const permissionCategories = [
  {
    name: 'Users',
    permissions: [
      { key: 'users:read', label: 'View Users' },
      { key: 'users:create', label: 'Create Users' },
      { key: 'users:update', label: 'Edit Users' },
      { key: 'users:delete', label: 'Delete Users' },
      { key: 'users:reset_password', label: 'Reset Password' }
    ]
  },
  {
    name: 'Employees',
    permissions: [
      { key: 'employees:read', label: 'View Employees' },
      { key: 'employees:create', label: 'Create Employees' },
      { key: 'employees:update', label: 'Edit Employees' },
      { key: 'employees:delete', label: 'Delete Employees' }
    ]
  },
  {
    name: 'Payroll',
    permissions: [
      { key: 'payroll:read', label: 'View Payroll' },
      { key: 'payroll:create', label: 'Create Payroll' },
      { key: 'payroll:update', label: 'Edit Payroll' },
      { key: 'payroll:delete', label: 'Delete Payroll' },
      { key: 'payroll:process', label: 'Process Payroll' }
    ]
  },
  {
    name: 'Salary',
    permissions: [
      { key: 'salary:read', label: 'View Salary' },
      { key: 'salary:update', label: 'Edit Salary' }
    ]
  },
  {
    name: 'Payments',
    permissions: [
      { key: 'payments:read', label: 'View Payments' },
      { key: 'payments:create', label: 'Make Payments' },
      { key: 'payments:approve', label: 'Approve Payments' }
    ]
  },
  {
    name: 'Reports',
    permissions: [
      { key: 'reports:read', label: 'View Reports' },
      { key: 'reports:export', label: 'Export Reports' }
    ]
  },
  {
    name: 'Attendance',
    permissions: [
      { key: 'attendance:read', label: 'View Attendance' },
      { key: 'attendance:create', label: 'Mark Attendance' },
      { key: 'attendance:update', label: 'Edit Attendance' }
    ]
  },
  {
    name: 'Leave',
    permissions: [
      { key: 'leave:read', label: 'View Leave' },
      { key: 'leave:create', label: 'Apply Leave' },
      { key: 'leave:approve', label: 'Approve Leave' },
      { key: 'leave:reject', label: 'Reject Leave' }
    ]
  },
  {
    name: 'Roles',
    permissions: [
      { key: 'roles:read', label: 'View Roles' },
      { key: 'roles:create', label: 'Create Roles' },
      { key: 'roles:update', label: 'Edit Roles' },
      { key: 'roles:delete', label: 'Delete Roles' }
    ]
  },
  {
    name: 'Settings',
    permissions: [
      { key: 'settings:read', label: 'View Settings' },
      { key: 'settings:update', label: 'Update Settings' }
    ]
  }
]

// Available Modules
const availableModules = [
  { key: 'dashboard', label: 'Dashboard', icon: 'heroicons:home' },
  { key: 'users', label: 'Users', icon: 'heroicons:users' },
  { key: 'employees', label: 'Employees', icon: 'heroicons:briefcase' },
  { key: 'departments', label: 'Departments', icon: 'heroicons:building-office' },
  { key: 'designations', label: 'Designations', icon: 'heroicons:academic-cap' },
  { key: 'attendance', label: 'Attendance', icon: 'heroicons:clock' },
  { key: 'leave', label: 'Leave', icon: 'heroicons:calendar-days' },
  { key: 'payroll', label: 'Payroll', icon: 'heroicons:banknotes' },
  { key: 'salary', label: 'Manage Salary', icon: 'heroicons:currency-dollar' },
  { key: 'payments', label: 'Payments', icon: 'heroicons:credit-card' },
  { key: 'payslips', label: 'Payslips', icon: 'heroicons:document-text' },
  { key: 'reports', label: 'Reports', icon: 'heroicons:chart-bar' },
  { key: 'roles', label: 'Roles', icon: 'heroicons:shield-check' },
  { key: 'activity_logs', label: 'Activity Logs', icon: 'heroicons:clipboard-document-list' },
  { key: 'leave_config', label: 'Leave Config', icon: 'heroicons:adjustments-horizontal' }
]

const getPermissionsList = (permissions: any) => {
  if (!permissions) return []
  return Object.keys(permissions).filter(key => permissions[key])
}

const getModulesList = (modules: any) => {
  if (!modules) return []
  return Object.keys(modules).filter(key => modules[key])
}

const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/roles')
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value
    ? await api.put(`/roles/${form.value.id}`, form.value)
    : await api.post('/roles', form.value)
  if (error) toast.error(error)
  else {
    toast.success('Role saved successfully!')
    closeModal()
    loadItems()
  }
  saving.value = false
}

const editItem = (item: any) => {
  form.value = { ...item }
  showEditModal.value = true
}

const deleteItem = async (item: any) => {
  if (!confirm('Are you sure you want to delete this role?')) return
  const { error } = await api.delete(`/roles/${item.id}`)
  if (error) toast.error(error)
  else {
    toast.success('Role deleted successfully!')
    loadItems()
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    name: '',
    display_name: '',
    description: '',
    is_active: true,
    permissions: {},
    modules: {}
  }
}

onMounted(() => loadItems())
</script>
