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
        <h1 class="text-3xl font-bold text-gray-900">Learning Progress</h1>
        <p class="mt-2 text-gray-600">Track your learning activities and achievements</p>
      </div>

      <!-- Progress Summary Cards -->
      <div v-if="progressLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
        <div v-for="i in 5" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-24"></div>
        </div>
      </div>

      <div v-else-if="progress" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-blue-600">{{ progress.notes_generated }}</p>
              <p class="mt-1 text-sm text-gray-600">Notes Generated</p>
            </div>
            <svg class="w-10 h-10 text-blue-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-green-600">{{ progress.quizzes_generated }}</p>
              <p class="mt-1 text-sm text-gray-600">Quizzes Generated</p>
            </div>
            <svg class="w-10 h-10 text-green-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-orange-600">{{ progress.active_tasks }}</p>
              <p class="mt-1 text-sm text-gray-600">Active Tasks</p>
            </div>
            <svg class="w-10 h-10 text-orange-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-purple-600">{{ progress.completed_tasks }}</p>
              <p class="mt-1 text-sm text-gray-600">Completed Tasks</p>
            </div>
            <svg class="w-10 h-10 text-purple-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-pink-600">{{ progress.bookmarks }}</p>
              <p class="mt-1 text-sm text-gray-600">Bookmarks</p>
            </div>
            <svg class="w-10 h-10 text-pink-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
              <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Learning History -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-900">Learning History</h2>
          <p class="text-sm text-gray-600">Recent notes and quizzes you've generated</p>
        </div>

        <!-- Error State -->
        <div v-if="historyError" class="p-6">
          <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <p class="text-red-600">{{ historyError }}</p>
            <button
              @click="loadHistory"
              class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Try Again
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="historyLoading" class="p-6 text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p class="mt-4 text-gray-600">Loading history...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="history.length === 0" class="p-6 text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No activity yet</h3>
          <p class="mt-1 text-sm text-gray-500">
            Generate notes or quizzes from course materials to start building your history.
          </p>
        </div>

        <!-- History Timeline -->
        <div v-else class="p-6">
          <div class="space-y-4">
            <div
              v-for="item in history"
              :key="`${item.type}-${item.id}`"
              class="flex items-start space-x-4 p-4 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <!-- Type Icon -->
              <div :class="item.type === 'note' ? 'text-blue-600' : 'text-green-600'" class="flex-shrink-0 mt-1">
                <svg v-if="item.type === 'note'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                </svg>
                <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                  <span :class="item.type === 'note' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ item.type === 'note' ? 'Study Note' : 'Practice Quiz' }}
                  </span>
                  <span class="text-sm text-gray-500">
                    {{ formatDate(item.created_at) }}
                  </span>
                </div>

                <h3 class="text-base font-semibold text-gray-900 mb-1">
                  {{ item.course_material?.title || 'Course Material' }}
                </h3>

                <router-link
                  :to="item.type === 'note' ? `/notes/${item.id}` : `/quizzes/${item.id}`"
                  class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                  View {{ item.type === 'note' ? 'Notes' : 'Quiz' }} →
                </router-link>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="historyPagination && historyPagination.last_page > 1" class="mt-6 pt-6 border-t border-gray-200">
            <nav class="flex justify-center space-x-2">
              <button
                @click="changeHistoryPage(historyPagination.current_page - 1)"
                :disabled="historyPagination.current_page === 1"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              
              <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                Page {{ historyPagination.current_page }} of {{ historyPagination.last_page }}
              </span>
              
              <button
                @click="changeHistoryPage(historyPagination.current_page + 1)"
                :disabled="historyPagination.current_page === historyPagination.last_page"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { learningService } from '../services/learning'

const router = useRouter()
const user = ref(authService.getUser())

// Progress state
const progress = ref(null)
const progressLoading = ref(true)

// History state
const history = ref([])
const historyPagination = ref(null)
const currentHistoryPage = ref(1)
const historyLoading = ref(true)
const historyError = ref('')

onMounted(async () => {
  await Promise.all([loadProgress(), loadHistory()])
})

const loadProgress = async () => {
  progressLoading.value = true
  try {
    const result = await learningService.getProgress()
    progress.value = result.data
  } catch (error) {
    console.error('Failed to load progress:', error)
    // Don't block the page, just log the error
    progress.value = {
      notes_generated: 0,
      quizzes_generated: 0,
      active_tasks: 0,
      completed_tasks: 0,
      bookmarks: 0,
    }
  } finally {
    progressLoading.value = false
  }
}

const loadHistory = async (page = 1) => {
  historyLoading.value = true
  historyError.value = ''
  try {
    const result = await learningService.getHistory({ page, per_page: 10 })
    history.value = result.data
    historyPagination.value = result.pagination
    currentHistoryPage.value = page
  } catch (error) {
    console.error('Failed to load history:', error)
    historyError.value = error.response?.data?.message || 'Failed to load learning history. Please try again.'
  } finally {
    historyLoading.value = false
  }
}

const changeHistoryPage = (page) => {
  if (page >= 1 && page <= historyPagination.value.last_page) {
    loadHistory(page)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return 'Today'
  if (diffDays === 2) return 'Yesterday'
  if (diffDays <= 7) return `${diffDays - 1} days ago`
  
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
