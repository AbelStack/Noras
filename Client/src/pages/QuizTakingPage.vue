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
          to="/quizzes"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Quizzes
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading quiz...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <div class="mt-4 space-x-4">
          <button
            @click="loadQuiz"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Try Again
          </button>
          <router-link
            to="/quizzes"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            Back to Library
          </router-link>
        </div>
      </div>

      <!-- Quiz Content -->
      <div v-else-if="quiz && questions.length > 0" class="space-y-6">
        <!-- Quiz Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <h1 class="text-2xl font-bold text-gray-900">
                  {{ material?.title || 'Quiz' }}
                </h1>
              </div>

              <div class="flex items-center space-x-4 text-sm text-gray-500 mb-3">
                <span>{{ questions.length }} Questions</span>
                <span>•</span>
                <span>Generated {{ formatDate(quiz.created_at) }}</span>
              </div>

              <!-- Security Warning -->
              <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800 mb-1">
                      Study Mode (No Grading)
                    </p>
                    <p class="text-sm text-yellow-700">
                      This is a practice quiz for studying. Answers and explanations are shown immediately to help you learn. This quiz does not track attempts or provide a score.
                    </p>
                  </div>
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

        <!-- Question Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}</h2>
            <div class="flex space-x-2">
              <button
                @click="previousQuestion"
                :disabled="currentQuestionIndex === 0"
                class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ← Previous
              </button>
              <button
                @click="nextQuestion"
                :disabled="currentQuestionIndex === questions.length - 1"
                class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next →
              </button>
            </div>
          </div>

          <!-- Question Progress Dots -->
          <div class="flex space-x-2 overflow-x-auto pb-2">
            <button
              v-for="(q, index) in questions"
              :key="q.id"
              @click="goToQuestion(index)"
              :class="[
                'w-8 h-8 rounded-full text-sm font-medium transition-colors flex-shrink-0',
                index === currentQuestionIndex
                  ? 'bg-blue-600 text-white'
                  : userAnswers[q.id]
                  ? 'bg-green-100 text-green-700 border border-green-300'
                  : 'bg-gray-100 text-gray-600 border border-gray-300'
              ]"
            >
              {{ index + 1 }}
            </button>
          </div>
        </div>

        <!-- Current Question -->
        <div v-if="currentQuestion" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-xl font-semibold text-gray-900 mb-6">
            {{ currentQuestion.question_text }}
          </h3>

          <!-- Options -->
          <div class="space-y-3">
            <button
              v-for="option in currentQuestion.options"
              :key="option.id"
              @click="selectAnswer(option.id)"
              :class="[
                'w-full text-left p-4 rounded-lg border-2 transition-all',
                userAnswers[currentQuestion.id] === option.id
                  ? showAnswers
                    ? option.is_correct
                      ? 'border-green-500 bg-green-50'
                      : 'border-red-500 bg-red-50'
                    : 'border-blue-500 bg-blue-50'
                  : showAnswers && option.is_correct
                  ? 'border-green-300 bg-green-50'
                  : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
              ]"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0 mt-1">
                  <div :class="[
                    'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                    userAnswers[currentQuestion.id] === option.id
                      ? showAnswers
                        ? option.is_correct
                          ? 'border-green-600 bg-green-600'
                          : 'border-red-600 bg-red-600'
                        : 'border-blue-600 bg-blue-600'
                      : showAnswers && option.is_correct
                      ? 'border-green-600'
                      : 'border-gray-400'
                  ]">
                    <svg v-if="userAnswers[currentQuestion.id] === option.id || (showAnswers && option.is_correct)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-3 flex-1">
                  <p class="text-gray-900 font-medium">{{ option.option_text }}</p>
                  <div v-if="showAnswers && option.is_correct" class="mt-2 text-sm text-green-700 font-medium">
                    ✓ Correct Answer
                  </div>
                  <div v-else-if="showAnswers && userAnswers[currentQuestion.id] === option.id && !option.is_correct" class="mt-2 text-sm text-red-700 font-medium">
                    ✗ Incorrect
                  </div>
                </div>
              </div>
            </button>
          </div>

          <!-- Explanation (shown after answer selection) -->
          <div v-if="showAnswers && currentQuestion.explanation && userAnswers[currentQuestion.id]" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-semibold text-blue-900 mb-2">Explanation:</p>
            <p class="text-sm text-blue-800">{{ currentQuestion.explanation }}</p>
          </div>

          <!-- Toggle Answers Button -->
          <div class="mt-6 flex justify-between items-center">
            <button
              @click="showAnswers = !showAnswers"
              class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              {{ showAnswers ? 'Hide' : 'Show' }} Correct Answers
            </button>
            <div v-if="showAnswers" class="text-sm text-gray-500">
              Answered: {{ Object.keys(userAnswers).length }} / {{ questions.length }}
            </div>
          </div>
        </div>

        <!-- Summary Card -->
        <div v-if="Object.keys(userAnswers).length === questions.length" class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl shadow-sm border border-green-200 p-6">
          <h3 class="text-xl font-bold text-gray-900 mb-2">All Questions Answered!</h3>
          <p class="text-gray-600 mb-4">
            You've answered all {{ questions.length }} questions. Review your answers and check the explanations to reinforce your learning.
          </p>
          <div class="flex space-x-4">
            <button
              @click="goToQuestion(0)"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Review from Start
            </button>
            <router-link
              to="/quizzes"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white"
            >
              Back to Library
            </router-link>
          </div>
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Quiz?</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete this quiz? This action cannot be undone.
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
            {{ isDeleting ? 'Deleting...' : 'Delete Quiz' }}
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
import { quizzesService } from '../services/quizzes'

const router = useRouter()
const route = useRoute()
const user = authService.getUser()

const quiz = ref(null)
const material = ref(null)
const questions = ref([])
const currentQuestionIndex = ref(0)
const userAnswers = ref({}) // { questionId: selectedOptionId }
const showAnswers = ref(false)
const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')
const showDeleteConfirm = ref(false)

const quizId = computed(() => parseInt(route.params.id))
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])

onMounted(() => {
  loadQuiz()
})

const loadQuiz = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await quizzesService.getQuiz(quizId.value)
    quiz.value = data.quiz
    material.value = data.material
    questions.value = data.questions.sort((a, b) => a.question_order - b.question_order)
    
    // Sort options within each question
    questions.value.forEach(q => {
      q.options = q.options.sort((a, b) => a.option_order - b.option_order)
    })
  } catch (error) {
    console.error('Failed to load quiz:', error)
    
    if (error.response?.status === 404) {
      errorMessage.value = 'Quiz not found. It may have been deleted.'
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        authService.logout()
        router.push('/login')
      }, 2000)
    } else if (error.response?.status === 403) {
      errorMessage.value = 'You do not have permission to view this quiz.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to load quiz. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

const selectAnswer = (optionId) => {
  userAnswers.value[currentQuestion.value.id] = optionId
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

const goToQuestion = (index) => {
  if (index >= 0 && index < questions.value.length) {
    currentQuestionIndex.value = index
  }
}

const handleDelete = async () => {
  isDeleting.value = true

  try {
    await quizzesService.deleteQuiz(quizId.value)
    router.push('/quizzes')
  } catch (error) {
    console.error('Failed to delete quiz:', error)
    showDeleteConfirm.value = false
    
    if (error.response?.status === 403) {
      errorMessage.value = 'You do not have permission to delete this quiz.'
    } else if (error.response?.status === 404) {
      errorMessage.value = 'Quiz not found. It may have been deleted already.'
      setTimeout(() => router.push('/quizzes'), 2000)
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to delete quiz. Please try again.'
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
