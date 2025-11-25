<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Leave Configuration</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure working days, leave days, and holidays</p>
    </div>

    <!-- Working Days Configuration -->
    <div class="card mb-6">
      <div class="card-body">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Set Working Days</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
          <label v-for="day in weekDays" :key="day.value" class="flex items-center p-3 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
            <input v-model="workingDays[day.value]" type="checkbox" class="form-checkbox" @change="saveWorkingDays" />
            <span class="ml-2 font-medium">{{ day.label }}</span>
          </label>
        </div>
        <p class="text-sm text-gray-500 mt-3">
          Selected: {{ Object.values(workingDays).filter(v => v).length }} days per week
        </p>
      </div>
    </div>

    <!-- Leave Days Configuration -->
    <div class="card mb-6">
      <div class="card-body">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Set Leave Days</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="type in leaveTypes" :key="type.key" class="border rounded-lg p-4">
            <label class="form-label">{{ type.label }}</label>
            <div class="flex items-center space-x-2">
              <input v-model.number="leaveDays[type.key]" type="number" min="0" class="form-input" @change="saveLeaveDays" />
              <span class="text-sm text-gray-500">days/year</span>
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="saveLeaveDays" class="btn btn-primary">
            <Icon name="heroicons:check" class="w-4 h-4 mr-2" />Save Leave Days
          </button>
        </div>
      </div>
    </div>

    <!-- Holidays Calendar -->
    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mark Holidays</h2>
          <div class="flex space-x-2">
            <button @click="previousMonth" class="btn btn-sm btn-outline">
              <Icon name="heroicons:chevron-left" class="w-4 h-4" />
            </button>
            <span class="px-4 py-2 font-semibold">{{ currentMonthName }} {{ currentYear }}</span>
            <button @click="nextMonth" class="btn btn-sm btn-outline">
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Calendar -->
        <div class="border rounded-lg overflow-hidden">
          <!-- Calendar Header -->
          <div class="grid grid-cols-7 bg-gray-100 dark:bg-gray-700">
            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="p-3 text-center font-semibold text-sm">
              {{ day }}
            </div>
          </div>

          <!-- Calendar Days -->
          <div class="grid grid-cols-7">
            <div
              v-for="(day, index) in calendarDays"
              :key="index"
              :class="[
                'p-3 border-t border-r text-center cursor-pointer transition-colors',
                day.isCurrentMonth ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900 text-gray-400',
                day.isToday ? 'bg-blue-50 dark:bg-blue-900/20 font-bold' : '',
                day.isHoliday ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : '',
                day.isWeekend ? 'bg-gray-100 dark:bg-gray-700' : '',
                'hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
              @click="toggleHoliday(day)"
            >
              <div class="text-sm">{{ day.date }}</div>
              <div v-if="day.isHoliday" class="text-xs mt-1">
                <Icon name="heroicons:star" class="w-3 h-3 inline" />
              </div>
            </div>
          </div>
        </div>

        <!-- Holidays List -->
        <div class="mt-6">
          <h3 class="font-semibold mb-3">Marked Holidays ({{ holidays.length }})</h3>
          <div v-if="holidays.length === 0" class="text-center py-4 text-gray-500">
            No holidays marked. Click on calendar dates to mark holidays.
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="holiday in holidays" :key="holiday.date" class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <div class="font-medium">{{ formatHolidayDate(holiday.date) }}</div>
                <input
                  v-model="holiday.name"
                  type="text"
                  placeholder="Holiday name..."
                  class="text-sm text-gray-600 dark:text-gray-400 bg-transparent border-none focus:outline-none mt-1"
                  @change="saveHolidays"
                />
              </div>
              <button @click="removeHoliday(holiday.date)" class="text-red-600 hover:text-red-800">
                <Icon name="heroicons:x-mark" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button @click="saveHolidays" class="btn btn-primary">
            <Icon name="heroicons:check" class="w-4 h-4 mr-2" />Save Holidays
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Leave Configuration' })

const api = useApi()
const toast = useToast()

const weekDays = [
  { label: 'Monday', value: 'monday' },
  { label: 'Tuesday', value: 'tuesday' },
  { label: 'Wednesday', value: 'wednesday' },
  { label: 'Thursday', value: 'thursday' },
  { label: 'Friday', value: 'friday' },
  { label: 'Saturday', value: 'saturday' },
  { label: 'Sunday', value: 'sunday' }
]

const leaveTypes = [
  { key: 'annual', label: 'ANNUAL' },
  { key: 'sick', label: 'SICK' },
  { key: 'maternity', label: 'MATERNITY' },
  { key: 'paternity', label: 'PATERNITY' },
  { key: 'study', label: 'STUDY' },
  { key: 'unpaid', label: 'UNPAID' },
  { key: 'mourning', label: 'MOURNING' },
  { key: 'compassionate', label: 'COMPASSIONATE' },
  { key: 'other', label: 'OTHER' }
]

const workingDays = ref({
  monday: true,
  tuesday: true,
  wednesday: true,
  thursday: true,
  friday: true,
  saturday: false,
  sunday: false
})

const leaveDays = ref({
  annual: 21,
  sick: 10,
  maternity: 90,
  paternity: 14,
  study: 5,
  unpaid: 0,
  mourning: 7,
  compassionate: 3,
  other: 0
})

const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const holidays = ref([])

const currentMonthName = computed(() => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return months[currentMonth.value]
})

const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const prevLastDay = new Date(currentYear.value, currentMonth.value, 0)
  
  const firstDayOfWeek = firstDay.getDay()
  const lastDate = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()
  
  const today = new Date()
  
  // Previous month days
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = prevLastDate - i
    const fullDate = new Date(currentYear.value, currentMonth.value - 1, date)
    days.push({
      date,
      fullDate: fullDate.toISOString().split('T')[0],
      isCurrentMonth: false,
      isToday: false,
      isHoliday: isHolidayDate(fullDate.toISOString().split('T')[0]),
      isWeekend: fullDate.getDay() === 0 || fullDate.getDay() === 6
    })
  }
  
  // Current month days
  for (let date = 1; date <= lastDate; date++) {
    const fullDate = new Date(currentYear.value, currentMonth.value, date)
    const dateStr = fullDate.toISOString().split('T')[0]
    days.push({
      date,
      fullDate: dateStr,
      isCurrentMonth: true,
      isToday: dateStr === today.toISOString().split('T')[0],
      isHoliday: isHolidayDate(dateStr),
      isWeekend: fullDate.getDay() === 0 || fullDate.getDay() === 6
    })
  }
  
  // Next month days
  const remainingDays = 42 - days.length
  for (let date = 1; date <= remainingDays; date++) {
    const fullDate = new Date(currentYear.value, currentMonth.value + 1, date)
    days.push({
      date,
      fullDate: fullDate.toISOString().split('T')[0],
      isCurrentMonth: false,
      isToday: false,
      isHoliday: isHolidayDate(fullDate.toISOString().split('T')[0]),
      isWeekend: fullDate.getDay() === 0 || fullDate.getDay() === 6
    })
  }
  
  return days
})

const isHolidayDate = (date: string) => {
  return holidays.value.some(h => h.date === date)
}

const toggleHoliday = (day: any) => {
  if (!day.isCurrentMonth) return
  
  const index = holidays.value.findIndex(h => h.date === day.fullDate)
  if (index > -1) {
    holidays.value.splice(index, 1)
  } else {
    holidays.value.push({
      date: day.fullDate,
      name: ''
    })
  }
}

const removeHoliday = (date: string) => {
  const index = holidays.value.findIndex(h => h.date === date)
  if (index > -1) {
    holidays.value.splice(index, 1)
  }
}

const formatHolidayDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const saveWorkingDays = async () => {
  const { error } = await api.post('/leave-config/working-days', workingDays.value)
  if (error) toast.error(error)
  else toast.success('Working days saved!')
}

const saveLeaveDays = async () => {
  const { error } = await api.post('/leave-config/leave-days', leaveDays.value)
  if (error) toast.error(error)
  else toast.success('Leave days saved!')
}

const saveHolidays = async () => {
  const { error } = await api.post('/leave-config/holidays', { holidays: holidays.value })
  if (error) toast.error(error)
  else toast.success('Holidays saved!')
}

const loadConfig = async () => {
  // Load working days
  const { data: workingData } = await api.get('/leave-config/working-days')
  if (workingData) workingDays.value = workingData

  // Load leave days
  const { data: leaveData } = await api.get('/leave-config/leave-days')
  if (leaveData) leaveDays.value = leaveData

  // Load holidays
  const { data: holidayData } = await api.get('/leave-config/holidays')
  if (holidayData) holidays.value = holidayData.holidays || []
}

onMounted(() => {
  loadConfig()
})
</script>
