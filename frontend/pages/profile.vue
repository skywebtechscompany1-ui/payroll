<template>
  <div>
    <div class="mb-6"><h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Profile</h1><p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your personal information</p></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="card">
        <div class="card-body text-center">
          <div class="w-32 h-32 mx-auto bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mb-4">
            <Icon name="heroicons:user" class="w-16 h-16 text-primary-600" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ user.name }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ user.email }}</p>
          <div class="mt-4"><span class="badge badge-primary">{{ getRoleName(user.access_label) }}</span></div>
        </div>
      </div>
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2></div>
          <div class="card-body">
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Full Name *</label><input v-model="form.name" type="text" required class="form-input" /></div>
                <div><label class="form-label">Email *</label><input v-model="form.email" type="email" required class="form-input" /></div>
                <div><label class="form-label">Contact Number</label><input v-model="form.contact_no_one" type="tel" class="form-input" /></div>
                <div><label class="form-label">Gender</label>
                  <select v-model="form.gender" class="form-select">
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                  </select>
                </div>
              </div>
              <div><label class="form-label">Address</label><textarea v-model="form.present_address" class="form-textarea" rows="2"></textarea></div>
              <div class="flex justify-end"><button type="submit" :disabled="saving" class="btn btn-primary">{{ saving ? 'Saving...' : 'Update Profile' }}</button></div>
            </form>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Change Password</h2></div>
          <div class="card-body">
            <form @submit.prevent="changePassword" class="space-y-4">
              <div><label class="form-label">Current Password *</label><input v-model="passwordForm.current" type="password" required class="form-input" minlength="6" /></div>
              <div><label class="form-label">New Password *</label><input v-model="passwordForm.new" type="password" required class="form-input" minlength="6" /></div>
              <div><label class="form-label">Confirm Password *</label><input v-model="passwordForm.confirm" type="password" required class="form-input" minlength="6" /></div>
              <div class="flex justify-end"><button type="submit" :disabled="changingPassword" class="btn btn-primary">{{ changingPassword ? 'Changing...' : 'Change Password' }}</button></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Profile' })
const auth = useAuth()
const api = useApi()
const toast = useToast()
const user = computed(() => auth.user.value || {})
const saving = ref(false)
const changingPassword = ref(false)
const form = ref({ name: '', email: '', contact_no_one: '', gender: 'M', present_address: '' })
const passwordForm = ref({ current: '', new: '', confirm: '' })
const updateProfile = async () => {
  saving.value = true
  const { error } = await api.put(`/employees/${user.value.id}`, form.value)
  if (error) toast.error(error)
  else { toast.success('Profile updated!'); await auth.fetchUser() }
  saving.value = false
}
const changePassword = async () => {
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    toast.error('Passwords do not match')
    return
  }
  changingPassword.value = true
  const { error } = await api.post('/auth/change-password', { old_password: passwordForm.value.current, new_password: passwordForm.value.new })
  if (error) toast.error(error)
  else { toast.success('Password changed!'); passwordForm.value = { current: '', new: '', confirm: '' } }
  changingPassword.value = false
}
const getRoleName = (role: number) => ['', 'Super Admin', 'Admin', 'HR', 'Manager', 'Employee'][role] || 'Employee'
onMounted(() => { if (user.value) form.value = { ...user.value } })
</script>
