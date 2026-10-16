<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
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
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Departments Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage academic departments and faculty divisions</p>
          </div>
          <button 
            @click="openAddModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 active:bg-primary-800 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Department
          </button>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:w-80">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search departments or codes..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
          />
        </div>
        <div class="w-full sm:w-auto flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Filter Campus:</label>
          <select 
            v-model="filterCampus"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
          >
            <option value="">All Campuses</option>
            <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>

      <!-- Loading State (Skeleton Table) -->
      <div v-if="loading" class="py-2">
        <SkeletonLoader type="table" :rows="6" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ error }}</p>
        </div>
        <button @click="fetchDepartments" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Department List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredDepartments.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No departments found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            {{ searchQuery || filterCampus ? 'No departments match your current filter criteria.' : 'Create academic departments and assign them to campuses.' }}
          </p>
          <button 
            v-if="!searchQuery && !filterCampus"
            @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add your first department
          </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department Name</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Campus</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="dept in filteredDepartments" :key="dept.id" class="hover:bg-gray-50/80 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">{{ dept.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                      <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ dept.university?.name || 'N/A' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs font-mono font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-800">
                      {{ dept.code || 'N/A' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full"
                      :class="dept.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                    >
                      {{ dept.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button 
                      @click="openEditModal(dept)" 
                      class="text-xs font-medium text-primary-600 hover:text-primary-800 px-2.5 py-1 rounded-md hover:bg-primary-50 transition-colors mr-2"
                    >
                      Edit
                    </button>
                    <button 
                      @click="deleteDepartment(dept)" 
                      class="text-xs font-medium text-red-600 hover:text-red-800 px-2.5 py-1 rounded-md hover:bg-red-50 transition-colors"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Department Modal (Add / Edit) -->
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
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
            @click.stop
          >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <h3 class="text-lg font-bold text-gray-900">
                {{ isEditing ? 'Edit Department' : 'Add New Department' }}
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveDepartment" class="p-6 space-y-4">
              <!-- Error banner if any -->
              <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                {{ formError }}
              </div>

              <!-- Campus Selection -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Campus <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.university_id"
                  required
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all"
                >
                  <option :value="null" disabled>Select a Campus</option>
                  <option v-for="c in campuses" :key="c.id" :value="c.id">
                    {{ c.name }} ({{ c.code }})
                  </option>
                </select>
                <p v-if="campuses.length === 0" class="text-xs text-amber-600 mt-1">
                  No campuses found. Please create a campus first.
                </p>
              </div>

              <!-- Department Name -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Department Name <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="form.name"
                  type="text" 
                  required
                  placeholder="e.g. Computer Science"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>

              <!-- Department Code -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Department Code <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="form.code"
                  type="text" 
                  required
                  maxlength="20"
                  placeholder="e.g. CS"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all uppercase"
                />
                <p class="text-xs text-gray-400 mt-1">Unique short code within the selected campus.</p>
              </div>

              <!-- Active Status -->
              <div class="flex items-center gap-3 pt-2">
                <input 
                  id="dept-active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                />
                <label for="dept-active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">
                  Active (open for course offerings and students)
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
                  :disabled="saving || campuses.length === 0"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm"
                >
                  <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ saving ? 'Saving...' : (isEditing ? 'Update Department' : 'Create Department') }}</span>
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
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const departments = ref([])
const campuses = ref([])
const loading = ref(true)
const error = ref(null)

const searchQuery = ref('')
const filterCampus = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  university_id: null,
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

const filteredDepartments = computed(() => {
  return departments.value.filter(d => {
    const matchesSearch = !searchQuery.value ||
      d.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      d.code?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      d.university?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesCampus = !filterCampus.value || d.university_id === parseInt(filterCampus.value)

    return matchesSearch && matchesCampus
  })
})

const fetchCampuses = async () => {
  try {
    const response = await api.get('/admin/universities')
    if (response.data.status === 'success') {
      campuses.value = response.data.data.universities || []
    }
  } catch (err) {
    console.error('Failed to fetch campuses list:', err)
  }
}

const fetchDepartments = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/departments')
    if (response.data.status === 'success') {
      departments.value = response.data.data.departments || []
    }
  } catch (err) {
    console.error('Failed to fetch departments:', err)
    error.value = 'Failed to load departments'
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  editingId.value = null
  form.value = {
    university_id: campuses.value.length > 0 ? campuses.value[0].id : null,
    name: '',
    code: '',
    is_active: true,
  }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (dept) => {
  isEditing.value = true
  editingId.value = dept.id
  form.value = {
    university_id: dept.university_id,
    name: dept.name,
    code: dept.code,
    is_active: Boolean(dept.is_active),
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
}

const saveDepartment = async () => {
  if (!form.value.university_id) {
    formError.value = 'Please select a campus.'
    return
  }
  if (!form.value.name.trim() || !form.value.code.trim()) {
    formError.value = 'Please fill in both department name and code.'
    return
  }

  saving.value = true
  formError.value = ''

  try {
    const payload = {
      university_id: form.value.university_id,
      name: form.value.name.trim(),
      code: form.value.code.trim().toUpperCase(),
      is_active: form.value.is_active,
    }

    if (isEditing.value) {
      await api.put(`/admin/departments/${editingId.value}`, payload)
      notify.success(`Department "${payload.name}" updated successfully.`)
    } else {
      await api.post('/admin/departments', payload)
      notify.success(`Department "${payload.name}" created successfully.`)
    }

    closeModal()
    await fetchDepartments()
  } catch (err) {
    console.error('Failed to save department:', err)
    if (err.response?.data?.message) {
      formError.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      const firstError = Object.values(err.response.data.errors)[0]
      formError.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else {
      formError.value = 'An error occurred while saving the department.'
    }
  } finally {
    saving.value = false
  }
}

const deleteDepartment = async (dept) => {
  const confirmed = await notify.confirm({
    title: `Delete Department "${dept.name}"?`,
    message: 'Are you sure you want to delete this department? Courses and offerings linked to this department may be affected.',
    confirmText: 'Delete Department',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/departments/${dept.id}`)
    if (response.data.status === 'success') {
      notify.success('Department deleted successfully.')
      await fetchDepartments()
    } else {
      notify.error(response.data.message || 'Failed to delete department')
    }
  } catch (err) {
    console.error('Failed to delete department:', err)
    const msg = err.response?.data?.message || 'Failed to delete department'
    notify.error(msg)
  }
}

onMounted(async () => {
  await Promise.all([fetchCampuses(), fetchDepartments()])
})
</script>
