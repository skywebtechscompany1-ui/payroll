<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Leave Management</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage leave requests and approvals</p>
      </div>
      <button v-if="auth.hasPermission('leave:create')" @click="showAddModal = true" class="btn btn-primary">
        <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />Request Leave
      </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Pending</p><p class="text-2xl font-bold text-warning-600">{{ stats.pending }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Approved</p><p class="text-2xl font-bold text-success-600">{{ stats.approved }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Rejected</p><p class="text-2xl font-bold text-error-600">{{ stats.rejected }}</p></div></div>
      <div class="card"><div class="card-body"><p class="text-sm text-gray-600 dark:text-gray-400">Total Days</p><p class="text-2xl font-bold text-primary-600">{{ stats.totalDays }}</p></div></div>
    </div>
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8"><div class="spinner w-8 h-8 mx-auto"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-8"><Icon name="heroicons:calendar" class="w-16 h-16 mx-auto text-gray-400" /><p class="mt-2 text-gray-600 dark:text-gray-400">No leave requests</p></div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead><tr><th>Employee</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>Employee #{{ item.employee_id }}</td>
                <td>{{ getLeaveType(item.leave_type) }}</td>
                <td>{{ formatDate(item.start_date) }}</td>
                <td>{{ formatDate(item.end_date) }}</td>
                <td>{{ item.days }}</td>
                <td><span :class="getStatusBadge(item.status)">{{ getStatusName(item.status) }}</span></td>
                <td>
                  <div class="flex space-x-2">
                    <button v-if="item.status === 1 && auth.hasPermission('leave:approve')" @click="approveLeave(item, true)" class="btn btn-sm btn-success" title="Approve"><Icon name="heroicons:check" class="w-4 h-4" /></button>
                    <button v-if="item.status === 1 && auth.hasPermission('leave:approve')" @click="approveLeave(item, false)" class="btn btn-sm btn-error" title="Reject"><Icon name="heroicons:x-mark" class="w-4 h-4" /></button>
                    <button v-if="item.status === 1" @click="editItem(item)" class="btn btn-sm btn-ghost"><Icon name="heroicons:pencil" class="w-4 h-4" /></button>
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
          <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">{{ showEditModal ? 'Edit' : 'Request' }} Leave</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <div><label class="form-label">Employee ID *</label><input v-model="form.employee_id" type="number" required class="form-input" /></div>
            <div><label class="form-label">Leave Type *</label>
              <select v-model="form.leave_type" required class="form-select">
                <option :value="1">Sick Leave</option>
                <option :value="2">Casual Leave</option>
                <option :value="3">Annual Leave</option>
                <option :value="4">Maternity Leave</option>
                <option :value="5">Paternity Leave</option>
                <option :value="6">Unpaid Leave</option>
              </select>
            </div>
            <div><label class="form-label">Start Date *</label><input v-model="form.start_date" type="date" required class="form-input" /></div>
            <div><label class="form-label">End Date *</label><input v-model="form.end_date" type="date" required class="form-input" /></div>
            <div><label class="form-label">Days *</label><input v-model="form.days" type="number" required class="form-input" min="1" /></div>
            <div><label class="form-label">Reason *</label><textarea v-model="form.reason" required class="form-textarea" rows="3" minlength="10"></textarea></div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">{{ saving ? 'Saving...' : 'Submit' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Leave Management' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const stats = ref({ pending: 0, approved: 0, rejected: 0, totalDays: 0 })
const form = ref({ employee_id: null, leave_type: 1, start_date: '', end_date: '', days: 1, reason: '' })
const loadLeave = async () => {
  loading.value = true
  const { data, error } = await api.get('/leave')
  if (error) toast.error(error)
  else {
    items.value = data.items
    stats.value = {
      pending: items.value.filter((i: any) => i.status === 1).length,
      approved: items.value.filter((i: any) => i.status === 2).length,
      rejected: items.value.filter((i: any) => i.status === 3).length,
      totalDays: items.value.reduce((sum: number, i: any) => sum + (i.days || 0), 0)
    }
  }
  loading.value = false
}
const saveItem = async () => {
  saving.value = true
  const { error } = showEditModal.value ? await api.put(`/leave/${form.value.id}`, form.value) : await api.post('/leave', form.value)
  if (error) toast.error(error)
  else { toast.success('Submitted!'); closeModal(); loadLeave() }
  saving.value = false
}
const approveLeave = async (item: any, approved: boolean) => {
  const reason = approved ? null : prompt('Rejection reason:')
  if (!approved && !reason) return
  const { error } = await api.post(`/leave/${item.id}/approve`, { approved, rejection_reason: reason })
  if (error) toast.error(error)
  else { toast.success(approved ? 'Approved!' : 'Rejected!'); loadLeave() }
}
const editItem = (item: any) => { form.value = { ...item }; showEditModal.value = true }
const closeModal = () => { showAddModal.value = false; showEditModal.value = false; form.value = { employee_id: null, leave_type: 1, start_date: '', end_date: '', days: 1, reason: '' } }
const formatDate = (date: string) => new Date(date).toLocaleDateString()
const getLeaveType = (type: number) => ['', 'Sick', 'Casual', 'Annual', 'Maternity', 'Paternity', 'Unpaid'][type]
const getStatusName = (status: number) => ['', 'Pending', 'Approved', 'Rejected', 'Cancelled'][status]
const getStatusBadge = (status: number) => ['', 'badge badge-warning', 'badge badge-success', 'badge badge-error', 'badge badge-secondary'][status]
onMounted(() => loadLeave())
</script>
