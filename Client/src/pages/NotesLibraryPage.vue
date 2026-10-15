<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">AI Study Notes</h1>
          <p class="text-sm text-gray-600 mt-1">Your AI-generated study notes library</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600"></div>
        <p class="text-sm text-gray-500 mt-3 font-medium">Loading your notes...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <button
          @click="loadNotes"
          class="text-sm text-red-700 hover:text-red-800 underline font-medium"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="notes.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No notes yet</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">Generate AI study notes from your course materials to see them here.</p>
        <router-link
          to="/departments"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-sm transition-colors"
        >
          Browse Course Materials
        </router-link>
      </div>

      <!-- Notes List -->
      <div v-else class="space-y-4">
        <div
          v-for="note in notes"
          :key="note.id"
          class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-primary-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path>
                  <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                </svg>
              </span>
              <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                {{ note.material?.title || 'Untitled Material' }}
              </h3>
            </div>

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-gray-500 ml-10 mb-2">
              <span v-if="note.material?.course" class="font-medium text-gray-700">
                {{ note.material.course.code }} - {{ note.material.course.name }}
              </span>
              <span v-if="note.material?.department" class="text-gray-300">•</span>
              <span v-if="note.material?.department">
                {{ note.material.department.name }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-x-2 text-xs text-gray-400 ml-10">
              <span>Generated {{ formatDate(note.created_at) }}</span>
              <span>•</span>
              <span>{{ Math.round((note.notes_char_count || 0) / 1000) }}k characters</span>
              <span v-if="note.ai_provider">•</span>
              <span v-if="note.ai_provider">{{ note.ai_provider }} ({{ note.ai_model }})</span>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:self-center ml-10 sm:ml-0 flex-shrink-0">
            <router-link
              :to="`/notes/${note.id}`"
              class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-sm font-medium shadow-sm"
            >
              <span>View Notes</span>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="pt-4 flex items-center justify-between">
          <button
            @click="loadPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
          >
            Previous
          </button>

          <span class="text-xs sm:text-sm text-gray-500">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>

          <button
            @click="loadPage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { notesService } from '../services/notes'
import { authService } from '../services/auth'
import { studentNavigationItems } from '../config/navigation'

const router = useRouter()

const notes = ref([])
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
})
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(() => {
  loadNotes()
})

const loadNotes = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await notesService.getNotes(page)
    notes.value = data.notes
    pagination.value = data.pagination
  } catch (error) {
    console.error('Failed to load notes:', error)
    
    if (error.response?.status === 401) {
      errorMessage.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        authService.logout()
        router.push('/login')
      }, 2000)
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to load notes. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

const loadPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadNotes(page)
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}
</script>
