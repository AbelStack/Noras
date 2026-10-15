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
            <router-link to="/dashboard" class="text-sm text-gray-600 hover:text-gray-900">Dashboard</router-link>
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
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Bookmarks</h1>
        <p class="mt-2 text-gray-600">Quick access to your saved materials, notes, and quizzes</p>
      </div>

      <!-- Filter Tabs -->
      <div class="mb-6 flex flex-wrap gap-2">
        <button
          @click="filterBookmarks(null)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === null
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          All Bookmarks
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.MATERIAL)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === RESOURCE_TYPES.MATERIAL
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Materials
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.NOTE)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === RESOURCE_TYPES.NOTE
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Notes
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.QUIZ)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === RESOURCE_TYPES.QUIZ
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Quizzes
        </button>
      </div>

      <!-- Error State -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadBookmarks"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading bookmarks...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="bookmarks.length === 0 && !errorMessage" class="text-center py-12 bg-white rounded-xl shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No bookmarks found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ currentFilter === null ? 'Save materials, notes, or quizzes to access them quickly.' : 'No bookmarks match this filter.' }}
        </p>
      </div>

      <!-- Bookmarks List -->
      <div v-else class="space-y-4">
        <div
          v-for="bookmark in bookmarks"
          :key="bookmark.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4 flex-1">
              <!-- Type Icon -->
              <div :class="getIconClass(bookmark.bookmarkable_type)" class="flex-shrink-0">
                <svg v-if="bookmark.bookmarkable_type.includes('CourseMaterial')" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else-if="bookmark.bookmarkable_type.includes('GeneratedNote')" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                </svg>
                <svg v-else-if="bookmark.bookmarkable_type.includes('GeneratedQuiz')" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>

              <!-- Bookmark Content -->
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-1">
                  <span :class="getTypeTagClass(bookmark.bookmarkable_type)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ getResourceTypeName(bookmark.bookmarkable_type) }}
                  </span>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                  {{ getBookmarkTitle(bookmark) }}
                </h3>

                <p v-if="getBookmarkDescription(bookmark)" class="text-gray-600 text-sm mb-2">
                  {{ getBookmarkDescription(bookmark) }}
                </p>

                <div class="text-sm text-gray-500">
                  Bookmarked {{ formatDate(bookmark.created_at) }}
                </div>

                <!-- Resource Unavailable Warning -->
                <div v-if="!bookmark.bookmarkable" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                  <p class="text-sm text-yellow-800">
                    ⚠️ This resource is no longer available. Consider removing this bookmark.
                  </p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2 ml-4">
              <router-link
                v-if="bookmark.bookmarkable"
                :to="getResourceLink(bookmark)"
                class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                title="Open resource"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
              </router-link>
              <button
                @click="confirmDelete(bookmark)"
                :disabled="deletingId === bookmark.id"
                class="p-2 text-gray-400 hover:text-red-600 transition-colors disabled:opacity-50"
                title="Remove bookmark"
              >
                <svg v-if="deletingId === bookmark.id" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
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

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Remove Bookmark</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to remove this bookmark?
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false; bookmarkToDelete = null"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="deleteBookmark"
            :disabled="isDeleting"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ isDeleting ? 'Removing...' : 'Remove' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { bookmarksService } from '../services/bookmarks'

const router = useRouter()
const user = ref(authService.getUser())
const bookmarks = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const currentFilter = ref(null)
const isLoading = ref(true)
const errorMessage = ref('')
const deletingId = ref(null)

// Modal states
const showDeleteModal = ref(false)
const bookmarkToDelete = ref(null)
const isDeleting = ref(false)

const RESOURCE_TYPES = bookmarksService.RESOURCE_TYPES

onMounted(() => {
  loadBookmarks()
})

const loadBookmarks = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const params = { page, per_page: 15 }
    if (currentFilter.value) {
      params.bookmarkable_type = currentFilter.value
    }
    
    const result = await bookmarksService.listBookmarks(params)
    bookmarks.value = result.data
    pagination.value = result.pagination
    currentPage.value = page
  } catch (error) {
    console.error('Failed to load bookmarks:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load bookmarks. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const filterBookmarks = (type) => {
  currentFilter.value = type
  loadBookmarks(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadBookmarks(page)
  }
}

const getResourceTypeName = (type) => {
  if (type.includes('CourseMaterial')) return 'Material'
  if (type.includes('GeneratedNote')) return 'Note'
  if (type.includes('GeneratedQuiz')) return 'Quiz'
  return 'Unknown'
}

const getIconClass = (type) => {
  if (type.includes('CourseMaterial')) return 'text-red-600'
  if (type.includes('GeneratedNote')) return 'text-blue-600'
  if (type.includes('GeneratedQuiz')) return 'text-green-600'
  return 'text-gray-600'
}

const getTypeTagClass = (type) => {
  if (type.includes('CourseMaterial')) return 'bg-red-100 text-red-700'
  if (type.includes('GeneratedNote')) return 'bg-blue-100 text-blue-700'
  if (type.includes('GeneratedQuiz')) return 'bg-green-100 text-green-700'
  return 'bg-gray-100 text-gray-700'
}

const getBookmarkTitle = (bookmark) => {
  if (!bookmark.bookmarkable) return 'Resource Unavailable'
  
  if (bookmark.bookmarkable_type.includes('CourseMaterial')) {
    return bookmark.bookmarkable.title
  }
  if (bookmark.bookmarkable_type.includes('GeneratedNote')) {
    return bookmark.bookmarkable.course_material?.title || 'Study Notes'
  }
  if (bookmark.bookmarkable_type.includes('GeneratedQuiz')) {
    return `Quiz: ${bookmark.bookmarkable.question_count} questions`
  }
  return 'Unknown Resource'
}

const getBookmarkDescription = (bookmark) => {
  if (!bookmark.bookmarkable) return null
  
  if (bookmark.bookmarkable_type.includes('CourseMaterial')) {
    return bookmark.bookmarkable.description || `${bookmark.bookmarkable.original_filename}`
  }
  if (bookmark.bookmarkable_type.includes('GeneratedNote')) {
    return `Generated with ${bookmark.bookmarkable.ai_provider}`
  }
  if (bookmark.bookmarkable_type.includes('GeneratedQuiz')) {
    return `${bookmark.bookmarkable.question_count} questions • ${bookmark.bookmarkable.ai_provider}`
  }
  return null
}

const getResourceLink = (bookmark) => {
  if (!bookmark.bookmarkable) return '#'
  
  if (bookmark.bookmarkable_type.includes('GeneratedNote')) {
    return `/notes/${bookmark.bookmarkable.id}`
  }
  if (bookmark.bookmarkable_type.includes('GeneratedQuiz')) {
    return `/quizzes/${bookmark.bookmarkable.id}`
  }
  if (bookmark.bookmarkable_type.includes('CourseMaterial')) {
    // Navigate to the materials page for the offering
    return `/offerings/${bookmark.bookmarkable.course_offering_id}/materials`
  }
  return '#'
}

const confirmDelete = (bookmark) => {
  bookmarkToDelete.value = bookmark
  showDeleteModal.value = true
}

const deleteBookmark = async () => {
  if (isDeleting.value) return
  
  isDeleting.value = true
  try {
    await bookmarksService.deleteBookmark(bookmarkToDelete.value.id)
    showDeleteModal.value = false
    bookmarkToDelete.value = null
    await loadBookmarks(currentPage.value)
  } catch (error) {
    console.error('Failed to delete bookmark:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to remove bookmark. Please try again.'
    showDeleteModal.value = false
    bookmarkToDelete.value = null
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    isDeleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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
