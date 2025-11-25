<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Manage Salary</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and edit employee salary structures</p>
      </div>
    </div>

    <!-- Salary List -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-8">
          <div class="spinner w-8 h-8 mx-auto"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
          <Icon name="heroicons:currency-dollar" class="w-16 h-16 mx-auto text-gray-400" />
          <p class="mt-2 text-gray-600 dark:text-gray-400">No salary structures found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Basic Salary</th>
                <th>Gross Salary</th>
                <th>Net Salary</th>
                <th>Employment Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td class="font-medium">{{ item.employee_name }}</td>
                <td>{{ formatCurrency(item.basic_salary) }}</td>
                <td>{{ formatCurrency(item.gross_salary) }}</td>
                <td>{{ formatCurrency(item.net_salary) }}</td>
                <td><span class="badge badge-primary">{{ item.payment_frequency }}</span></td>
                <td>
                  <span :class="item.is_active ? 'badge badge-success' : 'badge badge-error'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="flex space-x-2">
                    <button @click="editSalary(item)" class="btn btn-sm btn-primary">
                      <Icon name="heroicons:pencil-square" class="w-4 h-4 mr-1" />Edit Salary
                    </button>
                    <button @click="deleteItem(item)" class="btn btn-sm btn-error">
                      <Icon name="heroicons:trash" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit Salary Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-5xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">Edit Salary</h2>
          
          <form @submit.prevent="saveSalary" class="space-y-6">
            <!-- Employee Selection -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Employee *</label>
                <select v-model="form.employee_id" required class="form-select" disabled>
                  <option value="">Select Employee</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                    {{ emp.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="form-label">Employment Type *</label>
                <select v-model="form.payment_frequency" required class="form-select">
                  <option value="monthly">Permanent</option>
                  <option value="weekly">Contract</option>
                  <option value="daily">Daily Wage</option>
                </select>
              </div>
            </div>

            <!-- Basic Salary and Overtime -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Basic Salary *</label>
                <input v-model.number="form.basic_salary" @input="calculateSalary" type="number" required min="0" step="0.01" class="form-input" />
              </div>
              <div>
                <label class="form-label">Overtime Rate</label>
                <input v-model.number="form.overtime_rate" type="number" min="0" step="0.01" class="form-input" />
              </div>
            </div>

            <!-- Allowances Section -->
            <div class="border-t pt-4">
              <h3 class="text-lg font-semibold mb-3">Allowances</h3>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="form-label">House Allowance</label>
                  <input v-model.number="form.house_allowance" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Transport Allowance</label>
                  <input v-model.number="form.transport_allowance" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Medical Allowance</label>
                  <input v-model.number="form.medical_allowance" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Communication Allowance</label>
                  <input v-model.number="form.communication_allowance" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Meal Allowance</label>
                  <input v-model.number="form.meal_allowance" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Other Allowances</label>
                  <input v-model.number="form.other_allowances" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
              </div>
            </div>

            <!-- Deductions Section -->
            <div class="border-t pt-4">
              <h3 class="text-lg font-semibold mb-3">Deductions</h3>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="form-label">NSSF Rate (%)</label>
                  <input v-model.number="form.nssf_rate" @input="calculateSalary" type="number" min="0" max="100" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">NHIF Amount</label>
                  <input v-model.number="form.nhif_amount" @input="calculateSalary" type="number" min="0" step="0.01" class="form-input" />
                </div>
                <div>
                  <label class="form-label">Housing Levy (%)</label>
                  <input v-model.number="form.housing_levy" @input="calculateSalary" type="number" min="0" max="100" step="0.01" class="form-input" />
                </div>
              </div>
            </div>

            <!-- Details Calculation -->
            <div class="border-t pt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <h3 class="text-lg font-semibold mb-3">Details Calculation</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Gross Salary (Basic Pay + Allowances)</p>
                  <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(calculated.gross_salary) }}</p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">NSSF</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.nssf) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">NHIF</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.nhif) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">Housing Levy</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.housing_levy) }}</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Taxable Pay</p>
                  <p class="text-xl font-bold text-purple-600">{{ formatCurrency(calculated.taxable_pay) }}</p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">Income Tax</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.income_tax) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">Personal Relief</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.personal_relief) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">PAYE</p>
                    <p class="font-semibold">{{ formatCurrency(calculated.paye) }}</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Pay After Tax</p>
                  <p class="text-xl font-bold text-orange-600">{{ formatCurrency(calculated.pay_after_tax) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Net Pay</p>
                  <p class="text-3xl font-bold text-green-600">{{ formatCurrency(calculated.net_pay) }}</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6 border-t pt-4">
              <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
              <button type="submit" :disabled="saving" class="btn btn-primary">
                <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
                {{ saving ? 'Saving...' : 'Save Changes' }}
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
useHead({ title: 'Manage Salary' })

const api = useApi()
const toast = useToast()

const items = ref([])
const employees = ref([])
const loading = ref(false)
const saving = ref(false)
const showEditModal = ref(false)

const form = ref({
  id: null,
  employee_id: '',
  basic_salary: 0,
  house_allowance: 0,
  transport_allowance: 0,
  medical_allowance: 0,
  communication_allowance: 0,
  meal_allowance: 0,
  other_allowances: 0,
  nssf_rate: 6.00,
  nhif_amount: 0,
  housing_levy: 1.5,
  overtime_rate: 0,
  payment_frequency: 'monthly'
})

const calculated = ref({
  gross_salary: 0,
  nssf: 0,
  nhif: 0,
  housing_levy: 0,
  taxable_pay: 0,
  income_tax: 0,
  personal_relief: 2400,
  paye: 0,
  pay_after_tax: 0,
  net_pay: 0
})

const loadItems = async () => {
  loading.value = true
  const { data, error } = await api.get('/salary-structures')
  if (error) toast.error(error)
  else items.value = data.items
  loading.value = false
}

const loadEmployees = async () => {
  const { data, error } = await api.get('/employees')
  if (!error) employees.value = data.items
}

const calculateSalary = () => {
  // Calculate gross salary
  const gross = form.value.basic_salary + 
    form.value.house_allowance + 
    form.value.transport_allowance + 
    form.value.medical_allowance + 
    form.value.communication_allowance + 
    form.value.meal_allowance + 
    form.value.other_allowances
  
  calculated.value.gross_salary = gross
  
  // Calculate NSSF (6% of gross, max 2,160)
  calculated.value.nssf = Math.min((gross * form.value.nssf_rate / 100), 2160)
  
  // NHIF
  calculated.value.nhif = form.value.nhif_amount
  
  // Housing Levy (1.5% of gross)
  calculated.value.housing_levy = gross * (form.value.housing_levy / 100)
  
  // Taxable pay
  calculated.value.taxable_pay = gross - calculated.value.nssf - calculated.value.nhif - calculated.value.housing_levy
  
  // Calculate PAYE (simplified Kenya tax bands)
  let tax = 0
  const taxable = calculated.value.taxable_pay
  
  if (taxable <= 24000) {
    tax = taxable * 0.10
  } else if (taxable <= 32333) {
    tax = 2400 + (taxable - 24000) * 0.25
  } else if (taxable <= 500000) {
    tax = 2400 + 2083.25 + (taxable - 32333) * 0.30
  } else if (taxable <= 800000) {
    tax = 2400 + 2083.25 + 140300.10 + (taxable - 500000) * 0.325
  } else {
    tax = 2400 + 2083.25 + 140300.10 + 97500 + (taxable - 800000) * 0.35
  }
  
  calculated.value.income_tax = tax
  calculated.value.paye = Math.max(tax - calculated.value.personal_relief, 0)
  calculated.value.pay_after_tax = calculated.value.taxable_pay - calculated.value.paye
  calculated.value.net_pay = calculated.value.pay_after_tax
}

const editSalary = (item: any) => {
  form.value = { ...item }
  calculateSalary()
  showEditModal.value = true
}

const saveSalary = async () => {
  saving.value = true
  const { error } = await api.put(`/salary-structures/${form.value.id}`, form.value)
  if (error) toast.error(error)
  else {
    toast.success('Salary updated successfully!')
    closeModal()
    loadItems()
  }
  saving.value = false
}

const deleteItem = async (item: any) => {
  if (!confirm(`Are you sure you want to delete salary structure for ${item.employee_name}?`)) return
  const { error } = await api.delete(`/salary-structures/${item.id}`)
  if (error) toast.error(error)
  else {
    toast.success('Salary structure deleted!')
    loadItems()
  }
}

const closeModal = () => {
  showEditModal.value = false
  form.value = {
    id: null,
    employee_id: '',
    basic_salary: 0,
    house_allowance: 0,
    transport_allowance: 0,
    medical_allowance: 0,
    communication_allowance: 0,
    meal_allowance: 0,
    other_allowances: 0,
    nssf_rate: 6.00,
    nhif_amount: 0,
    housing_levy: 1.5,
    overtime_rate: 0,
    payment_frequency: 'monthly'
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
}

onMounted(() => {
  loadItems()
  loadEmployees()
})
</script>
