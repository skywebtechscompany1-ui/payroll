<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-16">
      <!-- Hero Section -->
      <div class="text-center max-w-4xl mx-auto mb-16">
        <!-- Logo/Icon -->
        <div class="mx-auto h-20 w-20 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 mb-8">
          <Icon name="heroicons:building-office-2" class="h-12 w-12 text-primary-600 dark:text-primary-400" />
        </div>

        <!-- Welcome Message -->
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
          Welcome to the
          <span class="text-primary-600 dark:text-primary-400"> Payroll Management System</span>
        </h1>

        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
          Streamline your payroll operations with our comprehensive management solution.
          Handle employee payroll, manage leave requests, generate reports, and more - all in one secure platform.
        </p>

        <!-- Call to Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <NuxtLink
            to="/login"
            class="btn-primary text-lg px-8 py-4 flex items-center justify-center min-w-[200px]"
          >
            <Icon name="heroicons:arrow-right-to-bracket" class="w-5 h-5 mr-2" />
            Sign In
          </NuxtLink>

          <NuxtLink
            to="/register"
            class="btn-outline text-lg px-8 py-4 flex items-center justify-center min-w-[200px]"
          >
            <Icon name="heroicons:user-plus" class="w-5 h-5 mr-2" />
            Create Account
          </NuxtLink>
        </div>
      </div>

      <!-- Features Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto mb-16">
        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:users" class="w-8 h-8 text-primary-600 dark:text-primary-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Employee Management</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Manage employee profiles, departments, and job roles with ease. Keep track of all employee information in one centralized location.
          </p>
        </div>

        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-success-100 dark:bg-success-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:banknotes" class="w-8 h-8 text-success-600 dark:text-success-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Payroll Processing</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Automate payroll calculations, manage deductions, and ensure timely salary payments. Generate detailed payroll reports instantly.
          </p>
        </div>

        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-warning-100 dark:bg-warning-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:calendar" class="w-8 h-8 text-warning-600 dark:text-warning-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Leave Management</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Track employee leave requests, manage approvals, and maintain accurate leave balance records. Streamline your leave workflow.
          </p>
        </div>

        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-secondary-100 dark:bg-secondary-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:chart-bar" class="w-8 h-8 text-secondary-600 dark:text-secondary-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Analytics & Reports</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Generate comprehensive reports and insights on payroll expenses, employee productivity, and department-wise metrics.
          </p>
        </div>

        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:shield-check" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Role-Based Access</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Secure access control with customizable user roles and permissions. Ensure data privacy and compliance with industry standards.
          </p>
        </div>

        <div class="card text-center p-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-info-100 dark:bg-info-900 rounded-full flex items-center justify-center">
            <Icon name="heroicons:cog-6-tooth" class="w-8 h-8 text-info-600 dark:text-info-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">System Configuration</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Flexible system settings to match your organization's unique requirements. Configure payroll rules, tax settings, and more.
          </p>
        </div>
      </div>

      <!-- Additional Info Section -->
      <div class="text-center max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Ready to get started?
          </h2>
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            Join thousands of organizations that trust our payroll management system to handle their most critical HR operations.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <NuxtLink
              to="/login"
              class="btn-primary flex items-center justify-center"
            >
              <Icon name="heroicons:arrow-right-to-bracket" class="w-4 h-4 mr-2" />
              Login to Your Account
            </NuxtLink>
            <NuxtLink
              to="/register"
              class="btn-outline flex items-center justify-center"
            >
              <Icon name="heroicons:information-circle" class="w-4 h-4 mr-2" />
              Learn More
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'guest'
})

const auth = useAuth()

// Redirect if already authenticated
onMounted(() => {
  if (auth.isAuthenticated) {
    // Redirect based on user role
    if (auth.isAdmin) {
      navigateTo('/dashboard')
    } else if (auth.isHR) {
      navigateTo('/employees')
    } else {
      navigateTo('/profile')
    }
  }
})

// Page metadata
useHead({
  title: 'Payroll Management System',
  meta: [
    { name: 'description', content: 'Modern Payroll Management System - Streamline your payroll operations with our comprehensive solution.' }
  ]
})
</script>