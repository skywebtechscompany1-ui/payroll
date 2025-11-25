<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Designations</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage job designations</p>
      </div>
      <button v-if="auth.hasPermission('employees:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Add Designation
      </button>
    </div>
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading...</p>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:briefcase" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No designations found</p>
          <button @click="showAddModal = true" class="btn btn-primary mt-4">Create First Designation</button>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Designation</th>
                <th>Department</th>
                <th>Description</th>
                <th>Created Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.designation }}</td>
                <td>{{ getDepartmentName(item.department_id) }}</td>
                <td>{{ item.designation_description || 'N/A' }}</td>
                <td>{{ formatDate(item.created_at) }}</td>
                <td>
                  <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" :checked="item.publication_status === 1" @change="toggleStatus(item)" class="sr-only peer" />
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    <span class="ms-3 text-sm font-medium" :class="item.publication_status === 1 ? 'text-green-600' : 'text-gray-500'">{{ item.publication_status === 1 ? 'Active' : 'Inactive' }}</span>
                  </label>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editItem(item)" class="btn btn-sm btn-ghost" title="Edit"><Icon name="heroicons:pencil" class="w-4 h-4" /></button>
                    <button @click="deleteItem(item)" class="btn btn-sm btn-ghost text-error-600" title="Delete"><Icon name="heroicons:trash" class="w-4 h-4" /></button>
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
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ showEditModal ? 'Edit' : 'Add' }} Designation</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div>
              <label class="form-label">Designation Name *</label>
              <input v-model="form.designation" type="text" required class="form-input" placeholder="e.g., Senior Developer" />
            </div>
            <div>
              <label class="form-label">Department *</label>
              <select v-model="form.department_id" required class="form-select">
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.department }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Description</label>
              <textarea v-model="form.designation_description" class="form-input" rows="3" placeholder="Brief description of the role"></textarea>
            </div>
            <div>
              <label class="inline-flex items-center cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
                <span class="ml-2 font-medium">Active</span>
              </label>
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
useHead({ title: 'Designations' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const items = ref([])
const departments = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({ designation: '', department_id: null, designation_description: '', is_active: true })
const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/designations')
  if (error) toast.error(error)
  else items.value = data.items || []
  loading.value = false
}

const loadDepartments = async () => {
  const { data, error } = await api.get('/departments')
  if (!error) departments.value = data.items || []
}

const getDepartmentName = (deptId: number) => {
  const dept = departments.value.find((d: any) => d.id === deptId)
  return dept?.department || 'N/A'
}

const formatDate = (date: string) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const toggleStatus = async (item: any) => {
  const newStatus = item.publication_status === 1 ? 0 : 1
  const { error } = await api.put(`/designations/${item.id}`, {
    ...item,
    publication_status: newStatus
  })
  if (error) {
    toast.error('Failed to update status')
  } else {
    item.publication_status = newStatus
    toast.success(`Designation ${newStatus === 1 ? 'activated' : 'deactivated'}`)
  }
}
const saveItem = async () => {
  saving.value = true
  const payload = {
    ...form.value,
    publication_status: form.value.is_active ? 1 : 0
  }
  const { error } = showEditModal.value ? await api.put(`/designations/${form.value.id}`, payload) : await api.post('/designations', payload)
  if (error) toast.error(error)
  else { toast.success('Designation saved successfully!'); closeModal(); loadItems() }
  saving.value = false
}
const editItem = (item: any) => { 
  form.value = { 
    ...item, 
    is_active: item.publication_status === 1 
  }
  showEditModal.value = true 
}
const deleteItem = async (item: any) => {
  if (!confirm(`Delete ${item.designation}?`)) return
  const { error } = await api.delete(`/designations/${item.id}`)
  if (error) toast.error(error)
  else { toast.success('Deleted!'); loadItems() }
}
const closeModal = () => { 
  showAddModal.value = false
  showEditModal.value = false
  form.value = { designation: '', department_id: null, designation_description: '', is_active: true } 
}

onMounted(() => {
  loadItems()
  loadDepartments()
})
</script>
