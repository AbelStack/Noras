<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50/30">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <img src="/Noras logo-sm.png" alt="Noras" class="h-8 w-auto" />
            <span class="ml-2 text-xl font-bold text-gray-900">Noras</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ user?.name }}</span>
            <button
              @click="handleLogout"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <CatalogBreadcrumb :items="breadcrumbs" />

      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ courseTitle }}</h1>
        <p v-if="offering" class="mt-2 text-gray-600">
          {{ offering.course?.code }} • Year {{ offering.study_year }}, Semester {{ offering.semester }} • {{ offering.academic_year }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading materials...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadMaterials"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="materials.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No materials found</h3>
        <p class="mt-1 text-sm text-gray-500">No published materials are available for this course yet.</p>
      </div>

      <!-- Materials List -->
      <div v-else>
        <div class="space-y-4">
          <div
            v-for="material in materials"
            :key="material.id"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                  </svg>
                  <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ material.title }}</h3>
                    <p class="text-sm text-gray-500">{{ material.original_filename }}</p>
                  </div>
                </div>
                
                <p v-if="material.description" class="text-gray-600 mb-3">{{ material.description }}</p>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>{{ formatFileSize(material.file_size) }}</span>
                  <span>•</span>
                  <span>Version {{ material.version }}</span>
                  <span v-if="material.created_at">•</span>
                  <span v-if="material.created_at">{{ formatDate(material.created_at) }}</span>
                </div>
              </div>
              
              <button
                @click="downloadMaterial(material)"
                :disabled="downloadingId === material.id"
                class="ml-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
              >
                <span v-if="downloadingId === material.id" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Downloading...
                </span>
                <span v-else>Download</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
          <nav class="inline-flex rounded-md shadow-sm space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/auth'
import { catalogService } from '../services/catalog'
import CatalogBreadcrumb from '../components/CatalogBreadcrumb.vue'

const router = useRouter()
const route = useRoute()
const user = ref(authService.getUser())
const offering = ref(null)
const materials = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const isLoading = ref(true)
const errorMessage = ref('')
const downloadingId = ref(null)

const courseTitle = computed(() => {
  return offering.value?.course?.title || 'Course Materials'
})

const breadcrumbs = computed(() => {
  const items = [
    { label: 'Departments', to: '/departments' },
  ]
  if (offering.value?.department) {
    items.push({
      label: offering.value.department.name,
      to: `/departments/${offering.value.department.id}/courses`
    })
  }
  if (offering.value?.course) {
    items.push({ label: offering.value.course.title })
  }
  return items
})

onMounted(() => {
  loadMaterials()
})

const loadMaterials = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const offeringId = route.params.offeringId
    
    // Load offering details first (for breadcrumb)
    const offeringResponse = await catalogService.getCourseOfferings({ })
    offering.value = offeringResponse.find(o => o.id == offeringId)
    
    // Load materials for this offering
    const result = await catalogService.getMaterials({ course_offering_id: offeringId }, page)
    materials.value = result.materials
    pagination.value = result.pagination
    currentPage.value = page
  } catch (error) {
    console.error('Failed to load materials:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load materials. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadMaterials(page)
  }
}

const downloadMaterial = async (material) => {
  if (downloadingId.value) return

  downloadingId.value = material.id
  try {
    const response = await catalogService.downloadMaterial(material.id)
    
    // Create a blob from the response data
    const blob = new Blob([response.data], { type: material.mime_type })
    
    // Create a temporary URL for the blob
    const url = window.URL.createObjectURL(blob)
    
    // Create a temporary link and trigger download
    const link = document.createElement('a')
    link.href = url
    link.download = material.original_filename
    document.body.appendChild(link)
    link.click()
    
    // Clean up
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download failed:', error)
    alert(error.response?.data?.message || 'Failed to download file. Please try again.')
  } finally {
    downloadingId.value = null
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'Unknown size'
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
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
</script>
