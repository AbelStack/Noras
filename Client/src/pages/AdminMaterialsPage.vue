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
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Course Materials</h1>
            <p class="text-sm text-gray-600 mt-1">Review, monitor, and manage academic resources and documents</p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Materials</div>
          <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</div>
          <p class="text-xs text-gray-400 mt-1">Uploaded PDF files</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Published</div>
          <div class="text-2xl sm:text-3xl font-bold text-emerald-600 mt-2">{{ stats.published }}</div>
          <p class="text-xs text-gray-400 mt-1">Accessible by students</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Drafts</div>
          <div class="text-2xl sm:text-3xl font-bold text-amber-600 mt-2">{{ stats.drafts }}</div>
          <p class="text-xs text-gray-400 mt-1">Pending publication</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Courses Linked</div>
          <div class="text-2xl sm:text-3xl font-bold text-primary-600 mt-2">{{ stats.coursesCount }}</div>
          <p class="text-xs text-gray-400 mt-1">Active curriculum subjects</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="relative w-full md:w-80">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search material title or course..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
          />
        </div>
        <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
          <select 
            v-model="filters.status"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
          >
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
          </select>

          <select 
            v-model="filters.courseId"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700 max-w-xs"
          >
            <option value="">All Courses</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.code }} - {{ course.title || course.name }}
            </option>
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
        <button @click="fetchMaterials" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Materials List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredMaterials.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No course materials found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto">
            {{ filters.search || filters.status || filters.courseId ? 'No materials match your current filter settings.' : 'Course materials uploaded by instructors or admins will appear here.' }}
          </p>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Document Title</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File Size</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Uploaded Date</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="material in filteredMaterials" :key="material.id" class="hover:bg-gray-50/80 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div class="min-w-0">
                        <div class="text-sm font-bold text-gray-900 truncate max-w-xs sm:max-w-md" :title="material.title">
                          {{ material.title }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">
                          {{ material.original_filename || 'PDF Document' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs font-mono font-semibold text-gray-800">
                      {{ material.course_offering?.course?.code || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 truncate max-w-xs">
                      {{ material.course_offering?.course?.title || material.course_offering?.course?.name || material.course_offering?.department?.name || '' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize"
                      :class="getStatusBadgeClass(material.status)"
                    >
                      {{ material.status || 'published' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                    {{ formatFileSize(material.file_size) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                    {{ formatDate(material.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button 
                      @click="deleteMaterial(material)" 
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
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const materials = ref([])
const courses = ref([])
const loading = ref(true)
const error = ref(null)

const filters = ref({
  search: '',
  status: '',
  courseId: '',
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

const stats = computed(() => {
  const total = materials.value.length
  const published = materials.value.filter(m => m.status === 'published').length
  const drafts = materials.value.filter(m => m.status === 'draft').length
  const uniqueCourses = new Set(materials.value.map(m => m.course_offering?.course?.id).filter(Boolean)).size

  return {
    total,
    published,
    drafts,
    coursesCount: uniqueCourses,
  }
})

const filteredMaterials = computed(() => {
  return materials.value.filter(m => {
    const search = filters.value.search.toLowerCase()
    const matchesSearch = !search ||
      m.title?.toLowerCase().includes(search) ||
      m.original_filename?.toLowerCase().includes(search) ||
      m.course_offering?.course?.code?.toLowerCase().includes(search) ||
      m.course_offering?.course?.title?.toLowerCase().includes(search) ||
      m.course_offering?.course?.name?.toLowerCase().includes(search)

    const matchesStatus = !filters.value.status || m.status === filters.value.status

    const matchesCourse = !filters.value.courseId ||
      m.course_offering?.course?.id === parseInt(filters.value.courseId)

    return matchesSearch && matchesStatus && matchesCourse
  })
})

const fetchMaterials = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/materials')
    if (response.data.status === 'success') {
      materials.value = response.data.data.materials || []
    }
  } catch (err) {
    console.error('Error fetching materials:', err)
    error.value = 'Failed to load course materials'
  } finally {
    loading.value = false
  }
}

const fetchCourses = async () => {
  try {
    const response = await api.get('/admin/courses')
    if (response.data.status === 'success') {
      courses.value = response.data.data.courses || []
    }
  } catch (err) {
    console.error('Error fetching courses:', err)
  }
}

const deleteMaterial = async (material) => {
  const confirmed = await notify.confirm({
    title: `Delete Material "${material.title}"?`,
    message: 'Are you sure you want to delete this course material? Any associated student bookmarks and task links will be safely cleaned up.',
    confirmText: 'Delete Material',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/materials/${material.id}`)
    if (response.data.status === 'success') {
      notify.success('Course material deleted successfully.')
      await fetchMaterials()
    } else {
      notify.error(response.data.message || 'Failed to delete material')
    }
  } catch (err) {
    console.error('Error deleting material:', err)
    notify.error(err.response?.data?.message || 'Failed to delete material')
  }
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'published':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    case 'draft':
      return 'bg-amber-50 text-amber-700 border border-amber-200'
    case 'archived':
      return 'bg-gray-100 text-gray-700 border border-gray-200'
    default:
      return 'bg-blue-50 text-blue-700 border border-blue-200'
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(1) + ' MB'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  await Promise.all([fetchMaterials(), fetchCourses()])
})
</script>
