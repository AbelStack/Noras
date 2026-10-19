<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-8">
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
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Learning Progress</h1>
          <p class="text-sm text-gray-600 mt-1">Track your learning activities, completed tasks, and achievements</p>
        </div>
      </div>

      <!-- Progress Summary Cards -->
      <div v-if="progressLoading">
        <SkeletonLoader type="stats" :count="5" />
      </div>

      <div v-else-if="progress" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Notes Generated / Available -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Study Notes</span>
            <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
              </svg>
            </span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ progress.notes_available ?? progress.notes_generated ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-gray-500">Available notes</p>
        </div>

        <!-- Quizzes Available -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Quizzes</span>
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ progress.quizzes_available ?? progress.quizzes_generated ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-gray-500">Available quizzes</p>
        </div>

        <!-- Active Tasks -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Tasks</span>
            <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ progress.active_tasks }}</p>
          <p class="mt-0.5 text-xs text-gray-500">Tasks in progress</p>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Completed</span>
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ progress.completed_tasks }}</p>
          <p class="mt-0.5 text-xs text-gray-500">Finished tasks</p>
        </div>

        <!-- Bookmarks -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Bookmarks</span>
            <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
              </svg>
            </span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ progress.bookmarks }}</p>
          <p class="mt-0.5 text-xs text-gray-500">Saved resources</p>
        </div>
      </div>

      <!-- Learning History Section -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Learning History</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Recent study notes and quizzes in your curriculum</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-if="historyError" class="p-6">
          <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
            <p class="text-sm text-red-700 font-medium">{{ historyError }}</p>
            <button
              @click="loadHistory(currentHistoryPage)"
              class="text-sm text-red-700 hover:text-red-800 underline font-medium"
            >
              Try Again
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="historyLoading" class="p-6">
          <SkeletonLoader type="tasks" :count="5" />
        </div>

        <!-- Empty State -->
        <div v-else-if="history.length === 0" class="p-12 text-center">
          <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-400 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-base font-bold text-gray-900 mb-1">No activity yet</h3>
          <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
            Review course study notes and practice quizzes from your curriculum to build your learning progress.
          </p>
          <div class="flex items-center justify-center gap-3 flex-wrap">
            <router-link
              to="/notes"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-xs transition-colors"
            >
              Study Notes
            </router-link>
            <router-link
              to="/quizzes"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              Practice Quizzes
            </router-link>
            <router-link
              to="/departments"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              Explore Materials
            </router-link>
          </div>
        </div>

        <!-- History Timeline -->
        <div v-else class="p-6">
          <div class="space-y-3">
            <div
              v-for="item in history"
              :key="`${item.type}-${item.id}`"
              class="flex items-start justify-between p-4 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/60 transition-all gap-4"
            >
              <div class="flex items-start gap-3.5 min-w-0">
                <!-- Type Icon -->
                <div class="flex-shrink-0 mt-0.5">
                  <span 
                    :class="[
                      'inline-flex items-center justify-center w-9 h-9 rounded-xl',
                      item.type === 'note' ? 'bg-primary-50 text-primary-600' : 'bg-emerald-50 text-emerald-600'
                    ]"
                  >
                    <svg v-if="item.type === 'note'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                      <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                  </span>
                </div>

                <!-- Content -->
                <div class="min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span 
                      :class="[
                        'px-2 py-0.5 rounded-md text-xs font-semibold',
                        item.type === 'note' ? 'bg-primary-50 text-primary-700' : 'bg-emerald-50 text-emerald-700'
                      ]"
                    >
                      {{ item.type === 'note' ? 'Study Note' : 'Practice Quiz' }}
                    </span>
                    <span class="text-xs text-gray-400">
                      {{ formatDate(item.created_at) }}
                    </span>
                  </div>

                  <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate">
                    {{ item.course_material?.title || 'Course Material' }}
                  </h3>
                </div>
              </div>

              <!-- Action Link -->
              <div class="flex-shrink-0 self-center">
                <router-link
                  :to="item.type === 'note' ? `/notes/${item.id}` : `/quizzes/${item.id}`"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl text-primary-600 hover:text-primary-700 hover:bg-primary-50 transition-colors"
                >
                  <span>View {{ item.type === 'note' ? 'Notes' : 'Quiz' }}</span>
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="historyPagination && historyPagination.last_page > 1" class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
            <button
              @click="changeHistoryPage(historyPagination.current_page - 1)"
              :disabled="historyPagination.current_page === 1"
              class="px-4 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
            >
              Previous
            </button>
            
            <span class="text-xs sm:text-sm text-gray-500">
              Page {{ historyPagination.current_page }} of {{ historyPagination.last_page }}
            </span>
            
            <button
              @click="changeHistoryPage(historyPagination.current_page + 1)"
              :disabled="historyPagination.current_page === historyPagination.last_page"
              class="px-4 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { learningService } from '../services/learning'
import { studentNavigationItems } from '../config/navigation'

const router = useRouter()

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
</script>
