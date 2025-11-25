<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Leave</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Apply for leave</p>
      </div>
    </div>

    <div class="card max-w-3xl">
      <div class="card-body">
        <form @submit.prevent="submitLeave" class="space-y-6">
          <!-- Employee Selection -->
          <div>
            <label class="form-label">Employee *</label>
            <select v-model="form.employee_id" required class="form-select">
              <option value="">Select Employee</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }}
              </option>
            </select>
          </div>

          <!-- Leave Type -->
          <div>
            <label class="form-label">Leave Type *</label>
            <select v-model="form.leave_type" required class="form-select" @change="onLeaveTypeChange">
              <option value="">Select Leave Type</option>
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

          <!-- Other Leave Type Name (if Other is selected) -->
          <div v-if="form.leave_type === '9'">
            <label class="form-label">Specify Leave Type *</label>
            <input v-model="form.other_leave_name" type="text" required class="form-input" placeholder="Enter leave type name..." />
            <p class="text-xs text-gray-500 mt-1">Please specify the type of leave you're applying for</p>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Start Date *</label>
              <input v-model="form.start_date" type="date" required class="form-input" @change="calculateDays" />
            </div>
            <div>
              <label class="form-label">End Date *</label>
              <input v-model="form.end_date" type="date" required class="form-input" @change="calculateDays" />
            </div>
          </div>

          <!-- Half Day -->
          <div>
            <label class="flex items-center">
              <input v-model="form.is_half_day" type="checkbox" class="form-checkbox" @change="calculateDays" />
              <span class="ml-2 font-medium">Is Half Day</span>
            </label>
          </div>

          <!-- Calculated Days -->
          <div v-if="calculatedDays > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center">
              <Icon name="heroicons:calendar-days" class="w-5 h-5 text-blue-600 mr-2" />
              <span class="font-semibold text-blue-900 dark:text-blue-100">Total Days: {{ calculatedDays }}</span>
            </div>
            <p v-if="remainingDays !== null" class="text-sm text-blue-700 dark:text-blue-300 mt-1">
              Remaining {{ getLeaveTypeName(form.leave_type) }} Days: {{ remainingDays }}
            </p>
          </div>

          <!-- Status (for admin/HR) -->
          <div v-if="auth.isAdmin || auth.isHR">
            <label class="form-label">Status</label>
            <select v-model="form.status" class="form-select">
              <option value="1">Pending</option>
              <option value="2">Approved</option>
              <option value="3">Rejected</option>
            </select>
          </div>

          <!-- Description -->
          <div>
            <label class="form-label">Description *</label>
            <textarea v-model="form.reason" required class="form-input" rows="4" placeholder="Reason for leave..."></textarea>
          </div>

          <!-- Rejection Reason (if rejected) -->
          <div v-if="form.status == 3">
            <label class="form-label">Rejection Reason</label>
            <textarea v-model="form.rejection_reason" class="form-input" rows="3" placeholder="Reason for rejection..."></textarea>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button type="button" @click="resetForm" class="btn btn-outline">Reset</button>
            <button type="submit" :disabled="saving" class="btn btn-primary">
              <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
              {{ saving ? 'Submitting...' : 'Submit Leave Application' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Create Leave' })

const auth = useAuth()
const api = useApi()
const toast = useToast()
const router = useRouter()

const employees = ref([])
const saving = ref(false)
const calculatedDays = ref(0)
const remainingDays = ref(null)
const holidays = ref([])

const form = ref({
  employee_id: '',
  leave_type: '',
  other_leave_name: '',
  start_date: '',
  end_date: '',
  is_half_day: false,
  status: '1',
  reason: '',
  rejection_reason: ''
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

const getLeaveTypeName = (type: string) => {
  return leaveTypes[type] || 'Leave'
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (error) toast.error(error)
  else employees.value = data.items || []
}

const loadHolidays = async () => {
  const { data, error } = await api.get('/leave-config/holidays')
  if (!error && data) {
    holidays.value = data.holidays || []
  }
}

const isHoliday = (dateStr: string) => {
  return holidays.value.some((h: any) => h.date === dateStr)
}

const onLeaveTypeChange = () => {
  if (form.value.employee_id && form.value.leave_type) {
    loadRemainingDays()
  }
}

const loadRemainingDays = async () => {
  if (!form.value.employee_id || !form.value.leave_type) return
  
  const { data, error } = await api.get(`/leave/remaining/${form.value.employee_id}/${form.value.leave_type}`)
  if (!error && data) {
    remainingDays.value = data.remaining_days
  }
}

const calculateDays = () => {
  if (!form.value.start_date || !form.value.end_date) {
    calculatedDays.value = 0
    return
  }

  const start = new Date(form.value.start_date)
  const end = new Date(form.value.end_date)
  
  if (end < start) {
    toast.error('End date must be after start date')
    calculatedDays.value = 0
    return
  }

  // Calculate working days (excluding weekends and holidays)
  let days = 0
  const current = new Date(start)
  
  while (current <= end) {
    const dayOfWeek = current.getDay()
    const dateStr = current.toISOString().split('T')[0]
    
    // Count only weekdays (Monday-Friday) that are not holidays
    if (dayOfWeek !== 0 && dayOfWeek !== 6 && !isHoliday(dateStr)) {
      days++
    }
    current.setDate(current.getDate() + 1)
  }

  if (form.value.is_half_day) {
    days = 0.5
  }

  calculatedDays.value = days
}

const submitLeave = async () => {
  if (calculatedDays.value <= 0) {
    toast.error('Please select valid dates')
    return
  }

  saving.value = true
  const payload = {
    ...form.value,
    days: calculatedDays.value
  }

  const { error } = await api.post('/leave', payload)
  
  if (error) {
    toast.error(error)
  } else {
    toast.success('Leave application submitted successfully!')
    router.push('/leaves')
  }
  
  saving.value = false
}

const resetForm = () => {
  form.value = {
    employee_id: '',
    leave_type: '',
    other_leave_name: '',
    start_date: '',
    end_date: '',
    is_half_day: false,
    status: '1',
    reason: '',
    rejection_reason: ''
  }
  calculatedDays.value = 0
  remainingDays.value = null
}

onMounted(() => {
  loadEmployees()
  loadHolidays()
})
</script>
