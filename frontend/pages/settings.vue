<template>
  <div>
    <div class="mb-6"><h1 class="text-2xl font-semibold text-gray-900 dark:text-white">System Settings</h1><p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure system preferences</p></div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <div class="space-y-2">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="['w-full text-left px-4 py-3 rounded-lg transition-colors', activeTab === tab.id ? 'bg-primary-50 dark:bg-primary-900 text-primary-600 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700']">
          <Icon :name="tab.icon" class="w-5 h-5 inline mr-2" />{{ tab.name }}
        </button>
      </div>
      <div class="lg:col-span-3">
        <div v-if="activeTab === 'general'" class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">General Settings</h2></div>
          <div class="card-body space-y-4">
            <div><label class="form-label">Company Name</label><input v-model="settings.companyName" type="text" class="form-input" /></div>
            <div><label class="form-label">Company Email</label><input v-model="settings.companyEmail" type="email" class="form-input" /></div>
            <div><label class="form-label">Currency</label>
              <select v-model="settings.currency" class="form-select">
                <option value="USD">USD ($)</option>
                <option value="EUR">EUR (€)</option>
                <option value="GBP">GBP (£)</option>
                <option value="KES">KES (KSh)</option>
              </select>
            </div>
            <div class="flex justify-end"><button @click="saveSettings" class="btn btn-primary">Save Changes</button></div>
          </div>
        </div>
        <div v-if="activeTab === 'payroll'" class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payroll Settings</h2></div>
          <div class="card-body space-y-4">
            <div><label class="form-label">Pay Frequency</label>
              <select v-model="settings.payFrequency" class="form-select">
                <option value="monthly">Monthly</option>
                <option value="bi-weekly">Bi-weekly</option>
                <option value="weekly">Weekly</option>
              </select>
            </div>
            <div><label class="form-label">Tax Rate (%)</label><input v-model="settings.taxRate" type="number" class="form-input" step="0.01" /></div>
            <div class="flex items-center"><input v-model="settings.autoProcessPayroll" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto-process payroll on schedule</label></div>
            <div class="flex justify-end"><button @click="saveSettings" class="btn btn-primary">Save Changes</button></div>
          </div>
        </div>
        <div v-if="activeTab === 'notifications'" class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Notification Settings</h2></div>
          <div class="card-body space-y-4">
            <div class="flex items-center"><input v-model="settings.emailNotifications" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Email notifications</label></div>
            <div class="flex items-center"><input v-model="settings.leaveNotifications" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Leave request notifications</label></div>
            <div class="flex items-center"><input v-model="settings.payrollNotifications" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Payroll processing notifications</label></div>
            <div class="flex justify-end"><button @click="saveSettings" class="btn btn-primary">Save Changes</button></div>
          </div>
        </div>
        <div v-if="activeTab === 'security'" class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Security Settings</h2></div>
          <div class="card-body space-y-4">
            <div><label class="form-label">Session Timeout (minutes)</label><input v-model="settings.sessionTimeout" type="number" class="form-input" min="5" /></div>
            <div class="flex items-center"><input v-model="settings.twoFactorAuth" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable two-factor authentication</label></div>
            <div class="flex items-center"><input v-model="settings.passwordExpiry" type="checkbox" class="form-checkbox" /><label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Require password change every 90 days</label></div>
            <div class="flex justify-end"><button @click="saveSettings" class="btn btn-primary">Save Changes</button></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Settings' })
const toast = useToast()
const activeTab = ref('general')
const tabs = [
  { id: 'general', name: 'General', icon: 'heroicons:cog-6-tooth' },
  { id: 'payroll', name: 'Payroll', icon: 'heroicons:currency-dollar' },
  { id: 'notifications', name: 'Notifications', icon: 'heroicons:bell' },
  { id: 'security', name: 'Security', icon: 'heroicons:shield-check' }
]
const settings = ref({
  companyName: 'My Company',
  companyEmail: 'info@company.com',
  currency: 'USD',
  payFrequency: 'monthly',
  taxRate: 15,
  autoProcessPayroll: false,
  emailNotifications: true,
  leaveNotifications: true,
  payrollNotifications: true,
  sessionTimeout: 30,
  twoFactorAuth: false,
  passwordExpiry: false
})
const saveSettings = () => {
  toast.success('Settings saved successfully!')
}
</script>
