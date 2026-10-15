<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <!-- Mobile Backdrop Overlay -->
    <div 
      v-if="mobileMenuOpen" 
      @click="mobileMenuOpen = false" 
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden transition-opacity"
      aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="[
        'bg-white border-r border-gray-200 transition-all duration-300 flex flex-col z-50',
        'fixed inset-y-0 left-0 lg:static lg:translate-x-0',
        mobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
        'w-64'
      ]"
    >
      <!-- Logo & Toggle Button -->
      <div class="h-16 flex items-center border-b border-gray-200 px-3 justify-between">
        <!-- Expanded state on desktop OR on mobile: Logo on left, collapse/close button on right -->
        <template v-if="!sidebarCollapsed || isMobileView">
          <router-link to="/" class="flex items-center min-w-0" title="Noras Home" @click="mobileMenuOpen = false">
            <NorasLogo imageClass="h-8 w-auto" textClass="text-xl text-gray-900" dividerClass="h-6" />
          </router-link>
          <button 
            type="button"
            @click="handleSidebarCloseOrCollapse"
            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0 ml-1"
            :title="isMobileView ? 'Close navigation' : 'Collapse sidebar'"
          >
            <!-- Close icon on mobile, left arrows on desktop -->
            <svg v-if="isMobileView" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>
        </template>

        <!-- Collapsed state on desktop: Centered logo and clear expand button -->
        <template v-else>
          <div class="w-full flex items-center justify-between px-0.5">
            <router-link to="/" class="flex items-center justify-center p-1" title="Noras Home">
              <img src="/Noras logo-sm.png" alt="Noras" class="h-7 w-auto object-contain" />
            </router-link>
            <button 
              type="button"
              @click="toggleSidebarCollapse(false)"
              class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-blue-50 rounded-lg transition-colors flex-shrink-0"
              title="Expand sidebar"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </template>
      </div>

      <!-- MENU Label -->
      <div class="px-4 py-3">
        <p class="text-xs text-gray-500 font-medium" v-if="!sidebarCollapsed || isMobileView">MENU</p>
      </div>

      <!-- Navigation Items -->
      <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          v-slot="{ isActive }"
          custom
        >
          <a
            :href="item.path"
            @click.prevent="navigateAndCloseMenu(item.path)"
            :class="[
              'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors group',
              isActive 
                ? 'bg-blue-50 text-primary-500' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <component 
              :is="iconComponents[item.icon]" 
              :class="[
                'flex-shrink-0',
                sidebarCollapsed && !isMobileView ? 'w-5 h-5' : 'w-5 h-5 mr-3'
              ]"
            />
            <span v-if="!sidebarCollapsed || isMobileView" class="flex-1">{{ item.label }}</span>
            <svg 
              v-if="(!sidebarCollapsed || isMobileView) && item.children" 
              :class="[
                'w-4 h-4 transition-transform',
                item.expanded ? 'transform rotate-180' : ''
              ]"
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </a>
        </router-link>
      </nav>

      <!-- Logout Button at Bottom -->
      <div class="p-3 border-t border-gray-200">
        <button
          @click="handleLogout"
          class="w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
        >
          <svg class="w-5 h-5" :class="sidebarCollapsed && !isMobileView ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span v-if="!sidebarCollapsed || isMobileView">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Navigation Bar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <button 
            type="button"
            @click="handleMenuToggle"
            class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            :title="isMobileView ? 'Open navigation' : (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar')"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <h1 class="text-lg sm:text-xl font-bold text-gray-900">
            {{ pageTitle === 'Admin' ? 'Noras Admin' : (pageTitle === 'Student' ? 'Noras Student' : pageTitle) }}
          </h1>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Notification Bell -->
          <div class="relative">
            <button 
              @click="showNotifications = !showNotifications"
              class="p-2 rounded-full hover:bg-gray-100 relative"
            >
              <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span v-if="notificationCount > 0" class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            
            <!-- Notification Dropdown -->
            <div 
              v-if="showNotifications"
              v-click-outside="() => showNotifications = false"
              class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
            >
              <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Notifications</h3>
              </div>
              <div class="max-h-96 overflow-y-auto">
                <div v-if="notificationCount === 0" class="p-4 text-center text-gray-500 text-sm">
                  No new notifications
                </div>
                <div v-else class="divide-y divide-gray-100">
                  <div v-for="notif in notifications" :key="notif.id" class="p-4 hover:bg-gray-50 cursor-pointer">
                    <p class="text-sm text-gray-900">{{ notif.message }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ notif.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- User Profile Dropdown -->
          <div class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-3 hover:bg-gray-50 rounded-lg px-3 py-2"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-medium text-sm">
                {{ userInitials }}
              </div>
              <div class="hidden md:block text-sm">
                <p class="font-medium text-gray-900">{{ userName }}</p>
              </div>
              <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- User Dropdown Menu -->
            <div 
              v-if="showUserMenu"
              v-click-outside="() => showUserMenu = false"
              class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
            >
              <div class="p-3 border-b border-gray-200">
                <p class="font-medium text-gray-900">{{ userName }}</p>
                <p class="text-sm text-gray-500">{{ user?.email }}</p>
              </div>
              <div class="py-2">
                <router-link
                  v-if="user?.role === 'admin'"
                  to="/admin/settings"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false"
                >
                  Settings
                </router-link>
                <button
                  @click="handleLogout"
                  class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50">
        <slot></slot>
      </main>
    </div>

    <!-- Centralized Toast & Confirmation Modals -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authService } from '../services/auth'
import NorasLogo from './NorasLogo.vue'
import NotificationContainer from './NotificationContainer.vue'

// Responsive viewport detection
const isMobileView = ref(typeof window !== 'undefined' ? window.innerWidth < 1024 : false)

const checkMobile = () => {
  if (typeof window !== 'undefined') {
    isMobileView.value = window.innerWidth < 1024
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

// Mobile menu and sidebar collapse state
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(
  typeof localStorage !== 'undefined' 
    ? localStorage.getItem('noras_sidebar_collapsed') === 'true' 
    : false
)

const toggleSidebarCollapse = (val) => {
  sidebarCollapsed.value = val !== undefined ? val : !sidebarCollapsed.value
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('noras_sidebar_collapsed', sidebarCollapsed.value)
  }
}

const handleSidebarCloseOrCollapse = () => {
  if (isMobileView.value) {
    mobileMenuOpen.value = false
  } else {
    toggleSidebarCollapse()
  }
}

const handleMenuToggle = () => {
  if (isMobileView.value) {
    mobileMenuOpen.value = !mobileMenuOpen.value
  } else {
    toggleSidebarCollapse()
  }
}

const navigateAndCloseMenu = (path) => {
  mobileMenuOpen.value = false
  router.push(path)
}

// Automatically close mobile menu whenever route changes
const route = useRoute()
const router = useRouter()

watch(() => route.path, () => {
  mobileMenuOpen.value = false
})

// Create icon components using h() render function
const DashboardIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
])

const ApprovalIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
])

const SalesIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' })
])

const ProductsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })
])

const InventoryIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' })
])

const ReportsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
])

const SettingsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
])

const BookIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
])

const UsersIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
])

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  },
  navigationItems: {
    type: Array,
    default: () => []
  }
})

const showNotifications = ref(false)
const showUserMenu = ref(false)
const notifications = ref([])
const notificationCount = computed(() => notifications.value.length)

const user = computed(() => authService.getUser())
const userName = computed(() => user.value?.name || 'User')
const userInitials = computed(() => {
  const name = userName.value
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

const handleLogout = async () => {
  try {
    await authService.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
    router.push('/login')
  }
}

const iconComponents = {
  DashboardIcon,
  ApprovalIcon,
  SalesIcon,
  ProductsIcon,
  InventoryIcon,
  ReportsIcon,
  SettingsIcon,
  BookIcon,
  UsersIcon,
}
</script>
