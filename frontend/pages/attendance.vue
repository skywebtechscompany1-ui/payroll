<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track employee attendance</p>
      </div>
      <button v-if="auth.hasPermission('attendance:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Mark Attendance
      </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Present Today</p>
              <p class="text-2xl font-bold text-success-600">{{ stats.present }}</p>
            </div>
            <Icon name="heroicons:check-circle" class="w-10 h-10 text-success-600" />
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Absent</p>
              <p class="text-2xl font-bold text-error-600">{{ stats.absent }}</p>
            </div>
            <Icon name="heroicons:x-circle" class="w-10 h-10 text-error-600" />
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Late</p>
              <p class="text-2xl font-bold text-warning-600">{{ stats.late }}</p>
            </div>
            <Icon name="heroicons:clock" class="w-10 h-10 text-warning-600" />
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">On Leave</p>
              <p class="text-2xl font-bold text-primary-600">{{ stats.leave }}</p>
            </div>
            <Icon name="heroicons:calendar" class="w-10 h-10 text-primary-600" />
          </div>
        </div>
      </div>
    </div>
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Start Date</label>
            <input v-model="filters.startDate" type="date" class="form-input" />
          </div>
          <div>
            <label class="form-label">End Date</label>
            <input v-model="filters.endDate" type="date" class="form-input" />
          </div>
          <div class="flex items-end">
            <button @click="loadAttendance" class="btn btn-outline w-full">
              <Icon name="heroicons:magnifying-glass" class="w-4 h-4 mr-2" />Search
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading...</p>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:calendar-days" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No attendance records found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead><tr><th>Employee</th><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">Employee #{{ item.employee_id }}</td>
                <td>{{ formatDate(item.date) }}</td>
                <td>{{ item.clock_in || 'N/A' }}</td>
                <td>{{ item.clock_out || 'N/A' }}</td>
                <td><span :class="getStatusBadge(item.status)">{{ getStatusName(item.status) }}</span></td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editItem(item)" class="btn btn-sm btn-ghost"><Icon name="heroicons:pencil" class="w-4 h-4" /></button>
                    <button @click="deleteItem(item)" class="btn btn-sm btn-ghost text-error-600"><Icon name="heroicons:trash" class="w-4 h-4" /></button>
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
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ showEditModal ? 'Edit' : 'Mark' }} Attendance</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div><label class="form-label">Employee ID *</label><input v-model="form.employee_id" type="number" required class="form-input" /></div>
            <div><label class="form-label">Date *</label><input v-model="form.date" type="date" required class="form-input" /></div>
            <div><label class="form-label">Clock In</label><input v-model="form.clock_in" type="time" class="form-input" /></div>
            <div><label class="form-label">Clock Out</label><input v-model="form.clock_out" type="time" class="form-input" /></div>
            <div><label class="form-label">Status *</label>
              <select v-model="form.status" required class="form-select">
                <option :value="1">Present</option>
                <option :value="2">Absent</option>
                <option :value="3">Late</option>
                <option :value="4">Half-day</option>
                <option :value="5">Leave</option>
              </select>
            </div>
            <div><label class="form-label">Notes</label><textarea v-model="form.notes" class="form-textarea" rows="2"></textarea></div>
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
interface AttendanceItem {
  id?: number
  employee_id: number | null
  date: string
  clock_in: string
  clock_out: string
  status: number
  notes: string
}

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Attendance' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const items = ref<AttendanceItem[]>([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const stats = ref({ present: 0, absent: 0, late: 0, leave: 0 })
const filters = ref({ startDate: new Date().toISOString().split('T')[0], endDate: new Date().toISOString().split('T')[0] })
const form = ref<AttendanceItem>({ employee_id: null, date: new Date().toISOString().split('T')[0], clock_in: '', clock_out: '', status: 1, notes: '' })
const loadAttendance = async () => {
  loading.value = true
  const params = new URLSearchParams()
  if (filters.value.startDate) params.append('start_date', filters.value.startDate)
  if (filters.value.endDate) params.append('end_date', filters.value.endDate)
  const { data, error } = await api.get(`/attendance?${params}`)
  if (error) toast.error(error)
  else if (data?.items) {
    items.value = data.items
    calculateStats()
  }
  loading.value = false
}
const calculateStats = () => {
  stats.value = {
    present: items.value.filter((i) => i.status === 1).length,
    absent: items.value.filter((i) => i.status === 2).length,
    late: items.value.filter((i) => i.status === 3).length,
    leave: items.value.filter((i) => i.status === 5).length
  }
}
const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value ? await api.put(`/attendance/${form.value.id}`, form.value) : await api.post('/attendance', form.value)
  if (error) toast.error(error)
  else { toast.success('Saved!'); closeModal(); loadAttendance() }
  saving.value = false
}
const editItem = (item: AttendanceItem) => { form.value = { ...item }; showEditModal.value = true }
const deleteItem = async (item: AttendanceItem) => {
  if (!confirm('Delete this record?')) return
  const { error } = await api.delete(`/attendance/${item.id}`)
  if (error) toast.error(error)
  else { toast.success('Deleted!'); loadAttendance() }
}
const closeModal = () => { showAddModal.value = false; showEditModal.value = false; form.value = { employee_id: null, date: new Date().toISOString().split('T')[0], clock_in: '', clock_out: '', status: 1, notes: '' } }
const formatDate = (date: string) => new Date(date).toLocaleDateString()
const getStatusName = (status: number) => ['', 'Present', 'Absent', 'Late', 'Half-day', 'Leave'][status]
const getStatusBadge = (status: number) => {
  const badges = ['', 'badge badge-success', 'badge badge-error', 'badge badge-warning', 'badge badge-secondary', 'badge badge-primary']
  return badges[status]
}
onMounted(() => loadAttendance())
</script>
