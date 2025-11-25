<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div><h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1><p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage system users and roles</p></div>
      <button v-if="auth.hasPermission('users:create')" @click="showAddModal = true" class="btn btn-primary"><Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Add User</button>
    </div>
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8"><div class="spinner w-8 h-8 mx-auto"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-8"><Icon name="heroicons:users" class="w-16 h-16 mx-auto text-gray-400" /><p class="mt-2 text-gray-600 dark:text-gray-400">No users found</p></div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.name }}</td>
                <td>{{ item.email }}</td>
                <td><span class="badge badge-primary">{{ item.role || 'Employee' }}</span></td>
                <td><span :class="item.activation_status === 1 ? 'badge badge-success' : 'badge badge-error'">{{ item.activation_status === 1 ? 'Active' : 'Inactive' }}</span></td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editItem(item)" class="btn btn-sm btn-ghost"><Icon name="heroicons:pencil" class="w-4 h-4" /></button>
                    <button @click="toggleStatus(item)" class="btn btn-sm btn-ghost"><Icon name="heroicons:lock-closed" class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ showEditModal ? 'Edit' : 'Add' }} User</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div><label class="form-label">Name *</label><input v-model="form.name" type="text" required class="form-input" /></div>
            <div><label class="form-label">Email *</label><input v-model="form.email" type="email" required class="form-input" /></div>
            <div v-if="!showEditModal"><label class="form-label">Password *</label><input v-model="form.password" type="password" required class="form-input" minlength="6" /></div>
            <div><label class="form-label">Role *</label>
              <select v-model="form.access_label" required class="form-select">
                <option :value="1">Super Admin</option>
                <option :value="2">Admin</option>
                <option :value="3">HR</option>
                <option :value="4">Manager</option>
                <option :value="5">Employee</option>
              </select>
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
useHead({ title: 'Users' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({ name: '', email: '', password: '', access_label: 5 })
const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/employees')
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}
const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value ? await api.put(`/employees/${form.value.id}`, form.value) : await api.post('/employees', { ...form.value, contact_no_one: '0000000000', gender: 'M', present_address: 'N/A' })
  if (error) toast.error(error)
  else { toast.success('Saved!'); closeModal(); loadItems() }
  saving.value = false
}
const editItem = (item: any) => { form.value = { ...item }; showEditModal.value = true }
const toggleStatus = async (item: any) => {
  const { error } = await api.put(`/employees/${item.id}`, { activation_status: item.activation_status === 1 ? 0 : 1 })
  if (error) toast.error(error)
  else { toast.success('Status updated!'); loadItems() }
}
const closeModal = () => { showAddModal.value = false; showEditModal.value = false; form.value = { name: '', email: '', password: '', access_label: 5 } }
onMounted(() => loadItems())
</script>
