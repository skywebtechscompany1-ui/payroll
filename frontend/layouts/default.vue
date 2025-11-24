<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Mobile sidebar backdrop -->
    <div
      v-if="sidebarOpen && isMobile"
      class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
      @click="closeSidebar"
    />

    <!-- Sidebar -->
    <aside
      :class="[
        'sidebar transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            Payroll System
          </h1>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
          <NuxtLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            :class="[
              'sidebar-item group',
              $route.path === item.href ? 'active' : ''
            ]"
            @click="closeSidebar"
          >
            <Icon :name="item.icon" class="w-5 h-5 mr-3" />
            {{ item.name }}
            <span
              v-if="item.badge"
              class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200"
            >
              {{ item.badge }}
            </span>
          </NuxtLink>
        </nav>

        <!-- User menu -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium">
              {{ userInitials }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ auth.user?.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ auth.user?.role }}
              </p>
            </div>
            <button
              @click="toggleUserMenu"
              class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
              <Icon name="heroicons:chevron-down" class="w-5 h-5" />
            </button>
          </div>

          <!-- User dropdown -->
          <div
            v-if="userMenuOpen"
            class="absolute bottom-full left-4 right-4 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
          >
            <div class="py-1">
              <NuxtLink
                to="/profile"
                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="closeUserMenu"
              >
                <Icon name="heroicons:user" class="w-4 h-4 mr-2 inline" />
                Profile
              </NuxtLink>
              <NuxtLink
                to="/settings"
                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="closeUserMenu"
              >
                <Icon name="heroicons:cog-6-tooth" class="w-4 h-4 mr-2 inline" />
                Settings
              </NuxtLink>
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Icon name="heroicons:arrow-right-on-rectangle" class="w-4 h-4 mr-2 inline" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
      <!-- Top navigation -->
      <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <!-- Left side -->
            <div class="flex items-center">
              <!-- Mobile menu button -->
              <button
                @click="toggleSidebar"
                class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 lg:hidden"
              >
                <Icon :name="sidebarOpen ? 'heroicons:x-mark-20-solid' : 'heroicons:bars-3'" class="w-6 h-6" />
              </button>

              <!-- Search -->
              <div class="hidden md:block ml-4 flex-1 md:ml-6">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Icon name="heroicons:magnifying-glass" class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    type="text"
                    placeholder="Search..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
              </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
              <!-- Notifications -->
              <button class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 relative">
                <Icon name="heroicons:bell" class="w-6 h-6" />
                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-error-400" />
              </button>

              <!-- Dark mode toggle -->
              <button
                @click="toggleDarkMode"
                class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
              >
                <Icon :name="isDarkMode ? 'heroicons:sun' : 'heroicons:moon'" class="w-6 h-6" />
              </button>

              <!-- Help -->
              <button class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <Icon name="heroicons:question-mark-circle" class="w-6 h-6" />
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1">
        <div class="py-6">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page header -->
            <div v-if="$slots.header" class="mb-6">
              <slot name="header" />
            </div>

            <!-- Page content -->
            <slot />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const auth = useAuth()
const route = useRoute()

// Reactive state
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)
const isDarkMode = ref(false)

// Computed properties
const isMobile = computed(() => {
  if (process.client) {
    return window.innerWidth < 1024
  }
  return false
})

const userInitials = computed(() => {
  if (auth.user?.name) {
    return auth.user.name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2)
  }
  return 'U'
})

// Navigation items based on user role
const navigation = computed(() => {
  const baseItems = [
    { name: 'Dashboard', href: '/dashboard', icon: 'heroicons:home' },
    { name: 'Profile', href: '/profile', icon: 'heroicons:user' }
  ]

  if (auth.isAdmin) {
    baseItems.push(
      { name: 'Users', href: '/users', icon: 'heroicons:users', badge: 'Admin' },
      { name: 'Employees', href: '/employees', icon: 'heroicons:briefcase' },
      { name: 'Departments', href: '/departments', icon: 'heroicons:building-office' },
      { name: 'Designations', href: '/designations', icon: 'heroicons:academic-cap' }
    )
  }

  if (auth.isHR || auth.isManager) {
    baseItems.push(
      { name: 'Employees', href: '/employees', icon: 'heroicons:briefcase' },
      { name: 'Attendance', href: '/attendance', icon: 'heroicons:clock' },
      { name: 'Leave', href: '/leave', icon: 'heroicons:calendar-days' }
    )
  }

  if (auth.hasPermission('payroll:read')) {
    baseItems.push(
      { name: 'Payroll', href: '/payroll', icon: 'heroicons:banknotes' },
      { name: 'Reports', href: '/reports', icon: 'heroicons:chart-bar' }
    )
  }

  return baseItems
})

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const closeUserMenu = () => {
  userMenuOpen.value = false
}

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const handleLogout = async () => {
  closeUserMenu()
  await auth.logout()
  await navigateTo('/login')
}

// Initialize dark mode
onMounted(() => {
  if (process.client) {
    const savedTheme = localStorage.getItem('theme')
    isDarkMode.value = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)
  }
})

// Close dropdowns when clicking outside
onClickOutside(userMenuOpen, closeUserMenu)

// Watch route changes to close mobile sidebar
watch(() => route.path, () => {
  if (isMobile.value) {
    closeSidebar()
  }
})
</script>