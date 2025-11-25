<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create users, assign roles, and manage passwords</p>
      </div>
      <button @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Create User
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="form-label">Search</label>
            <input v-model="filters.search" @input="filterItems" type="text" placeholder="Search by name or email..." class="form-input" />
          </div>
          <div>
            <label class="form-label">Role</label>
            <select v-model="filters.role" @change="filterItems" class="form-select">
              <option value="">All Roles</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.status" @change="filterItems" class="form-select">
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="resetFilters" class="btn btn-outline w-full">
              <Icon name="heroicons:x-mark" class="w-4 h-4 mr-2" />Clear Filters
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="filteredItems.length === 0" class="text-center py-8">
          <Icon name="heroicons:users" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No users found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Permissions</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredItems" :key="item.id">
                <td>
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium text-sm mr-3">
                      {{ getInitials(item.name) }}
                    </div>
                    <div>
                      <div class="font-medium">{{ item.name }}</div>
                      <div class="text-xs text-gray-500">{{ item.employee_id || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ item.email }}</td>
                <td>
                  <span class="badge badge-primary">{{ getRoleName(item.role_id) }}</span>
                </td>
                <td>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="perm in getUserPermissions(item.role_id).slice(0, 3)" :key="perm" class="badge badge-sm badge-info">
                      {{ perm }}
                    </span>
                    <span v-if="getUserPermissions(item.role_id).length > 3" class="badge badge-sm badge-ghost">
                      +{{ getUserPermissions(item.role_id).length - 3 }}
                    </span>
                  </div>
                </td>
                <td>
                  <span :class="item.is_active ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editItem(item)" class="btn btn-sm btn-primary">
                      <Icon name="heroicons:pencil" class="w-4 h-4 mr-1" />Edit
                    </button>
                    <button @click="showPasswordModal(item)" class="btn btn-sm btn-warning">
                      <Icon name="heroicons:key" class="w-4 h-4 mr-1" />Password
                    </button>
                    <button @click="toggleStatus(item)" :class="item.is_active ? 'btn btn-sm btn-error' : 'btn btn-sm btn-success'">
                      <Icon :name="item.is_active ? 'heroicons:lock-closed' : 'heroicons:lock-open'" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit' : 'Create' }} User
          </h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" type="text" required class="form-input" placeholder="John Doe" />
              </div>
              <div>
                <label class="form-label">Email *</label>
                <input v-model="form.email" type="email" required class="form-input" placeholder="john@example.com" />
              </div>
            </div>

            <div v-if="!showEditModal" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="form-label">Password *</label>
                <input v-model="form.password" type="password" required class="form-input" minlength="6" placeholder="Min 6 characters" />
              </div>
              <div>
                <label class="form-label">Confirm Password *</label>
                <input v-model="form.confirm_password" type="password" required class="form-input" minlength="6" />
              </div>
            </div>

            <div>
              <label class="form-label">Assign Role *</label>
              <select v-model="form.role_id" required class="form-select" @change="onRoleChange">
                <option value="">Select a role...</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.display_name }} - {{ role.description }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">User will inherit all permissions and module access from this role</p>
            </div>

            <!-- Show assigned permissions preview -->
            <div v-if="form.role_id" class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
              <h4 class="font-semibold mb-2 text-sm text-gray-900 dark:text-white">Permissions from selected role:</h4>
              <div class="flex flex-wrap gap-1">
                <span v-for="perm in getUserPermissions(form.role_id)" :key="perm" class="badge badge-sm badge-primary">
                  {{ perm }}
                </span>
              </div>
              <h4 class="font-semibold mb-2 mt-3 text-sm text-gray-900 dark:text-white">Module Access:</h4>
              <div class="flex flex-wrap gap-1">
                <span v-for="mod in getUserModules(form.role_id)" :key="mod" class="badge badge-sm badge-info">
                  {{ mod }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="form-label">Employee ID</label>
                <input v-model="form.employee_id" type="text" class="form-input" placeholder="Optional" />
              </div>
              <div>
                <label class="flex items-center mt-7">
                  <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
                  <span class="ml-2 font-medium">Active User</span>
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">
                <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                {{ saving ? 'Saving...' : (showEditModal ? 'Update User' : 'Create User') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div v-if="showChangePasswordModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closePasswordModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            Change Password for {{ selectedUser?.name }}
          </h2>
          <form @submit.prevent="changePassword" class="space-y-4">
            <div>
              <label class="form-label">New Password *</label>
              <input v-model="passwordForm.new_password" type="password" required class="form-input" minlength="6" placeholder="Min 6 characters" />
            </div>
            <div>
              <label class="form-label">Confirm New Password *</label>
              <input v-model="passwordForm.confirm_password" type="password" required class="form-input" minlength="6" />
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
              <p class="text-sm text-yellow-800 dark:text-yellow-200">
                <Icon name="heroicons:exclamation-triangle" class="w-4 h-4 inline mr-1" />
                This will reset the user's password. They will need to use the new password to login.
              </p>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closePasswordModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-warning">
                <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                {{ saving ? 'Resetting...' : 'Reset Password' }}
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
useHead({ title: 'User Management' })

const auth = useAuth()
const api = useApi()
const toast = useToast()

const items = ref([])
const roles = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const showChangePasswordModal = ref(false)
const selectedUser = ref(null)

const filters = ref({
  search: '',
  role: '',
  status: ''
})

const form = ref({
  name: '',
  email: '',
  password: '',
  confirm_password: '',
  role_id: '',
  employee_id: '',
  is_active: true
})

const passwordForm = ref({
  new_password: '',
  confirm_password: ''
})

const filteredItems = computed(() => {
  let filtered = items.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(item =>
      item.name?.toLowerCase().includes(search) ||
      item.email?.toLowerCase().includes(search)
    )
  }

  if (filters.value.role) {
    filtered = filtered.filter(item => item.role_id == filters.value.role)
  }

  if (filters.value.status !== '') {
    filtered = filtered.filter(item => item.is_active == filters.value.status)
  }

  return filtered
})

const getInitials = (name: string) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getRoleName = (roleId: number) => {
  const role = roles.value.find(r => r.id === roleId)
  return role?.display_name || 'Employee'
}

const getUserPermissions = (roleId: number) => {
  const role = roles.value.find(r => r.id === roleId)
  if (!role || !role.permissions) return []
  return Object.keys(role.permissions).filter(key => role.permissions[key])
}

const getUserModules = (roleId: number) => {
  const role = roles.value.find(r => r.id === roleId)
  if (!role || !role.modules) return []
  return Object.keys(role.modules).filter(key => role.modules[key])
}

const onRoleChange = () => {
  // Automatically show permissions preview when role is selected
}

const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/users')
  if (error) toast.error(error)
  else items.value = data.items || []
  loading.value = false
}

const loadRoles = async () => {
  const { data, error } = await api.get('/roles')
  if (error) toast.error(error)
  else roles.value = data.items || []
}

const filterItems = () => {
  // Computed property handles filtering
}

const resetFilters = () => {
  filters.value = {
    search: '',
    role: '',
    status: ''
  }
}

const saveItem = async () => {
  if (!showEditModal.value && form.value.password !== form.value.confirm_password) {
    toast.error('Passwords do not match!')
    return
  }

  saving.value = true
  const payload = { ...form.value }
  delete payload.confirm_password

  const { error } = showEditModal.value
    ? await api.put(`/users/${form.value.id}`, payload)
    : await api.post('/users', payload)

  if (error) toast.error(error)
  else {
    toast.success(`User ${showEditModal.value ? 'updated' : 'created'} successfully!`)
    closeModal()
    loadItems()
  }
  saving.value = false
}

const editItem = (item: any) => {
  form.value = {
    ...item,
    password: '',
    confirm_password: ''
  }
  showEditModal.value = true
}

const showPasswordModal = (item: any) => {
  selectedUser.value = item
  passwordForm.value = {
    new_password: '',
    confirm_password: ''
  }
  showChangePasswordModal.value = true
}

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    toast.error('Passwords do not match!')
    return
  }

  saving.value = true
  const { error } = await api.put(`/users/${selectedUser.value.id}/reset-password`, {
    new_password: passwordForm.value.new_password
  })

  if (error) toast.error(error)
  else {
    toast.success('Password reset successfully!')
    closePasswordModal()
  }
  saving.value = false
}

const toggleStatus = async (item: any) => {
  const action = item.is_active ? 'deactivate' : 'activate'
  if (!confirm(`Are you sure you want to ${action} this user?`)) return

  const { error } = await api.put(`/users/${item.id}`, {
    is_active: !item.is_active
  })

  if (error) toast.error(error)
  else {
    toast.success(`User ${action}d successfully!`)
    loadItems()
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    name: '',
    email: '',
    password: '',
    confirm_password: '',
    role_id: '',
    employee_id: '',
    is_active: true
  }
}

const closePasswordModal = () => {
  showChangePasswordModal.value = false
  selectedUser.value = null
  passwordForm.value = {
    new_password: '',
    confirm_password: ''
  }
}

onMounted(() => {
  loadRoles()
  loadItems()
})
</script>
