<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="goBack"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Course Offerings
        </button>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">{{ courseTitle }}</h1>
          <p v-if="offering" class="text-sm text-gray-600 mt-1">
            {{ offering.course?.code }} • Year {{ offering.study_year }}, Semester {{ offering.semester }} • {{ offering.academic_year }}
          </p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600"></div>
        <p class="text-sm text-gray-500 mt-3 font-medium">Loading course materials...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <button @click="loadMaterials" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="materials.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No course materials available</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">No published study materials or lecture notes have been uploaded for this offering yet.</p>
      </div>

      <!-- Materials List -->
      <div v-else class="space-y-4">
        <div
          v-for="material in materials"
          :key="material.id"
          class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-6"
        >
          <div class="flex-1 min-w-0">
            <div class="flex items-start gap-4 mb-2">
              <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="min-w-0">
                <h3 class="text-base font-bold text-gray-900 truncate" :title="material.title">{{ material.title }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ material.original_filename }}</p>
              </div>
            </div>
            
            <p v-if="material.description" class="text-xs text-gray-600 mt-2 line-clamp-2 leading-relaxed">
              {{ material.description }}
            </p>
            
            <div class="flex items-center gap-3 text-xs text-gray-400 pt-3 mt-3 border-t border-gray-100">
              <span class="font-medium text-gray-600">{{ formatFileSize(material.file_size) }}</span>
              <span>•</span>
              <span>Version {{ material.version }}</span>
              <span v-if="material.created_at">•</span>
              <span v-if="material.created_at">{{ formatDate(material.created_at) }}</span>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex flex-wrap items-center gap-2 flex-shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100">
            <!-- Download Button -->
            <button
              @click="downloadMaterial(material)"
              :disabled="downloadingId === material.id"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm"
            >
              <svg v-if="downloadingId === material.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ downloadingId === material.id ? 'Downloading...' : 'Download PDF' }}</span>
            </button>
            
            <!-- Generate Notes Button -->
            <button
              @click="generateNotes(material)"
              :disabled="generatingId === material.id"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 text-primary-700 border border-blue-200 text-xs font-semibold rounded-xl hover:bg-blue-100 transition-colors disabled:opacity-50"
            >
              <svg v-if="generatingId === material.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ generatingId === material.id ? 'Generating...' : 'AI Notes' }}</span>
            </button>
            
            <!-- Generate Quiz Button -->
            <button
              @click="generateQuiz(material)"
              :disabled="generatingQuizId === material.id"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold rounded-xl hover:bg-emerald-100 transition-colors disabled:opacity-50"
            >
              <svg v-if="generatingQuizId === material.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ generatingQuizId === material.id ? 'Generating...' : 'AI Quiz' }}</span>
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
          <nav class="inline-flex items-center gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 disabled:opacity-50 transition-colors"
            >
              Previous
            </button>
            
            <span class="px-3.5 py-2 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 disabled:opacity-50 transition-colors"
            >
              Next
            </button>
          </nav>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { catalogService } from '../services/catalog'
import { notesService } from '../services/notes'
import { quizzesService } from '../services/quizzes'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()
const router = useRouter()
const route = useRoute()
const offering = ref(null)
const materials = ref([])
const pagination = ref(null)
const isLoading = ref(true)
const errorMessage = ref('')
const downloadingId = ref(null)
const generatingId = ref(null)
const generatingQuizId = ref(null)

const courseTitle = computed(() => {
  return offering.value?.course?.title || offering.value?.course?.name || 'Course Materials'
})

const goBack = () => {
  if (offering.value?.department_id) {
    router.push(`/departments/${offering.value.department_id}/courses`)
  } else {
    router.push('/departments')
  }
}

onMounted(() => {
  loadMaterials()
})

const loadMaterials = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const offeringId = route.params.offeringId
    offering.value = await catalogService.getCourseOffering(offeringId)
    const result = await catalogService.getCourseMaterials(offeringId, { page })
    materials.value = result.materials
    pagination.value = result.pagination
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
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const downloadMaterial = async (material) => {
  downloadingId.value = material.id
  try {
    await catalogService.downloadMaterial(material.id, material.original_filename)
    notify.success(`Downloading "${material.title}"`)
  } catch (error) {
    console.error('Failed to download material:', error)
    notify.error('Failed to download material. Please try again.')
  } finally {
    downloadingId.value = null
  }
}

const generateNotes = async (material) => {
  generatingId.value = material.id
  try {
    const note = await notesService.generateNote(material.id)
    notify.success('AI Study Notes generated successfully!')
    router.push(`/notes/${note.id}`)
  } catch (error) {
    console.error('Notes generation failed:', error)
    notify.error(error.response?.data?.message || 'Failed to generate study notes.')
  } finally {
    generatingId.value = null
  }
}

const generateQuiz = async (material) => {
  generatingQuizId.value = material.id
  try {
    const quiz = await quizzesService.generateQuiz(material.id)
    notify.success('AI Practice Quiz generated successfully!')
    router.push(`/quizzes/${quiz.id}`)
  } catch (error) {
    console.error('Quiz generation failed:', error)
    notify.error(error.response?.data?.message || 'Failed to generate practice quiz.')
  } finally {
    generatingQuizId.value = null
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
</script>
