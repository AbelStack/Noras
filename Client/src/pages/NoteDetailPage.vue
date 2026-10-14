<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50/30">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/dashboard" class="flex items-center">
              <img src="/Noras logo-sm.png" alt="Noras" class="h-8 w-auto" />
              <span class="ml-2 text-xl font-bold text-gray-900">Noras</span>
            </router-link>
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <div class="mb-6">
        <router-link
          to="/notes"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Notes Library
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading note...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <div class="mt-4 space-x-4">
          <button
            @click="loadNote"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Try Again
          </button>
          <router-link
            to="/notes"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            Back to Library
          </router-link>
        </div>
      </div>

      <!-- Note Content -->
      <div v-else-if="note" class="space-y-6">
        <!-- Note Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path>
                  <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                </svg>
                <h1 class="text-2xl font-bold text-gray-900">
                  {{ material?.title || 'Study Notes' }}
                </h1>
              </div>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600 mb-3">
                <span v-if="material?.course">
                  {{ material.course.code }} - {{ material.course.name }}
                </span>
                <span v-if="material?.department" class="text-gray-400">•</span>
                <span v-if="material?.department">
                  {{ material.department.name }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
                <span>Generated {{ formatDate(note.created_at) }}</span>
                <span>•</span>
                <span>{{ Math.round(note.notes_char_count / 1000) }}k characters</span>
                <span>•</span>
                <span>{{ note.ai_provider }} ({{ note.ai_model }})</span>
                <span>•</span>
                <span>{{ note.generation_time_ms }}ms generation time</span>
              </div>

              <!-- Version Warning -->
              <div 
                v-if="material?.version && note.material_version && material.version !== note.material_version"
                class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
              >
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                  <p class="text-sm text-yellow-800">
                    <strong>Outdated notes:</strong> The source material has been updated (v{{ material.version }}). These notes were generated from v{{ note.material_version }}. Consider regenerating for the latest content.
                  </p>
                </div>
              </div>
            </div>

            <button
              @click="showDeleteConfirm = true"
              :disabled="isDeleting"
              class="ml-4 px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>

        <!-- Note Content Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <div 
            class="prose prose-blue max-w-none"
            v-html="sanitizedContent"
          ></div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
          <router-link
            v-if="material?.id"
            :to="`/offerings/${material.course_offering_id}/materials`"
            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700"
          >
            View Source Material
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Study Notes?</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete these study notes? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="handleDelete"
            :disabled="isDeleting"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ isDeleting ? 'Deleting...' : 'Delete Notes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/auth'
import { notesService } from '../services/notes'
import DOMPurify from 'dompurify'
import { marked } from 'marked'

const router = useRouter()
const route = useRoute()
const user = authService.getUser()

const note = ref(null)
const material = ref(null)
const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')
const showDeleteConfirm = ref(false)

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

const handleDelete = async () => {
  isDeleting.value = true

  try {
    await notesService.deleteNote(noteId.value)
    router.push('/notes')
  } catch (error) {
    console.error('Failed to delete note:', error)
    showDeleteConfirm.value = false
    
    if (error.response?.status === 403) {
      errorMessage.value = 'You do not have permission to delete this note.'
    } else if (error.response?.status === 404) {
      errorMessage.value = 'Note not found. It may have been deleted already.'
      setTimeout(() => router.push('/notes'), 2000)
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to delete note. Please try again.'
    }
  } finally {
    isDeleting.value = false
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
