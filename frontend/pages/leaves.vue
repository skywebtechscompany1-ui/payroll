<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Leaves</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and manage all leave applications</p>
      </div>
      <button @click="$router.push('/leave-apply')" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Apply Leave
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="form-label">Employee</label>
            <select v-model="filters.employee_id" @change="loadItems" class="form-select">
              <option value="">All Employees</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">Leave Type</label>
            <select v-model="filters.leave_type" @change="loadItems" class="form-select">
              <option value="">All Types</option>
              <option value="1">Annual</option>
              <option value="2">Sick</option>
              <option value="3">Maternity</option>
              <option value="4">Paternity</option>
              <option value="5">Study</option>
              <option value="6">Unpaid</option>
              <option value="7">Mourning</option>
              <option value="8">Compassionate</option>
              <option value="9">Other</option>
            </select>
          </div>
          <div>
            <label class="form-label">Status</label>
            <select v-model="filters.status" @change="loadItems" class="form-select">
              <option value="">All Status</option>
              <option value="1">Pending</option>
              <option value="2">Approved</option>
              <option value="3">Rejected</option>
              <option value="4">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="form-label">Start Date</label>
            <input v-model="filters.start_date" @change="loadItems" type="date" class="form-input" />
          </div>
          <div>
            <label class="form-label">End Date</label>
            <input v-model="filters.end_date" @change="loadItems" type="date" class="form-input" />
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="resetFilters" class="btn btn-outline">
            <Icon name="heroicons:x-mark" class="w-4 h-4 mr-2" />Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Leaves Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:calendar-days" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No leave applications found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Remaining Days</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.employee_name || 'N/A' }}</td>
                <td>
                  <span class="badge badge-info">{{ getLeaveTypeName(item.leave_type) }}</span>
                </td>
                <td>{{ formatDate(item.start_date) }}</td>
                <td>{{ formatDate(item.end_date) }}</td>
                <td>{{ item.days }}</td>
                <td>
                  <span class="badge badge-primary">{{ item.remaining_days || 'N/A' }}</span>
                </td>
                <td>
                  <span :class="getStatusClass(item.status)">
                    {{ getStatusName(item.status) }}
                  </span>
                  <span v-if="isExtended(item)" class="badge badge-warning ml-2">Extended</span>
                  <span v-if="isReturned(item)" class="badge badge-success ml-2">Returned</span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="viewItem(item)" class="btn btn-sm btn-ghost" title="View">
                      <Icon name="heroicons:eye" class="w-4 h-4" />
                    </button>
                    <button v-if="item.status == 1" @click="editItem(item)" class="btn btn-sm btn-primary" title="Edit">
                      <Icon name="heroicons:pencil" class="w-4 h-4" />
                    </button>
                    <button v-if="item.status == 1 && (auth.isAdmin || auth.isHR)" @click="approveItem(item)" class="btn btn-sm btn-success" title="Approve">
                      <Icon name="heroicons:check" class="w-4 h-4" />
                    </button>
                    <button v-if="item.status == 1 && (auth.isAdmin || auth.isHR)" @click="rejectItem(item)" class="btn btn-sm btn-error" title="Reject">
                      <Icon name="heroicons:x-mark" class="w-4 h-4" />
                    </button>
                    <button v-if="item.status == 2" @click="extendLeave(item)" class="btn btn-sm btn-warning" title="Extend">
                      <Icon name="heroicons:arrow-right" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeViewModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Leave Details</h2>
          <div v-if="selectedItem" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Employee</label>
                <p class="text-gray-900 dark:text-white">{{ selectedItem.employee_name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Leave Type</label>
                <p class="text-gray-900 dark:text-white">{{ getLeaveTypeName(selectedItem.leave_type) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Start Date</label>
                <p class="text-gray-900 dark:text-white">{{ formatDate(selectedItem.start_date) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">End Date</label>
                <p class="text-gray-900 dark:text-white">{{ formatDate(selectedItem.end_date) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Total Days</label>
                <p class="text-gray-900 dark:text-white">{{ selectedItem.days }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <p><span :class="getStatusClass(selectedItem.status)">{{ getStatusName(selectedItem.status) }}</span></p>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Reason</label>
              <p class="text-gray-900 dark:text-white">{{ selectedItem.reason }}</p>
            </div>
            <div v-if="selectedItem.rejection_reason">
              <label class="text-sm font-medium text-gray-500">Rejection Reason</label>
              <p class="text-red-600">{{ selectedItem.rejection_reason }}</p>
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button @click="closeViewModal" class="btn btn-outline">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeRejectModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Reject Leave</h2>
          <form @submit.prevent="confirmReject" class="space-y-4">
            <div>
              <label class="form-label">Rejection Reason *</label>
              <textarea v-model="rejectReason" required class="form-input" rows="4" placeholder="Provide reason for rejection..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeRejectModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-error">
                {{ saving ? 'Rejecting...' : 'Reject Leave' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Extend Modal -->
    <div v-if="showExtendModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeExtendModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Extend Leave</h2>
          <form @submit.prevent="confirmExtend" class="space-y-4">
            <div>
              <label class="form-label">New End Date *</label>
              <input v-model="extendDate" type="date" required class="form-input" />
            </div>
            <div>
              <label class="form-label">Reason for Extension</label>
              <textarea v-model="extendReason" class="form-input" rows="3" placeholder="Optional..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeExtendModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-warning">
                {{ saving ? 'Extending...' : 'Extend Leave' }}
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
useHead({ title: 'Leaves' })

const auth = useAuth()
const api = useApi()
const toast = useToast()
const router = useRouter()

const items = ref([])
const employees = ref([])
const loading = ref(false)
const saving = ref(false)
const showViewModal = ref(false)
const showRejectModal = ref(false)
const showExtendModal = ref(false)
const selectedItem = ref(null)
const rejectReason = ref('')
const extendDate = ref('')
const extendReason = ref('')

const filters = ref({
  employee_id: '',
  leave_type: '',
  status: '',
  start_date: '',
  end_date: ''
})

const leaveTypes = {
  '1': 'Annual',
  '2': 'Sick',
  '3': 'Maternity',
  '4': 'Paternity',
  '5': 'Study',
  '6': 'Unpaid',
  '7': 'Mourning',
  '8': 'Compassionate',
  '9': 'Other'
}

const getLeaveTypeName = (type: number) => {
  return leaveTypes[type.toString()] || 'Unknown'
}

const getStatusName = (status: number) => {
  const statuses = {
    1: 'Pending',
    2: 'Approved',
    3: 'Rejected',
    4: 'Cancelled'
  }
  return statuses[status] || 'Unknown'
}

const getStatusClass = (status: number) => {
  const classes = {
    1: 'badge badge-warning',
    2: 'badge badge-success',
    3: 'badge badge-error',
    4: 'badge badge-ghost'
  }
  return classes[status] || 'badge'
}

const formatDate = (date: string) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const isExtended = (item: any) => {
  // Check if leave has been extended (you can add a field in backend)
  return item.is_extended || false
}

const isReturned = (item: any) => {
  // Check if employee has returned (compare current date with end date)
  if (item.status !== 2) return false
  const today = new Date()
  const endDate = new Date(item.end_date)
  return today > endDate
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (!error) employees.value = data.items || []
}

const loadItems = async () => {
  loading.value = true
  const params = new URLSearchParams()
  Object.keys(filters.value).forEach(key => {
    if (filters.value[key]) params.append(key, filters.value[key])
  })
  
  const { data, error } = await api.get(`/leave?${params.toString()}`)
  if (error) toast.error(error)
  else items.value = data.items || []
  loading.value = false
}

const resetFilters = () => {
  filters.value = {
    employee_id: '',
    leave_type: '',
    status: '',
    start_date: '',
    end_date: ''
  }
  loadItems()
}

const viewItem = (item: any) => {
  selectedItem.value = item
  showViewModal.value = true
}

const editItem = (item: any) => {
  router.push(`/leave-apply?id=${item.id}`)
}

const approveItem = async (item: any) => {
  if (!confirm('Are you sure you want to approve this leave?')) return
  
  saving.value = true
  const { error } = await api.put(`/leave/${item.id}`, { status: 2 })
  if (error) toast.error(error)
  else {
    toast.success('Leave approved successfully!')
    loadItems()
  }
  saving.value = false
}

const rejectItem = (item: any) => {
  selectedItem.value = item
  rejectReason.value = ''
  showRejectModal.value = true
}

const confirmReject = async () => {
  saving.value = true
  const { error } = await api.put(`/leave/${selectedItem.value.id}`, {
    status: 3,
    rejection_reason: rejectReason.value
  })
  if (error) toast.error(error)
  else {
    toast.success('Leave rejected')
    closeRejectModal()
    loadItems()
  }
  saving.value = false
}

const extendLeave = (item: any) => {
  selectedItem.value = item
  extendDate.value = item.end_date
  extendReason.value = ''
  showExtendModal.value = true
}

const confirmExtend = async () => {
  saving.value = true
  const { error } = await api.put(`/leave/${selectedItem.value.id}/extend`, {
    new_end_date: extendDate.value,
    reason: extendReason.value
  })
  if (error) toast.error(error)
  else {
    toast.success('Leave extended successfully!')
    closeExtendModal()
    loadItems()
  }
  saving.value = false
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedItem.value = null
}

const closeRejectModal = () => {
  showRejectModal.value = false
  selectedItem.value = null
  rejectReason.value = ''
}

const closeExtendModal = () => {
  showExtendModal.value = false
  selectedItem.value = null
  extendDate.value = ''
  extendReason.value = ''
}

onMounted(() => {
  loadEmployees()
  loadItems()
})
</script>
