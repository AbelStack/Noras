<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Campus Management</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage academic campuses and locations</p>
          </div>
          <button 
            @click="openAddModal"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-primary-600 text-white text-xs sm:text-sm font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Campus
          </button>
        </div>
      </div>

      <!-- Loading State (Skeleton Cards) -->
      <div v-if="loading" class="py-2">
        <SkeletonLoader type="cards" :count="3" columnsClass="grid-cols-1 md:grid-cols-2 lg:grid-cols-3" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ error }}</p>
        </div>
        <button @click="fetchUniversities" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Campus List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="universities.length === 0" class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center shadow-sm min-h-[440px] flex flex-col items-center justify-center">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No campuses added yet</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">Create academic campus locations to organize faculties and academic departments.</p>
          <button 
            @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add your first campus
          </button>
        </div>

        <!-- Campus Grid (Large Box Flow) -->
        <div v-else class="min-h-[480px]">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="university in universities"
            :key="university.id"
            class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
          >
            <div>
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                  <h3 class="text-lg font-bold text-gray-900 truncate" :title="university.name">{{ university.name }}</h3>
                  <div class="inline-flex items-center mt-1">
                    <span class="text-xs font-mono font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-700">
                      Code: {{ university.code }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <span 
                    class="px-2.5 py-1 text-xs font-semibold rounded-full" 
                    :class="university.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                  >
                    {{ university.is_active ? 'Active' : 'Inactive' }}
                  </span>

                  <!-- 3-Dots Action Menu -->
                  <div class="relative inline-block text-left" @click.stop>
                    <button 
                      type="button"
                      @click.stop="toggleActionMenu(university.id)" 
                      class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 cursor-pointer"
                      title="Actions"
                    >
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                      </svg>
                    </button>

                    <!-- 3-Dots Dropdown Menu -->
                    <div 
                      v-if="activeActionMenuId === university.id"
                      @click.stop
                      class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-30 divide-y divide-gray-100 overflow-hidden"
                    >
                      <div class="py-1">
                        <button 
                          type="button"
                          @click="openEditModal(university); activeActionMenuId = null" 
                          class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors text-left cursor-pointer"
                        >
                          <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                          <span>Edit Campus</span>
                        </button>
                      </div>
                      <div class="py-1">
                        <button 
                          type="button"
                          @click="deleteUniversity(university); activeActionMenuId = null" 
                          class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer"
                        >
                          <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          <span>Delete Campus</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Campus Stats -->
              <div class="pt-3 border-t border-gray-100 mt-4 space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-500">
                  <span>Departments</span>
                  <span class="font-semibold text-gray-900">{{ university.departments_count ?? university.departments?.length ?? 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Campus Modal (Add / Edit) -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div 
            class="relative transform rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100"
            @click.stop
          >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900">
                  {{ isEditing ? 'Edit Campus' : 'Add New Campus' }}
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                  Configure university campus location and unique identification code.
                </p>
              </div>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveCampus" class="p-6 sm:p-8 space-y-5">
              <!-- Error banner if any -->
              <div v-if="formError" class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                {{ formError }}
              </div>

              <!-- 2-Column Responsive Form Fields -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                <!-- Campus Name -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Campus Name <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model.trim="form.name"
                    type="text" 
                    required
                    placeholder="e.g. Dessie Main Campus"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                  />
                </div>

                <!-- Campus Code -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Campus Code <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model.trim="form.code"
                    type="text" 
                    required
                    maxlength="20"
                    placeholder="e.g. DMC or KIoT"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all uppercase"
                  />
                  <p class="text-xs text-gray-400 mt-1">Unique short identifier, max 20 characters.</p>
                </div>
              </div>

              <!-- Active Status -->
              <div class="flex items-center gap-3 pt-2">
                <input 
                  id="campus-active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                />
                <label for="campus-active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">
                  Active (available for departments and student registration)
                </label>
              </div>

              <!-- Modal Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm"
                >
                  <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ saving ? 'Saving...' : (isEditing ? 'Update Campus' : 'Create Campus') }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const universities = ref([])
const loading = ref(true)
const error = ref(null)

const activeActionMenuId = ref(null)

const toggleActionMenu = (id) => {
  activeActionMenuId.value = activeActionMenuId.value === id ? null : id
}

const handleClickOutside = () => {
  activeActionMenuId.value = null
}

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  code: '',
  is_active: true,
})

const navigationItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  { path: '/admin/students', label: 'Student Approvals', icon: 'ApprovalIcon' },
  { path: '/admin/notifications', label: 'Notifications', icon: 'ReportsIcon' },
  { path: '/admin/campus', label: 'Campuses', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const fetchUniversities = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/universities')
    if (response.data.status === 'success') {
      universities.value = response.data.data.universities || []
    }
  } catch (err) {
    console.error('Failed to fetch campuses:', err)
    error.value = 'Failed to load campuses'
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    code: '',
    is_active: true,
  }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (campus) => {
  isEditing.value = true
  editingId.value = campus.id
  form.value = {
    name: campus.name,
    code: campus.code,
    is_active: Boolean(campus.is_active),
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
}

const saveCampus = async () => {
  if (!form.value.name.trim() || !form.value.code.trim()) {
    formError.value = 'Please provide both campus name and code.'
    return
  }

  saving.value = true
  formError.value = ''

  try {
    const payload = {
      name: form.value.name.trim(),
      code: form.value.code.trim().toUpperCase(),
      is_active: form.value.is_active,
    }

    if (isEditing.value) {
      await api.put(`/admin/universities/${editingId.value}`, payload)
      notify.success(`Campus "${payload.name}" updated successfully.`)
    } else {
      await api.post('/admin/universities', payload)
      notify.success(`Campus "${payload.name}" created successfully.`)
    }

    closeModal()
    await fetchUniversities()
  } catch (err) {
    console.error('Failed to save campus:', err)
    if (err.response?.data?.message) {
      formError.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      const firstError = Object.values(err.response.data.errors)[0]
      formError.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else {
      formError.value = 'An error occurred while saving the campus.'
    }
  } finally {
    saving.value = false
  }
}

const deleteUniversity = async (campus) => {
  const confirmed = await notify.confirm({
    title: `Delete Campus "${campus.name}"?`,
    message: 'Are you sure you want to delete this campus? Campuses with linked departments cannot be deleted.',
    confirmText: 'Delete Campus',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/universities/${campus.id}`)
    if (response.data.status === 'success') {
      notify.success('Campus deleted successfully.')
      await fetchUniversities()
    } else {
      notify.error(response.data.message || 'Failed to delete campus')
    }
  } catch (err) {
    console.error('Failed to delete campus:', err)
    const msg = err.response?.data?.message || 'Failed to delete campus'
    notify.error(msg)
  }
}

onMounted(() => {
  fetchUniversities()
  window.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
})
</script>
