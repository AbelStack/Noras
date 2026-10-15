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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">AI Quizzes</h1>
        <p class="mt-2 text-gray-600">Your AI-generated quiz library</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading your quizzes...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadQuizzes"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="quizzes.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No quizzes yet</h3>
        <p class="mt-1 text-sm text-gray-500">Generate AI quizzes from your course materials to see them here.</p>
        <router-link
          to="/departments"
          class="mt-6 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          Browse Course Materials
        </router-link>
      </div>

      <!-- Quizzes List -->
      <div v-else>
        <div class="space-y-4">
          <div
            v-for="quiz in quizzes"
            :key="quiz.id"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                  </svg>
                  <h3 class="text-lg font-bold text-gray-900">
                    {{ quiz.material?.title || 'Untitled Material' }}
                  </h3>
                </div>

                <div class="flex items-center space-x-4 text-sm text-gray-500 mb-2">
                  <span v-if="quiz.material?.course">
                    {{ quiz.material.course.code }} - {{ quiz.material.course.name }}
                  </span>
                  <span v-if="quiz.material?.department" class="text-gray-400">•</span>
                  <span v-if="quiz.material?.department">
                    {{ quiz.material.department.name }}
                  </span>
                </div>

                <div class="flex items-center space-x-4 text-xs text-gray-400">
                  <span>{{ quiz.question_count }} questions</span>
                  <span>•</span>
                  <span>Generated {{ formatDate(quiz.created_at) }}</span>
                  <span>•</span>
                  <span>{{ quiz.ai_provider }} ({{ quiz.ai_model }})</span>
                </div>
              </div>

              <div class="flex space-x-2 ml-4">
                <router-link
                  :to="`/quizzes/${quiz.id}`"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                  Take Quiz
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-between">
          <button
            @click="loadPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>

          <span class="text-sm text-gray-600">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>

          <button
            @click="loadPage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
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
import { quizzesService } from '../services/quizzes'

const router = useRouter()
const user = authService.getUser()

const quizzes = ref([])
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
})
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(() => {
  loadQuizzes()
})

const loadQuizzes = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await quizzesService.getQuizzes(page)
    quizzes.value = data.quizzes
    pagination.value = data.pagination
  } catch (error) {
    console.error('Failed to load quizzes:', error)
    
    if (error.response?.status === 401) {
      errorMessage.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        authService.logout()
        router.push('/login')
      }, 2000)
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to load quizzes. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

const loadPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadQuizzes(page)
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
