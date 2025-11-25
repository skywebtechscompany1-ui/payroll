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
          <template v-for="item in navigation" :key="item.name">
            <!-- Regular menu item -->
            <NuxtLink
              v-if="!item.children"
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

            <!-- Dropdown menu item -->
            <div v-else>
              <button
                @click="toggleDropdown(item.name)"
                :class="[
                  'sidebar-item group w-full',
                  isDropdownOpen(item.name) || isChildActive(item) ? 'active' : ''
                ]"
              >
                <Icon :name="item.icon" class="w-5 h-5 mr-3" />
                {{ item.name }}
                <Icon
                  :name="isDropdownOpen(item.name) ? 'heroicons:chevron-down' : 'heroicons:chevron-right'"
                  class="w-4 h-4 ml-auto transition-transform"
                />
              </button>
              
              <!-- Submenu -->
              <div
                v-show="isDropdownOpen(item.name)"
                class="ml-8 mt-1 space-y-1"
              >
                <NuxtLink
                  v-for="child in item.children"
                  :key="child.name"
                  :to="child.href"
                  :class="[
                    'sidebar-item group text-sm',
                    $route.path === child.href ? 'active' : ''
                  ]"
                  @click="closeSidebar"
                >
                  <Icon :name="child.icon" class="w-4 h-4 mr-2" />
                  {{ child.name }}
                </NuxtLink>
              </div>
            </div>
          </template>
        </nav>

        <!-- User menu -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 relative">
          <button
            @click="toggleUserMenu"
            class="flex items-center space-x-3 w-full hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-2 transition-colors"
          >
            <!-- Profile Photo or Initials -->
            <div class="relative">
              <img
                v-if="auth.user?.profile_photo"
                :src="auth.user.profile_photo"
                :alt="auth.user?.name"
                class="w-10 h-10 rounded-full object-cover border-2 border-primary-500"
              />
              <div
                v-else
                class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-md"
              >
                {{ userInitials }}
              </div>
              <!-- Online indicator -->
              <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-success-400 ring-2 ring-white dark:ring-gray-800"></span>
            </div>
            
            <div class="flex-1 min-w-0 text-left">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                {{ auth.user?.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ auth.user?.email || auth.user?.role }}
              </p>
            </div>
            
            <Icon
              :name="userMenuOpen ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
              class="w-5 h-5 text-gray-400 transition-transform"
            />
          </button>

          <!-- User dropdown -->
          <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="userMenuOpen"
              class="absolute bottom-full left-4 right-4 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
            >
              <div class="py-1">
                <NuxtLink
                  to="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  @click="closeUserMenu"
                >
                  <Icon name="heroicons:user" class="w-4 h-4 mr-2 inline" />
                  Profile
                </NuxtLink>
                <NuxtLink
                  to="/settings"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  @click="closeUserMenu"
                >
                  <Icon name="heroicons:cog-6-tooth" class="w-4 h-4 mr-2 inline" />
                  Settings
                </NuxtLink>
                <div class="border-t border-gray-200 dark:border-gray-700"></div>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <Icon name="heroicons:arrow-right-on-rectangle" class="w-4 h-4 mr-2 inline" />
                  Logout
                </button>
              </div>
            </div>
          </Transition>
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
              <NotificationCenter />

              <!-- Dark mode toggle -->
              <button
                @click="toggleDarkMode"
                class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
              >
                <Icon :name="isDarkMode ? 'heroicons:sun' : 'heroicons:moon'" class="w-6 h-6" />
              </button>

              <!-- Help -->
              <a href="https://jacksonalex.co.ke" target="_blank" rel="noopener noreferrer" class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300" title="Developer Portfolio">
                <Icon name="heroicons:question-mark-circle" class="w-6 h-6" />
              </a>
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

      <!-- Footer -->
      <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <p class="text-center text-sm text-gray-600 dark:text-gray-400">
            Made by <span class="font-semibold text-primary-600">Jafasol Systems</span> | 
            Copyright 2014-2051 PAYROLL. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
const auth = useAuth()
const route = useRoute()

// Reactive state
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const userMenuOpen = ref(false)
const isDarkMode = ref(false)
const openDropdowns = ref<string[]>(['Leave Management', 'User Management', 'Payroll', 'Settings'])

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
    { name: 'Dashboard', href: '/dashboard', icon: 'heroicons:home' }
  ]

  if (auth.isAdmin) {
    baseItems.push(
      { name: 'Employees', href: '/employees', icon: 'heroicons:briefcase' },
      { name: 'Departments', href: '/departments', icon: 'heroicons:building-office' },
      { name: 'Designations', href: '/designations', icon: 'heroicons:academic-cap' }
    )
  }

  if (auth.isHR || auth.isManager) {
    if (!auth.isAdmin) {
      baseItems.push(
        { name: 'Employees', href: '/employees', icon: 'heroicons:briefcase' }
      )
    }
    baseItems.push(
      { name: 'Attendance', href: '/attendance', icon: 'heroicons:clock' }
    )
  }

  // Leave Management dropdown
  baseItems.push({
    name: 'Leave Management',
    icon: 'heroicons:calendar-days',
    children: [
      { name: 'Create Leave', href: '/leave-apply', icon: 'heroicons:plus-circle' },
      { name: 'Leaves', href: '/leaves', icon: 'heroicons:clipboard-document-list' },
      { name: 'Leave Config', href: '/leave-settings', icon: 'heroicons:cog-6-tooth' }
    ]
  })

  // User Management dropdown
  if (auth.isAdmin || auth.isHR) {
    baseItems.push({
      name: 'User Management',
      icon: 'heroicons:user-group',
      children: [
        { name: 'Users', href: '/users-new', icon: 'heroicons:users' },
        { name: 'Roles', href: '/roles', icon: 'heroicons:shield-check' }
      ]
    })
  }

  // Payroll dropdown
  baseItems.push({
    name: 'Payroll',
    icon: 'heroicons:banknotes',
    children: [
      { name: 'Overview', href: '/payroll', icon: 'heroicons:chart-pie' },
      { name: 'Manage Salary', href: '/manage-salary', icon: 'heroicons:currency-dollar' },
      { name: 'Payments', href: '/payments', icon: 'heroicons:credit-card' },
      { name: 'Payslips', href: '/payslips', icon: 'heroicons:document-text' },
      { name: 'Reports', href: '/reports-new', icon: 'heroicons:chart-bar' }
    ]
  })

  // Settings dropdown
  if (auth.isAdmin || auth.isHR) {
    baseItems.push({
      name: 'Settings',
      icon: 'heroicons:cog-6-tooth',
      children: [
        { name: 'System Settings', href: '/system-settings', icon: 'heroicons:cog-8-tooth' },
        { name: 'Activity Logs', href: '/activity-logs', icon: 'heroicons:clipboard-document-list' }
      ]
    })
  }

  return baseItems
})

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString())
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleDropdown = (name: string) => {
  const index = openDropdowns.value.indexOf(name)
  if (index > -1) {
    openDropdowns.value.splice(index, 1)
  } else {
    openDropdowns.value.push(name)
  }
}

const isDropdownOpen = (name: string) => {
  return openDropdowns.value.includes(name)
}

const isChildActive = (item: any) => {
  if (!item.children) return false
  return item.children.some((child: any) => route.path === child.href)
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

// Initialize dark mode and sidebar state
onMounted(() => {
  if (process.client) {
    const savedTheme = localStorage.getItem('theme')
    isDarkMode.value = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)
    
    const savedCollapsed = localStorage.getItem('sidebarCollapsed')
    sidebarCollapsed.value = savedCollapsed === 'true'
  }
})

// Watch route changes to close mobile sidebar
watch(() => route.path, () => {
  if (isMobile.value) {
    closeSidebar()
  }
})
</script>