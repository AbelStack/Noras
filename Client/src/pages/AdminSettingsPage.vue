<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-5xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">System Settings</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Configure administrator preferences, security options, and platform defaults</p>
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
              <p class="text-xs text-gray-500 mt-0.5">Your personal administrative account credentials and profile picture</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-primary-700 border border-blue-200">
              {{ settings.profile.role }}
            </span>
          </div>
          <div class="p-6 space-y-6">
            <!-- Profile Photo Section -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-5 p-4 rounded-xl bg-gray-50/70 border border-gray-100">
              <!-- Avatar Display with Camera Overlay Badge -->
              <div class="relative flex-shrink-0 self-center sm:self-auto">
                <img 
                  v-if="avatarPreview || currentUser?.avatar_url" 
                  :src="avatarPreview || currentUser.avatar_url" 
                  :alt="settings.profile.name" 
                  class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-md"
                />
                <div 
                  v-else 
                  class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-2xl shadow-md ring-4 ring-white"
                >
                  {{ userInitials }}
                </div>
                <button
                  type="button"
                  @click="triggerFileInput"
                  class="absolute -bottom-1 -right-1 w-7 h-7 bg-white rounded-full shadow-md border border-gray-200 flex items-center justify-center text-gray-600 hover:text-primary-600 hover:border-primary-300 transition-colors cursor-pointer"
                  title="Upload profile photo"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </div>

              <!-- Upload Actions & Details -->
              <div class="flex-1 min-w-0 space-y-2">
                <div>
                  <h3 class="text-sm font-bold text-gray-900">Profile Photo</h3>
                  <p class="text-xs text-gray-500 mt-0.5">
                    JPG, PNG, WEBP, or GIF. Maximum photo size is 2MB.
                  </p>
                </div>

                <!-- Hidden file input -->
                <input 
                  ref="fileInputRef"
                  type="file" 
                  accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" 
                  class="hidden" 
                  @change="handleFileChange"
                />

                <div class="flex flex-wrap items-center gap-2 pt-1">
                  <!-- Choose / Upload button -->
                  <button
                    type="button"
                    @click="triggerFileInput"
                    :disabled="isUploadingPhoto"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
                  >
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ selectedFile ? 'Change Selection' : 'Upload New Photo' }}</span>
                  </button>

                  <!-- Confirm Upload button (if file selected) -->
                  <button
                    v-if="selectedFile"
                    type="button"
                    @click="saveProfilePhoto"
                    :disabled="isUploadingPhoto"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
                  >
                    <svg v-if="isUploadingPhoto" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ isUploadingPhoto ? 'Saving...' : 'Save Photo' }}</span>
                  </button>

                  <!-- Cancel selection button -->
                  <button
                    v-if="selectedFile"
                    type="button"
                    @click="cancelPhotoSelection"
                    class="px-2.5 py-1.5 text-xs text-gray-500 hover:text-gray-700 transition-colors cursor-pointer"
                  >
                    Cancel
                  </button>

                  <!-- Remove photo button if user currently has one -->
                  <button
                    v-if="currentUser?.avatar_url && !selectedFile"
                    type="button"
                    @click="removeProfilePhoto"
                    :disabled="isUploadingPhoto"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors cursor-pointer disabled:opacity-50"
                  >
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Remove Photo</span>
                  </button>
                </div>

                <!-- Error notice if any -->
                <div v-if="photoError" class="p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700 flex items-center gap-2">
                  <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>{{ photoError }}</span>
                </div>
              </div>
            </div>

            <!-- Profile Info Fields -->
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
                <div class="relative">
                  <input 
                    v-model="security.currentPassword"
                    :type="showCurrentPassword ? 'text' : 'password'" 
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                  />
                  <button
                    type="button"
                    tabindex="-1"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                    aria-label="Toggle current password visibility"
                  >
                    <Eye v-if="!showCurrentPassword" :size="18" />
                    <EyeOff v-else :size="18" />
                  </button>
                </div>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">New Password</label>
                <div class="relative">
                  <input 
                    v-model="security.newPassword"
                    :type="showNewPassword ? 'text' : 'password'" 
                    placeholder="Minimum 8 characters (letters, numbers, symbols)"
                    class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                  />
                  <button
                    type="button"
                    tabindex="-1"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                    aria-label="Toggle new password visibility"
                  >
                    <Eye v-if="!showNewPassword" :size="18" />
                    <EyeOff v-else :size="18" />
                  </button>
                </div>
                <PasswordRequirements :password="security.newPassword" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                <div class="relative">
                  <input 
                    v-model="security.confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'" 
                    placeholder="Repeat new password"
                    class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                  />
                  <button
                    type="button"
                    tabindex="-1"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                    aria-label="Toggle confirm password visibility"
                  >
                    <Eye v-if="!showConfirmPassword" :size="18" />
                    <EyeOff v-else :size="18" />
                  </button>
                </div>
              </div>
            </div>
            <div class="flex justify-end pt-2">
              <button 
                type="button"
                @click="changePassword"
                :disabled="isUpdatingPassword"
                class="px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm disabled:opacity-50 inline-flex items-center gap-1.5"
              >
                <svg v-if="isUpdatingPassword" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            type="button"
            @click="resetSettings"
            :disabled="isSavingSettings"
            class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-50"
          >
            Reset
          </button>
          <button 
            type="button"
            @click="saveSettings"
            :disabled="isSavingSettings"
            class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 transition-colors shadow-sm disabled:opacity-50 inline-flex items-center gap-2"
          >
            <svg v-if="isSavingSettings" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isSavingSettings ? 'Saving...' : 'Save Preferences' }}</span>
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'
import DashboardLayout from '../components/DashboardLayout.vue'
import { authService } from '../services/auth'
import { userService } from '../services/userService'
import { useNotification } from '../composables/useNotification'
import { adminNavigationItems } from '../config/navigation'
import PasswordRequirements from '../components/PasswordRequirements.vue'

const notify = useNotification()

const currentUser = ref(authService.getUser())
const fileInputRef = ref(null)
const selectedFile = ref(null)
const avatarPreview = ref(null)
const isUploadingPhoto = ref(false)
const photoError = ref('')
const isSavingSettings = ref(false)
const isUpdatingPassword = ref(false)
const isLoadingProfile = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const navigationItems = adminNavigationItems

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

const userInitials = computed(() => {
  const name = settings.value.profile.name || currentUser.value?.name || 'Admin'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const handleFileChange = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Strict 2MB check
  const MAX_SIZE = 2 * 1024 * 1024
  if (file.size > MAX_SIZE) {
    const sizeInMb = (file.size / (1024 * 1024)).toFixed(2)
    photoError.value = `File size is ${sizeInMb}MB, which exceeds the 2MB limit. Please choose a smaller photo.`
    selectedFile.value = null
    return
  }

  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif']
  if (!validTypes.includes(file.type)) {
    photoError.value = 'Invalid file type. Supported formats are JPG, PNG, WEBP, or GIF.'
    selectedFile.value = null
    return
  }

  photoError.value = ''
  selectedFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
}

const cancelPhotoSelection = () => {
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
  selectedFile.value = null
  avatarPreview.value = null
  photoError.value = ''
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const saveProfilePhoto = async () => {
  if (!selectedFile.value) return
  isUploadingPhoto.value = true
  photoError.value = ''
  try {
    const updatedUser = await userService.uploadProfilePicture(selectedFile.value)
    currentUser.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
    window.dispatchEvent(new Event('user-profile-updated'))
    notify.success('Profile photo updated successfully!')
    cancelPhotoSelection()
  } catch (err) {
    photoError.value = err.response?.data?.message || 'Failed to upload photo. Please ensure it is under 2MB.'
  } finally {
    isUploadingPhoto.value = false
  }
}

const removeProfilePhoto = async () => {
  const confirmed = await notify.confirm({
    title: 'Remove Profile Picture',
    message: 'Are you sure you want to remove your profile picture?',
    confirmText: 'Remove Photo',
    type: 'danger'
  })
  if (!confirmed) return

  isUploadingPhoto.value = true
  photoError.value = ''
  try {
    const updatedUser = await userService.removeProfilePicture()
    currentUser.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
    window.dispatchEvent(new Event('user-profile-updated'))
    notify.success('Profile photo removed.')
  } catch (err) {
    notify.error('Failed to remove profile photo.')
  } finally {
    isUploadingPhoto.value = false
  }
}

const security = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const loadUserProfile = async () => {
  // 1. Initialise quickly from local storage if present
  const cached = authService.getUser()
  if (cached) {
    currentUser.value = cached
    settings.value.profile.name = cached.name || ''
    settings.value.profile.email = cached.email || ''
    settings.value.profile.role = cached.role ? (cached.role.charAt(0).toUpperCase() + cached.role.slice(1)) : 'Admin'
    if (cached.preferences) {
      settings.value.system = {
        emailNotifications: cached.preferences.emailNotifications !== false,
        autoApprove: !!cached.preferences.autoApprove,
        maintenanceMode: !!cached.preferences.maintenanceMode,
      }
    }
  }

  // 2. Fetch fresh profile and preferences from server
  try {
    isLoadingProfile.value = true
    const user = await userService.getProfile()
    currentUser.value = user
    settings.value.profile.name = user.name || ''
    settings.value.profile.email = user.email || ''
    settings.value.profile.role = user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Admin'
    if (user.preferences) {
      settings.value.system = {
        emailNotifications: user.preferences.emailNotifications !== false,
        autoApprove: !!user.preferences.autoApprove,
        maintenanceMode: !!user.preferences.maintenanceMode,
      }
    }
    localStorage.setItem('user', JSON.stringify(user))
  } catch (err) {
    console.error('Failed to load profile from server:', err)
  } finally {
    isLoadingProfile.value = false
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
      notify.warning('Maintenance mode is now active. Remember to click "Save Preferences" to commit.')
    }
  } else {
    settings.value.system.maintenanceMode = false
    notify.success('Maintenance mode disabled. Remember to click "Save Preferences" to commit.')
  }
}

const saveSettings = async () => {
  if (!settings.value.profile.name || !settings.value.profile.email) {
    notify.error('Please provide both name and email.')
    return
  }

  isSavingSettings.value = true
  try {
    const updatedUser = await userService.updateProfile({
      name: settings.value.profile.name,
      email: settings.value.profile.email,
      preferences: {
        emailNotifications: settings.value.system.emailNotifications,
        autoApprove: settings.value.system.autoApprove,
        maintenanceMode: settings.value.system.maintenanceMode,
      },
    })

    currentUser.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
    window.dispatchEvent(new Event('user-profile-updated'))

    notify.success('Preferences and profile saved successfully.')
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save settings.'
    notify.error(msg)
  } finally {
    isSavingSettings.value = false
  }
}

const resetSettings = async () => {
  const confirmed = await notify.confirm({
    title: 'Reset Preferences?',
    message: 'Are you sure you want to revert your preference settings to default values?',
    confirmText: 'Reset Defaults',
    type: 'primary',
  })

  if (!confirmed) return

  settings.value.system = {
    emailNotifications: true,
    autoApprove: false,
    maintenanceMode: false,
  }

  await saveSettings()
  notify.info('Preferences reset to default values and saved.')
}

const changePassword = async () => {
  if (!security.value.currentPassword || !security.value.newPassword || !security.value.confirmPassword) {
    notify.error('Please complete all password fields.')
    return
  }

  if (security.value.newPassword.length < 8) {
    notify.error('New password must be at least 8 characters long.')
    return
  }

  if (!/[a-zA-Z]/.test(security.value.newPassword)) {
    notify.error('New password must contain at least one letter.')
    return
  }

  if (!/[0-9]/.test(security.value.newPassword)) {
    notify.error('New password must contain at least one number.')
    return
  }

  if (!/[^a-zA-Z0-9]/.test(security.value.newPassword)) {
    notify.error('New password must contain at least one symbol (e.g. !@#$%^&*).')
    return
  }

  if (security.value.newPassword !== security.value.confirmPassword) {
    notify.error('New password and confirmation do not match.')
    return
  }

  isUpdatingPassword.value = true
  try {
    await userService.updatePassword({
      current_password: security.value.currentPassword,
      new_password: security.value.newPassword,
      new_password_confirmation: security.value.confirmPassword,
    })

    notify.success('Password updated successfully.')
    security.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }
    showCurrentPassword.value = false
    showNewPassword.value = false
    showConfirmPassword.value = false
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.current_password?.[0] || 'Failed to update password.'
    notify.error(msg)
  } finally {
    isUpdatingPassword.value = false
  }
}

onMounted(() => {
  loadUserProfile()
  window.addEventListener('user-profile-updated', loadUserProfile)
})

onUnmounted(() => {
  window.removeEventListener('user-profile-updated', loadUserProfile)
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
})
</script>
