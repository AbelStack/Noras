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
      <div v-if="isLoading">
        <SkeletonLoader type="cards" :count="6" />
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
              <div v-if="isPpt(material)" class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs tracking-wider shadow-2xs">
                PPT
              </div>
              <div v-else class="w-10 h-10 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-bold text-gray-900 truncate" :title="material.title">{{ material.title }}</h3>
                  <span 
                    class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase"
                    :class="isPpt(material) ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ isPpt(material) ? 'PPT' : 'PDF' }}
                  </span>
                </div>
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
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm cursor-pointer"
            >
              <svg v-if="downloadingId === material.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span>{{ downloadingId === material.id ? 'Downloading...' : (isPpt(material) ? 'Download PPT' : 'Download PDF') }}</span>
            </button>

            <!-- Save / Bookmark Button -->
            <button
              @click="toggleBookmark(material)"
              :disabled="bookmarkingId === material.id"
              :class="[
                'inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer',
                isBookmarked(material.id)
                  ? 'bg-amber-50 text-accent-600 border-amber-300'
                  : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
              ]"
              :title="isBookmarked(material.id) ? 'Remove from Saved Materials' : 'Save Material to Bookmarks'"
            >
              <svg class="w-3.5 h-3.5" :fill="isBookmarked(material.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
              </svg>
              <span>{{ isBookmarked(material.id) ? 'Saved' : 'Save' }}</span>
            </button>
            
            <!-- Study Notes Link & Download Button -->
            <div v-if="material.master_note" class="flex items-center gap-1.5">
              <router-link
                :to="`/notes/${material.master_note.id}`"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-50 text-primary-700 border border-primary-200 text-xs font-semibold rounded-xl hover:bg-primary-100 transition-colors shadow-2xs cursor-pointer"
              >
                <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Study Notes</span>
              </router-link>
              <button
                @click="downloadNote(material)"
                :disabled="downloadingNoteId === material.id"
                class="inline-flex items-center gap-1 px-3 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-2xs cursor-pointer"
                title="Download Study Notes as Markdown file"
              >
                <svg v-if="downloadingNoteId === material.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Note</span>
              </button>
            </div>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-50 text-gray-400 border border-gray-200 text-xs font-medium rounded-xl cursor-not-allowed"
              title="Official study notes are being prepared by your instructor"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Notes Pending</span>
            </span>
            
            <!-- Practice Quiz Link -->
            <router-link
              v-if="material.master_quiz"
              :to="`/quizzes/${material.master_quiz.id}`"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-semibold rounded-xl hover:bg-indigo-100 transition-colors shadow-2xs cursor-pointer"
            >
              <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <span>Practice Quiz ({{ material.master_quiz.question_count || 20 }} Qs)</span>
            </router-link>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-50 text-gray-400 border border-gray-200 text-xs font-medium rounded-xl cursor-not-allowed"
              title="Official practice quiz is being prepared by your instructor"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Quiz Pending</span>
            </span>
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
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { catalogService } from '../services/catalog'
import { notesService } from '../services/notes'
import { quizzesService } from '../services/quizzes'
import { bookmarksService } from '../services/bookmarks'
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
const downloadingNoteId = ref(null)
const generatingId = ref(null)
const generatingQuizId = ref(null)
const bookmarkingId = ref(null)
const userBookmarks = ref({}) // { materialId: bookmarkId }

const courseTitle = computed(() => {
  return offering.value?.course?.title || offering.value?.course?.name || 'Course Materials'
})

const goBack = () => {
  if (offering.value?.department_id) {
    const yearParam = offering.value?.study_year ? `?year=${offering.value.study_year}` : ''
    router.push(`/departments/${offering.value.department_id}/courses${yearParam}`)
  } else {
    router.push('/departments')
  }
}

onMounted(() => {
  loadMaterials()
})

const isBookmarked = (materialId) => {
  return !!userBookmarks.value[materialId]
}

const toggleBookmark = async (material) => {
  bookmarkingId.value = material.id
  try {
    if (isBookmarked(material.id)) {
      const bookmarkId = userBookmarks.value[material.id]
      await bookmarksService.deleteBookmark(bookmarkId)
      delete userBookmarks.value[material.id]
      notify.success(`"${material.title}" removed from saved materials.`)
    } else {
      const response = await bookmarksService.createBookmark(
        bookmarksService.RESOURCE_TYPES.MATERIAL,
        material.id
      )
      userBookmarks.value[material.id] = response.data?.id || response.id || true
      notify.success(`"${material.title}" saved to bookmarks!`)
    }
  } catch (error) {
    console.error('Failed to update bookmark:', error)
    notify.error('Could not update saved status. Please try again.')
  } finally {
    bookmarkingId.value = null
  }
}

const loadMaterials = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const offeringId = route.params.offeringId
    const [offeringData, result, bookmarksData] = await Promise.all([
      catalogService.getCourseOffering(offeringId).catch(err => {
        console.warn('Could not load course offering details:', err)
        return null
      }),
      catalogService.getCourseMaterials(offeringId, { page }),
      bookmarksService.listBookmarks({ per_page: 100, bookmarkable_type: bookmarksService.RESOURCE_TYPES.MATERIAL }).catch(() => ({ data: [] }))
    ])

    offering.value = offeringData || result?.materials?.[0]?.course_offering || null
    materials.value = result?.materials || []
    pagination.value = result?.pagination || null

    const bMap = {}
    if (bookmarksData?.data && Array.isArray(bookmarksData.data)) {
      bookmarksData.data.forEach(b => {
        if (b.bookmarkable_id) bMap[b.bookmarkable_id] = b.id
      })
    }
    userBookmarks.value = bMap
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

const downloadNote = async (material) => {
  if (!material.master_note) return
  downloadingNoteId.value = material.id
  try {
    const cleanTitle = (material.title || 'study-notes').replace(/[^a-zA-Z0-9_-]/g, '_')
    await notesService.downloadNote(material.master_note.id, `${cleanTitle}_Study_Notes.md`)
    notify.success(`Downloaded study notes for "${material.title}"`)
  } catch (error) {
    console.error('Failed to download study notes:', error)
    notify.error('Failed to download study notes. Please try again.')
  } finally {
    downloadingNoteId.value = null
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

const isPpt = (material) => {
  if (!material) return false
  const name = (material.original_filename || '').toLowerCase()
  const mime = (material.mime_type || '').toLowerCase()
  return name.endsWith('.ppt') || name.endsWith('.pptx') || mime.includes('presentation') || mime.includes('powerpoint')
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
