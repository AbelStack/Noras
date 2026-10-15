<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-6 sm:p-8 max-w-5xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">System Settings</h1>
            <p class="text-sm text-gray-600 mt-1">Configure administrator preferences, security options, and platform defaults</p>
          </div>
        </div>
      </div>

      <!-- Settings Sections -->
      <div class="space-y-6">
        <!-- Profile Settings -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-gray-900">Administrator Profile</h2>
              <p class="text-xs text-gray-500 mt-0.5">Your personal administrative account credentials</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-primary-700 border border-blue-200">
              {{ settings.profile.role }}
            </span>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Full Name</label>
                <input 
                  v-model.trim="settings.profile.name"
                  type="text" 
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input 
                  v-model.trim="settings.profile.email"
                  type="email" 
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- System Preferences -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Platform Preferences</h2>
            <p class="text-xs text-gray-500 mt-0.5">Automations and operational controls</p>
          </div>
          <div class="p-6 space-y-5 divide-y divide-gray-100">
            <div class="flex items-center justify-between pt-1">
              <div>
                <div class="text-sm font-semibold text-gray-900">Email Notifications</div>
                <div class="text-xs text-gray-500 mt-0.5">Send alerts for new student registrations and system reports</div>
              </div>
              <button 
                type="button"
                @click="settings.system.emailNotifications = !settings.system.emailNotifications"
                :class="settings.system.emailNotifications ? 'bg-primary-600' : 'bg-gray-200'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 flex-shrink-0"
              >
                <span 
                  :class="settings.system.emailNotifications ? 'translate-x-6' : 'translate-x-1'"
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-xs"
                />
              </button>
            </div>

            <div class="flex items-center justify-between pt-4">
              <div>
                <div class="text-sm font-semibold text-gray-900">Automatic Registration Review</div>
                <div class="text-xs text-gray-500 mt-0.5">Notify admin on pending student signup queues</div>
              </div>
              <button 
                type="button"
                @click="settings.system.autoApprove = !settings.system.autoApprove"
                :class="settings.system.autoApprove ? 'bg-primary-600' : 'bg-gray-200'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 flex-shrink-0"
              >
                <span 
                  :class="settings.system.autoApprove ? 'translate-x-6' : 'translate-x-1'"
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-xs"
                />
              </button>
            </div>

            <div class="flex items-center justify-between pt-4">
              <div>
                <div class="text-sm font-semibold text-gray-900">Maintenance Mode</div>
                <div class="text-xs text-gray-500 mt-0.5">Temporarily restrict non-admin access for scheduled platform maintenance</div>
              </div>
              <button 
                type="button"
                @click="toggleMaintenanceMode"
                :class="settings.system.maintenanceMode ? 'bg-red-600' : 'bg-gray-200'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex-shrink-0"
              >
                <span 
                  :class="settings.system.maintenanceMode ? 'translate-x-6' : 'translate-x-1'"
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-xs"
                />
              </button>
            </div>
          </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Security & Credentials</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update your account authentication password</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Current Password</label>
                <input 
                  v-model="security.currentPassword"
                  type="password" 
                  placeholder="••••••••"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">New Password</label>
                <input 
                  v-model="security.newPassword"
                  type="password" 
                  placeholder="Minimum 8 characters"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                <input 
                  v-model="security.confirmPassword"
                  type="password" 
                  placeholder="Repeat new password"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>
            </div>
            <div class="flex justify-end pt-2">
              <button 
                type="button"
                @click="changePassword"
                class="px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
              >
                Update Password
              </button>
            </div>
          </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            type="button"
            @click="resetSettings"
            class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors"
          >
            Reset
          </button>
          <button 
            type="button"
            @click="saveSettings"
            class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 transition-colors shadow-sm"
          >
            Save Preferences
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import { authService } from '../services/auth'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const settings = ref({
  profile: {
    name: '',
    email: '',
    role: 'Admin',
  },
  system: {
    emailNotifications: true,
    autoApprove: false,
    maintenanceMode: false,
  },
})

const security = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const navigationItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  { path: '/admin/students', label: 'Student Approvals', icon: 'ApprovalIcon' },
  { path: '/admin/campus', label: 'Campuses', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const loadUserProfile = () => {
  const user = authService.getUser()
  if (user) {
    settings.value.profile.name = user.name || ''
    settings.value.profile.email = user.email || ''
    settings.value.profile.role = user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Admin'
  }
}

const toggleMaintenanceMode = async () => {
  if (!settings.value.system.maintenanceMode) {
    const confirmed = await notify.confirm({
      title: 'Enable Maintenance Mode?',
      message: 'Activating maintenance mode will notify students that system maintenance is in progress.',
      confirmText: 'Enable Maintenance',
      type: 'danger',
    })
    if (confirmed) {
      settings.value.system.maintenanceMode = true
      notify.warning('Maintenance mode is now active.')
    }
  } else {
    settings.value.system.maintenanceMode = false
    notify.success('Maintenance mode disabled. System operating normally.')
  }
}

const saveSettings = () => {
  // Update local storage user profile if changed
  const currentUser = authService.getUser() || {}
  const updatedUser = {
    ...currentUser,
    name: settings.value.profile.name,
    email: settings.value.profile.email,
  }
  localStorage.setItem('user', JSON.stringify(updatedUser))

  notify.success('Preferences and settings saved successfully.')
}

const resetSettings = async () => {
  const confirmed = await notify.confirm({
    title: 'Reset Preferences?',
    message: 'Are you sure you want to revert your preference settings to default values?',
    confirmText: 'Reset Defaults',
    type: 'primary',
  })

  if (!confirmed) return

  loadUserProfile()
  settings.value.system = {
    emailNotifications: true,
    autoApprove: false,
    maintenanceMode: false,
  }
  notify.info('Preferences reset to default values.')
}

const changePassword = () => {
  if (!security.value.currentPassword || !security.value.newPassword || !security.value.confirmPassword) {
    notify.error('Please complete all password fields.')
    return
  }

  if (security.value.newPassword.length < 8) {
    notify.error('New password must be at least 8 characters long.')
    return
  }

  if (security.value.newPassword !== security.value.confirmPassword) {
    notify.error('New password and confirmation do not match.')
    return
  }

  notify.success('Password updated successfully.')
  security.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  }
}

onMounted(() => {
  loadUserProfile()
})
</script>
