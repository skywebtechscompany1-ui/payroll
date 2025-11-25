<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">System Settings</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure system-wide settings and branding</p>
      </div>
      <button @click="showImportModal = true" class="btn btn-primary">
        <Icon name="heroicons:arrow-up-tray" class="w-4 h-4 mr-2" />
        Import Data
      </button>
    </div>

    <!-- Settings Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Settings -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Company Information -->
        <div class="card">
          <div class="card-body">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Company Information</h2>
            <div class="space-y-4">
              <div>
                <label class="form-label">Company Name *</label>
                <input v-model="settings.company_name" type="text" required class="form-input" placeholder="Enter company name" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Email</label>
                  <input v-model="settings.company_email" type="email" class="form-input" placeholder="company@example.com" />
                </div>
                <div>
                  <label class="form-label">Phone</label>
                  <input v-model="settings.company_phone" type="tel" class="form-input" placeholder="+254 700 000 000" />
                </div>
              </div>
              <div>
                <label class="form-label">Address</label>
                <textarea v-model="settings.company_address" class="form-input" rows="3" placeholder="Company address"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- System Configuration -->
        <div class="card">
          <div class="card-body">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">System Configuration</h2>
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Currency</label>
                  <select v-model="settings.currency" class="form-select">
                    <option value="KES">KES - Kenyan Shilling</option>
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="GBP">GBP - British Pound</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Timezone</label>
                  <select v-model="settings.timezone" class="form-select">
                    <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">America/New York (EST)</option>
                    <option value="Europe/London">Europe/London (GMT)</option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Date Format</label>
                  <select v-model="settings.date_format" class="form-select">
                    <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                    <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                    <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Time Format</label>
                  <select v-model="settings.time_format" class="form-select">
                    <option value="12">12 Hour</option>
                    <option value="24">24 Hour</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payroll Settings -->
        <div class="card">
          <div class="card-body">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payroll Settings</h2>
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Payroll Cycle</label>
                  <select v-model="settings.payroll_cycle" class="form-select">
                    <option value="monthly">Monthly</option>
                    <option value="bi-weekly">Bi-Weekly</option>
                    <option value="weekly">Weekly</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Payment Day</label>
                  <input v-model.number="settings.payment_day" type="number" min="1" max="31" class="form-input" placeholder="e.g., 25" />
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Working Hours/Day</label>
                  <input v-model.number="settings.working_hours_per_day" type="number" min="1" max="24" step="0.5" class="form-input" placeholder="8" />
                </div>
                <div>
                  <label class="form-label">Working Days/Week</label>
                  <input v-model.number="settings.working_days_per_week" type="number" min="1" max="7" class="form-input" placeholder="5" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Logo Upload -->
      <div class="lg:col-span-1">
        <div class="card sticky top-6">
          <div class="card-body">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Company Logo</h2>
            
            <!-- Logo Preview -->
            <div class="mb-4">
              <div v-if="logoPreview || settings.logo_url" class="relative group">
                <img :src="logoPreview || settings.logo_url" alt="Company Logo" class="w-full h-48 object-contain bg-gray-100 dark:bg-gray-700 rounded-lg" />
                <button @click="removeLogo" class="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                  <Icon name="heroicons:x-mark" class="w-4 h-4" />
                </button>
              </div>
              <div v-else class="w-full h-48 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <div class="text-center text-gray-400">
                  <Icon name="heroicons:photo" class="w-16 h-16 mx-auto mb-2" />
                  <p class="text-sm">No logo uploaded</p>
                </div>
              </div>
            </div>

            <!-- Upload Area -->
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary-500 transition-colors cursor-pointer" @click="triggerFileInput" @drop.prevent="handleDrop" @dragover.prevent>
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
              <Icon name="heroicons:cloud-arrow-up" class="w-12 h-12 mx-auto text-gray-400 mb-2" />
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                <span class="text-primary-600 font-semibold">Click to upload</span> or drag and drop
              </p>
              <p class="text-xs text-gray-500">PNG, JPG, SVG up to 2MB</p>
            </div>

            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <p class="text-xs text-blue-800 dark:text-blue-200">
                <Icon name="heroicons:information-circle" class="w-4 h-4 inline mr-1" />
                Logo will appear in reports, payslips, and system header
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="mt-6 flex justify-end space-x-3">
      <button @click="resetSettings" class="btn btn-outline">Reset</button>
      <button @click="saveSettings" :disabled="saving" class="btn btn-primary">
        <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>

    <!-- Import Data Modal -->
    <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeImportModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Import Data</h2>
            <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
              <Icon name="heroicons:x-mark" class="w-6 h-6" />
            </button>
          </div>

          <!-- Import Instructions -->
          <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex">
              <Icon name="heroicons:information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">Upload CSV or Excel files</p>
                <p>The system will automatically detect the data type and import to the correct module.</p>
              </div>
            </div>
          </div>

          <!-- File Upload Area -->
          <div class="mb-6">
            <div 
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-primary-500 transition-colors cursor-pointer"
              @click="triggerImportFileInput"
              @drop.prevent="handleImportDrop"
              @dragover.prevent
            >
              <input ref="importFileInput" type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="handleImportFileSelect" />
              <Icon name="heroicons:document-arrow-up" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
              <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Drop your file here or click to browse
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Supports: CSV, Excel (.xlsx, .xls)
              </p>
            </div>
          </div>

          <!-- Selected File -->
          <div v-if="importFile" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <Icon name="heroicons:document-text" class="w-8 h-8 text-primary-600 mr-3" />
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ importFile.name }}</p>
                  <p class="text-sm text-gray-500">{{ formatFileSize(importFile.size) }}</p>
                </div>
              </div>
              <button @click="removeImportFile" class="text-red-600 hover:text-red-700">
                <Icon name="heroicons:trash" class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Data Type Selection -->
          <div v-if="importFile" class="mb-6">
            <label class="form-label">Select Data Type</label>
            <select v-model="importDataType" class="form-select">
              <option value="">Auto-detect from file</option>
              <option value="employees">Employees</option>
              <option value="departments">Departments</option>
              <option value="designations">Designations</option>
              <option value="attendance">Attendance</option>
              <option value="leave">Leave Records</option>
              <option value="payroll">Payroll</option>
              <option value="users">Users</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Leave blank to auto-detect based on column headers</p>
          </div>

          <!-- Import Options -->
          <div v-if="importFile" class="mb-6 space-y-3">
            <label class="inline-flex items-center">
              <input v-model="importOptions.skipFirstRow" type="checkbox" class="form-checkbox" />
              <span class="ml-2 text-sm">Skip first row (headers)</span>
            </label>
            <label class="inline-flex items-center">
              <input v-model="importOptions.updateExisting" type="checkbox" class="form-checkbox" />
              <span class="ml-2 text-sm">Update existing records</span>
            </label>
            <label class="inline-flex items-center">
              <input v-model="importOptions.validateData" type="checkbox" class="form-checkbox" />
              <span class="ml-2 text-sm">Validate data before import</span>
            </label>
          </div>

          <!-- Import Progress -->
          <div v-if="importing" class="mb-6">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium">Importing...</span>
              <span class="text-sm text-gray-500">{{ importProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-primary-600 h-2 rounded-full transition-all" :style="{ width: importProgress + '%' }"></div>
            </div>
          </div>

          <!-- Import Results -->
          <div v-if="importResults" class="mb-6 p-4 rounded-lg" :class="importResults.success ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
            <div class="flex items-start">
              <Icon :name="importResults.success ? 'heroicons:check-circle' : 'heroicons:x-circle'" 
                    class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"
                    :class="importResults.success ? 'text-green-600' : 'text-red-600'" />
              <div class="flex-1">
                <p class="font-semibold" :class="importResults.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                  {{ importResults.message }}
                </p>
                <div v-if="importResults.details" class="mt-2 text-sm" :class="importResults.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                  <p v-if="importResults.details.imported">✓ Imported: {{ importResults.details.imported }} records</p>
                  <p v-if="importResults.details.updated">✓ Updated: {{ importResults.details.updated }} records</p>
                  <p v-if="importResults.details.skipped">⚠ Skipped: {{ importResults.details.skipped }} records</p>
                  <p v-if="importResults.details.errors">✗ Errors: {{ importResults.details.errors }} records</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3">
            <button @click="closeImportModal" class="btn btn-outline">Cancel</button>
            <button 
              @click="handleImport" 
              :disabled="!importFile || importing" 
              class="btn btn-primary"
            >
              <Icon v-if="importing" name="heroicons:arrow-path" class="w-4 h-4 mr-2 animate-spin" />
              {{ importing ? 'Importing...' : 'Import Data' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'System Settings' })

const api = useApi()
const toast = useToast()

const saving = ref(false)
const fileInput = ref(null)
const logoPreview = ref('')
const logoFile = ref(null)

// Import Data State
const showImportModal = ref(false)
const importFileInput = ref(null)
const importFile = ref(null)
const importDataType = ref('')
const importing = ref(false)
const importProgress = ref(0)
const importResults = ref(null)
const importOptions = ref({
  skipFirstRow: true,
  updateExisting: false,
  validateData: true
})

const settings = ref({
  company_name: 'Jafasol Systems',
  company_email: 'info@jafasol.com',
  company_phone: '+254 700 000 000',
  company_address: 'Nairobi, Kenya',
  logo_url: '',
  currency: 'KES',
  timezone: 'Africa/Nairobi',
  date_format: 'DD/MM/YYYY',
  time_format: '24',
  payroll_cycle: 'monthly',
  payment_day: 25,
  working_hours_per_day: 8,
  working_days_per_week: 5
})

const loadSettings = async () => {
  const { data, error } = await api.get('/system-settings')
  if (!error && data) {
    settings.value = { ...settings.value, ...data }
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processFile(file)
  }
}

const handleDrop = (event: DragEvent) => {
  const file = event.dataTransfer?.files?.[0]
  if (file) {
    processFile(file)
  }
}

const processFile = (file: File) => {
  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Please upload an image file')
    return
  }

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('File size must be less than 2MB')
    return
  }

  logoFile.value = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const removeLogo = () => {
  logoPreview.value = ''
  logoFile.value = null
  settings.value.logo_url = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const saveSettings = async () => {
  saving.value = true

  try {
    // Upload logo if new file selected
    if (logoFile.value) {
      const formData = new FormData()
      formData.append('logo', logoFile.value)

      const { data: uploadData, error: uploadError } = await api.post('/system-settings/upload-logo', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })

      if (uploadError) {
        toast.error('Failed to upload logo')
        saving.value = false
        return
      }

      settings.value.logo_url = uploadData.logo_url
    }

    // Save settings
    const { error } = await api.post('/system-settings', settings.value)

    if (error) {
      toast.error(error)
    } else {
      toast.success('Settings saved successfully!')
      // Update localStorage for immediate effect
      localStorage.setItem('systemSettings', JSON.stringify(settings.value))
      // Reload to apply changes
      setTimeout(() => window.location.reload(), 1000)
    }
  } catch (err) {
    toast.error('An error occurred while saving settings')
  }

  saving.value = false
}

const resetSettings = () => {
  loadSettings()
  logoPreview.value = ''
  logoFile.value = null
}

// Import Data Functions
const triggerImportFileInput = () => {
  importFileInput.value?.click()
}

const handleImportFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processImportFile(file)
  }
}

const handleImportDrop = (event: DragEvent) => {
  const file = event.dataTransfer?.files?.[0]
  if (file) {
    processImportFile(file)
  }
}

const processImportFile = (file: File) => {
  // Validate file type
  const validTypes = [
    'text/csv',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ]
  
  if (!validTypes.includes(file.type) && !file.name.match(/\.(csv|xlsx|xls)$/i)) {
    toast.error('Please upload a CSV or Excel file')
    return
  }

  // Validate file size (10MB)
  if (file.size > 10 * 1024 * 1024) {
    toast.error('File size must be less than 10MB')
    return
  }

  importFile.value = file
  importResults.value = null
}

const removeImportFile = () => {
  importFile.value = null
  importDataType.value = ''
  importResults.value = null
  if (importFileInput.value) {
    importFileInput.value.value = ''
  }
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const handleImport = async () => {
  if (!importFile.value) return

  importing.value = true
  importProgress.value = 0
  importResults.value = null

  try {
    const formData = new FormData()
    formData.append('file', importFile.value)
    formData.append('data_type', importDataType.value)
    formData.append('skip_first_row', importOptions.value.skipFirstRow.toString())
    formData.append('update_existing', importOptions.value.updateExisting.toString())
    formData.append('validate_data', importOptions.value.validateData.toString())

    // Simulate progress
    const progressInterval = setInterval(() => {
      if (importProgress.value < 90) {
        importProgress.value += 10
      }
    }, 200)

    const { data, error } = await api.post('/system-settings/import-data', formData)

    clearInterval(progressInterval)
    importProgress.value = 100

    if (error) {
      importResults.value = {
        success: false,
        message: error || 'Import failed',
        details: null
      }
      toast.error('Import failed')
    } else {
      importResults.value = {
        success: true,
        message: data.message || 'Data imported successfully!',
        details: {
          imported: data.imported || 0,
          updated: data.updated || 0,
          skipped: data.skipped || 0,
          errors: data.errors || 0
        }
      }
      toast.success('Data imported successfully!')
      
      // Refresh page after 2 seconds
      setTimeout(() => {
        window.location.reload()
      }, 2000)
    }
  } catch (err) {
    importResults.value = {
      success: false,
      message: 'An error occurred during import',
      details: null
    }
    toast.error('Import failed')
  }

  importing.value = false
}

const closeImportModal = () => {
  showImportModal.value = false
  importFile.value = null
  importDataType.value = ''
  importing.value = false
  importProgress.value = 0
  importResults.value = null
}

onMounted(() => {
  loadSettings()
})
</script>
