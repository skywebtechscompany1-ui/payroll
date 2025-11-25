<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Leave Configuration</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure leave types and policies</p>
      </div>
      <button v-if="auth.hasPermission('leave_config:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Add Leave Type
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:calendar-days" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No leave configurations found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Leave Type</th>
                <th>Annual Days</th>
                <th>Carry Forward</th>
                <th>Requires Approval</th>
                <th>Paid</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.leave_type_name }}</td>
                <td>{{ item.annual_days }} days</td>
                <td>
                  <span v-if="item.can_carry_forward" class="badge badge-success">
                    Yes ({{ item.max_carry_forward_days }} days)
                  </span>
                  <span v-else class="badge badge-error">No</span>
                </td>
                <td>
                  <span :class="item.requires_approval ? 'badge badge-warning' : 'badge badge-info'">
                    {{ item.requires_approval ? 'Yes' : 'No' }}
                  </span>
                </td>
                <td>
                  <span :class="item.is_paid ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_paid ? 'Paid' : 'Unpaid' }}
                  </span>
                </td>
                <td>
                  <span :class="item.is_active ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="flex space-x-2">
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
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit' : 'Add' }} Leave Configuration
          </h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Leave Type *</label>
                <select v-model="form.leave_type" required class="form-select" :disabled="showEditModal">
                  <option :value="1">Sick Leave</option>
                  <option :value="2">Casual Leave</option>
                  <option :value="3">Annual Leave</option>
                  <option :value="4">Maternity Leave</option>
                  <option :value="5">Paternity Leave</option>
                  <option :value="6">Unpaid Leave</option>
                </select>
              </div>
              <div>
                <label class="form-label">Leave Type Name *</label>
                <input v-model="form.leave_type_name" type="text" required class="form-input" />
              </div>
              <div>
                <label class="form-label">Annual Days *</label>
                <input v-model.number="form.annual_days" type="number" required min="0" class="form-input" />
              </div>
              <div>
                <label class="form-label">Max Consecutive Days</label>
                <input v-model.number="form.max_consecutive_days" type="number" min="1" class="form-input" />
              </div>
              <div>
                <label class="form-label">Min Days Notice</label>
                <input v-model.number="form.min_days_notice" type="number" min="0" class="form-input" />
              </div>
              <div>
                <label class="form-label">Max Carry Forward Days</label>
                <input v-model.number="form.max_carry_forward_days" type="number" min="0" class="form-input" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <label class="flex items-center">
                <input v-model="form.can_carry_forward" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Can Carry Forward</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.is_accrued" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Is Accrued</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.requires_approval" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Requires Approval</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.is_paid" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Is Paid</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.requires_documentation" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Requires Documentation</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
                <span class="ml-2">Active</span>
              </label>
            </div>
            <div>
              <label class="form-label">Description</label>
              <textarea v-model="form.description" class="form-input" rows="3"></textarea>
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
useHead({ title: 'Leave Configuration' })

const auth = useAuth()
const api = useApi()
const toast = useToast()

const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({
  leave_type: 1,
  leave_type_name: '',
  annual_days: 0,
  max_consecutive_days: null,
  min_days_notice: 0,
  can_carry_forward: false,
  max_carry_forward_days: 0,
  is_accrued: false,
  accrual_rate: 0,
  requires_approval: true,
  is_paid: true,
  requires_documentation: false,
  documentation_after_days: 0,
  is_active: true,
  description: ''
})

const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/leave-configs')
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value
    ? await api.put(`/leave-configs/${form.value.id}`, form.value)
    : await api.post('/leave-configs', form.value)
  if (error) toast.error(error)
  else {
    toast.success('Leave configuration saved successfully!')
    closeModal()
    loadItems()
  }
  saving.value = false
}

const editItem = (item: any) => {
  form.value = { ...item }
  showEditModal.value = true
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  form.value = {
    leave_type: 1,
    leave_type_name: '',
    annual_days: 0,
    max_consecutive_days: null,
    min_days_notice: 0,
    can_carry_forward: false,
    max_carry_forward_days: 0,
    is_accrued: false,
    accrual_rate: 0,
    requires_approval: true,
    is_paid: true,
    requires_documentation: false,
    documentation_after_days: 0,
    is_active: true,
    description: ''
  }
}

onMounted(() => loadItems())
</script>
