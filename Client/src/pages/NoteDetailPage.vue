<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-5xl mx-auto space-y-6">
      <!-- Back Button -->
      <div>
        <button
          @click="$router.push('/notes')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Notes Library
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="detail" />
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="loadNote"
            class="text-sm text-red-700 hover:text-red-800 underline font-medium"
          >
            Try Again
          </button>
          <button
            @click="$router.push('/notes')"
            class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-medium"
          >
            Back to Library
          </button>
        </div>
      </div>

      <!-- Note Content -->
      <div v-else-if="note" class="space-y-6">
        <!-- Note Header -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex-shrink-0">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path>
                    <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                  </svg>
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                  {{ material?.title || 'Study Notes' }}
                </h1>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600 ml-10 mb-3">
                <span v-if="material?.course" class="font-medium text-gray-800">
                  {{ material.course.code }} - {{ material.course.name }}
                </span>
                <span v-if="material?.department" class="text-gray-300">•</span>
                <span v-if="material?.department">
                  {{ material.department.name }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400 ml-10">
                <span>Generated {{ formatDate(note.created_at) }}</span>
                <span>•</span>
                <span>{{ Math.round((note.notes_char_count || 0) / 1000) }}k characters</span>
                <span v-if="note.ai_provider">•</span>
                <span v-if="note.ai_provider">{{ note.ai_provider }} ({{ note.ai_model }})</span>
                <span v-if="note.generation_time_ms">•</span>
                <span v-if="note.generation_time_ms">{{ note.generation_time_ms }}ms</span>
              </div>

              <!-- Version Warning -->
              <div 
                v-if="material?.version && note.material_version && material.version !== note.material_version"
                class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 ml-10"
              >
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-xs sm:text-sm text-amber-800">
                  <strong class="font-semibold">Outdated notes:</strong> The source material has been updated (v{{ material.version }}). These notes were generated from v{{ note.material_version }}.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-center ml-10 sm:ml-0 flex-shrink-0">
              <!-- Download Note (.md) Button -->
              <button
                @click="downloadNote"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-xs sm:text-sm font-semibold transition-colors shadow-2xs cursor-pointer"
                title="Download notes as Markdown file"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Note</span>
              </button>

              <!-- Print Button -->
              <button
                @click="printNote"
                class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-xs sm:text-sm font-medium transition-colors cursor-pointer"
                title="Print notes or save as PDF"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print</span>
              </button>

              <button
                v-if="canDelete"
                @click="confirmDelete"
                :disabled="isDeleting"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-red-200 text-red-600 rounded-xl hover:bg-red-50 text-xs sm:text-sm font-medium transition-colors disabled:opacity-50 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>{{ isDeleting ? 'Deleting...' : 'Delete' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Note Content Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm">
          <div 
            class="prose prose-blue max-w-none"
            v-html="sanitizedContent"
          ></div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-2">
          <router-link
            v-if="material?.id"
            :to="`/offerings/${material.course_offering_id}/materials`"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
          >
            <span>View Source Material</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { notesService } from '../services/notes'
import { authService } from '../services/auth'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'
import DOMPurify from 'dompurify'
import { marked } from 'marked'

const router = useRouter()
const route = useRoute()
const notify = useNotification()

const note = ref(null)
const material = ref(null)
const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')

const user = computed(() => authService.getUser())
const canDelete = computed(() => {
  return authService.isAdmin() || (note.value?.generated_by && user.value?.id && note.value.generated_by === user.value.id)
})

const noteId = computed(() => parseInt(route.params.id))

// Sanitize HTML content to prevent XSS attacks
const sanitizedContent = computed(() => {
  if (!note.value?.content) return ''
  
  // Convert markdown to HTML if needed
  const html = marked.parse(note.value.content)
  
  // Sanitize HTML to remove dangerous content
  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'code', 'pre'],
    ALLOWED_ATTR: [],
  })
})

onMounted(() => {
  loadNote()
})

const loadNote = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await notesService.getNote(noteId.value)
    note.value = data.note
    material.value = data.material
  } catch (error) {
    console.error('Failed to load note:', error)
    
    if (error.response?.status === 404) {
      errorMessage.value = 'Note not found. It may have been deleted.'
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        authService.logout()
        router.push('/login')
      }, 2000)
    } else if (error.response?.status === 403) {
      errorMessage.value = 'You do not have permission to view this note.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to load note. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

const confirmDelete = async () => {
  const confirmed = await notify.confirm({
    title: 'Delete Study Notes?',
    message: 'Are you sure you want to delete these study notes? This action cannot be undone.',
    confirmText: 'Delete Notes',
    cancelText: 'Cancel',
    type: 'danger',
  })

  if (!confirmed) return
  await handleDelete()
}

const handleDelete = async () => {
  isDeleting.value = true

  try {
    await notesService.deleteNote(noteId.value)
    notify.success('Study notes deleted successfully.')
    router.push('/notes')
  } catch (error) {
    console.error('Failed to delete note:', error)
    
    if (error.response?.status === 403) {
      notify.error('You do not have permission to delete this note.')
    } else if (error.response?.status === 404) {
      notify.error('Note not found. It may have been deleted already.')
      setTimeout(() => router.push('/notes'), 2000)
    } else {
      notify.error(error.response?.data?.message || 'Failed to delete note. Please try again.')
    }
  } finally {
    isDeleting.value = false
  }
}

const downloadNote = () => {
  if (!note.value?.content) return
  const cleanTitle = (material.value?.title || 'Study_Notes').replace(/[^a-zA-Z0-9_-]/g, '_')
  notesService.downloadNoteContent(note.value.content, `${cleanTitle}_Study_Notes.md`)
  notify.success('Notes downloaded successfully!')
}

const printNote = () => {
  window.print()
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

<style scoped>
/* Prose styles for note content */
:deep(.prose) {
  color: #374151;
  line-height: 1.75;
}

:deep(.prose p) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}

:deep(.prose h1) {
  font-size: 2em;
  font-weight: 800;
  margin-top: 0;
  margin-bottom: 0.8em;
  line-height: 1.1;
  color: #111827;
}

:deep(.prose h2) {
  font-size: 1.5em;
  font-weight: 700;
  margin-top: 2em;
  margin-bottom: 1em;
  line-height: 1.3;
  color: #111827;
}

:deep(.prose h3) {
  font-size: 1.25em;
  font-weight: 600;
  margin-top: 1.6em;
  margin-bottom: 0.6em;
  line-height: 1.6;
  color: #111827;
}

:deep(.prose ul), :deep(.prose ol) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  padding-left: 1.625em;
}

:deep(.prose li) {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

:deep(.prose strong) {
  font-weight: 600;
  color: #111827;
}

:deep(.prose em) {
  font-style: italic;
}

:deep(.prose code) {
  background-color: #f3f4f6;
  padding: 0.2em 0.4em;
  border-radius: 0.25rem;
  font-size: 0.875em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

:deep(.prose pre) {
  background-color: #1f2937;
  color: #f9fafb;
  padding: 1em;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin-top: 1.7em;
  margin-bottom: 1.7em;
}

:deep(.prose blockquote) {
  border-left: 4px solid #2563eb;
  padding-left: 1em;
  font-style: italic;
  color: #6b7280;
  margin: 1.6em 0;
}
</style>
