<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/dashboard')"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Bookmarks</h1>
          <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Quick access to your saved materials, notes, and quizzes</p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <button
          @click="filterBookmarks(null)"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === null
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          All Bookmarks
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.MATERIAL)"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === RESOURCE_TYPES.MATERIAL
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          Materials
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.NOTE)"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === RESOURCE_TYPES.NOTE
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          Notes
        </button>
        <button
          @click="filterBookmarks(RESOURCE_TYPES.QUIZ)"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === RESOURCE_TYPES.QUIZ
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          Quizzes
        </button>
      </div>

      <!-- Error State -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <button
          @click="loadBookmarks(currentPage)"
          class="text-sm text-red-700 hover:text-red-800 underline font-medium"
        >
          Try Again
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="cards" :count="6" />
      </div>

      <!-- Empty State -->
      <div v-else-if="bookmarks.length === 0 && !errorMessage" class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center shadow-sm min-h-[440px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No bookmarks found</h3>
        <p class="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto mb-5">
          {{ currentFilter === null ? 'Save materials, notes, or quizzes to access them quickly.' : 'No bookmarks match this filter.' }}
        </p>
        <router-link
          to="/departments"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-semibold text-xs sm:text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-2xs transition-colors"
        >
          Browse Materials
        </router-link>
      </div>

      <!-- Bookmarks List -->
      <div v-else class="space-y-3 min-h-[460px]">
        <div
          v-for="bookmark in bookmarks"
          :key="bookmark.id"
          class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm hover:shadow-md transition-all flex items-start justify-between gap-4"
        >
          <div class="flex items-start gap-4 flex-1 min-w-0">
            <!-- Type Icon -->
            <div :class="getIconClass(bookmark.bookmarkable_type)" class="flex-shrink-0 mt-0.5">
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 border border-gray-100">
                <svg v-if="bookmark.bookmarkable_type.includes('CourseMaterial')" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else-if="bookmark.bookmarkable_type.includes('GeneratedNote')" class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                </svg>
                <svg v-else-if="bookmark.bookmarkable_type.includes('GeneratedQuiz')" class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
              </span>
            </div>

            <!-- Bookmark Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span :class="getTypeTagClass(bookmark.bookmarkable_type)" class="px-2.5 py-0.5 rounded-lg text-xs font-semibold">
                  {{ getResourceTypeName(bookmark.bookmarkable_type) }}
                </span>
                <span class="text-xs text-gray-400">
                  Bookmarked {{ formatDate(bookmark.created_at) }}
                </span>
              </div>

              <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight truncate mb-1">
                {{ getBookmarkTitle(bookmark) }}
              </h3>

              <p v-if="getBookmarkDescription(bookmark)" class="text-gray-600 text-xs sm:text-sm line-clamp-2 mb-2">
                {{ getBookmarkDescription(bookmark) }}
              </p>

              <!-- Resource Unavailable Warning -->
              <div v-if="!bookmark.bookmarkable" class="mt-2 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span>This resource is no longer available. Consider removing this bookmark.</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 ml-2 flex-shrink-0">
            <router-link
              v-if="bookmark.bookmarkable"
              :to="getResourceLink(bookmark)"
              class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-50 text-primary-700 hover:bg-primary-100 rounded-xl text-xs font-semibold border border-primary-200 transition-colors"
              title="Open resource"
            >
              <span>Open</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
            </router-link>
            <button
              @click="confirmRemoveBookmark(bookmark)"
              :disabled="deletingId === bookmark.id"
              class="p-2 text-gray-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition-colors disabled:opacity-50"
              title="Remove bookmark"
            >
              <svg v-if="deletingId === bookmark.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Dynamic Pagination -->
        <DynamicPagination
          v-if="pagination && pagination.total > 0"
          :currentPage="pagination.current_page"
          :totalPages="pagination.last_page"
          :totalItems="pagination.total"
          :perPage="pagination.per_page || 10"
          :showPerPage="false"
          itemLabel="bookmarks"
          @update:currentPage="changePage"
          class="pt-4 rounded-2xl border border-gray-200 shadow-sm"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import DynamicPagination from '../components/DynamicPagination.vue'
import { bookmarksService } from '../services/bookmarks'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'

const router = useRouter()
const notify = useNotification()

const bookmarks = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const currentFilter = ref(null)
const isLoading = ref(true)
const errorMessage = ref('')
const deletingId = ref(null)

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
  if (type.includes('GeneratedNote')) return 'text-primary-600'
  if (type.includes('GeneratedQuiz')) return 'text-emerald-600'
  return 'text-gray-600'
}

const getTypeTagClass = (type) => {
  if (type.includes('CourseMaterial')) return 'bg-red-50 text-red-700 border border-red-200'
  if (type.includes('GeneratedNote')) return 'bg-primary-50 text-primary-700 border border-primary-200'
  if (type.includes('GeneratedQuiz')) return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  return 'bg-gray-100 text-gray-700 border border-gray-200'
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
    return `${bookmark.bookmarkable.question_count} questions &bull; ${bookmark.bookmarkable.ai_provider}`
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
    return `/offerings/${bookmark.bookmarkable.course_offering_id}/materials`
  }
  return '#'
}

const confirmRemoveBookmark = async (bookmark) => {
  const confirmed = await notify.confirm({
    title: 'Remove Bookmark?',
    message: 'Are you sure you want to remove this bookmark?',
    confirmText: 'Remove',
    cancelText: 'Cancel',
    type: 'danger',
  })

  if (!confirmed) return

  deletingId.value = bookmark.id
  try {
    await bookmarksService.deleteBookmark(bookmark.id)
    notify.success('Bookmark removed.')
    await loadBookmarks(currentPage.value)
  } catch (error) {
    console.error('Failed to delete bookmark:', error)
    notify.error(error.response?.data?.message || 'Failed to remove bookmark. Please try again.')
  } finally {
    deletingId.value = null
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
