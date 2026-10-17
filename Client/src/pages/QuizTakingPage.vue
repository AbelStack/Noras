<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-4xl mx-auto space-y-6">
      <!-- Back Button -->
      <div>
        <button
          @click="$router.push('/quizzes')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Quizzes
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="quiz" />
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
            @click="loadQuiz"
            class="text-sm text-red-700 hover:text-red-800 underline font-medium"
          >
            Try Again
          </button>
          <button
            @click="$router.push('/quizzes')"
            class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-medium"
          >
            Back to Library
          </button>
        </div>
      </div>

      <!-- Quiz Content -->
      <div v-else-if="quiz && questions.length > 0" class="space-y-6">
        <!-- Quiz Header -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex-shrink-0">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                  </svg>
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                  {{ material?.title || 'Quiz' }}
                </h1>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-gray-500 ml-10 mb-3">
                <span class="font-medium text-emerald-600">{{ questions.length }} Questions</span>
                <span>•</span>
                <span>Generated {{ formatDate(quiz.created_at) }}</span>
                <span v-if="quiz.ai_provider">•</span>
                <span v-if="quiz.ai_provider">{{ quiz.ai_provider }} ({{ quiz.ai_model }})</span>
              </div>

              <!-- Study Mode Notice -->
              <div class="mt-4 p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-xl ml-10">
                <div class="flex items-start gap-2.5">
                  <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                  <div>
                    <p class="text-xs sm:text-sm font-semibold text-emerald-900 mb-0.5">
                      Practice & Study Mode
                    </p>
                    <p class="text-xs text-emerald-800">
                      Answers and explanations are available immediately to reinforce your learning.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center ml-10 sm:ml-0 flex-shrink-0">
              <button
                type="button"
                @click="retakeQuiz"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl text-xs sm:text-sm font-semibold transition-colors cursor-pointer"
                title="Reset answers and retake this quiz"
              >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Retake Quiz</span>
              </button>

              <button
                v-if="isAdmin"
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

        <!-- Question Navigation -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base sm:text-lg font-bold text-gray-900">
              Question {{ currentQuestionIndex + 1 }} <span class="text-xs font-normal text-gray-500">of {{ questions.length }}</span>
            </h2>
            <div class="flex gap-2">
              <button
                @click="previousQuestion"
                :disabled="currentQuestionIndex === 0"
                class="px-3 py-1.5 text-xs sm:text-sm border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-gray-700 transition-colors"
              >
                ← Previous
              </button>
              <button
                @click="nextQuestion"
                :disabled="currentQuestionIndex === questions.length - 1"
                class="px-3 py-1.5 text-xs sm:text-sm border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-gray-700 transition-colors"
              >
                Next →
              </button>
            </div>
          </div>

          <!-- Question Progress Dots -->
          <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-thin">
            <button
              v-for="(q, index) in questions"
              :key="q.id"
              @click="goToQuestion(index)"
              :class="[
                'w-8 h-8 rounded-xl text-xs font-semibold transition-all flex-shrink-0 flex items-center justify-center',
                index === currentQuestionIndex
                  ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-300'
                  : userAnswers[q.id]
                  ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                  : 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'
              ]"
            >
              {{ index + 1 }}
            </button>
          </div>
        </div>

        <!-- Current Question -->
        <div v-if="currentQuestion" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-6 leading-relaxed">
            {{ currentQuestion.question_text }}
          </h3>

          <!-- Options -->
          <div class="space-y-3">
            <button
              v-for="(option, optIndex) in currentQuestion.options"
              :key="option.id"
              @click="selectAnswer(option.id)"
              :class="[
                'w-full text-left p-4 rounded-xl border-2 transition-all',
                userAnswers[currentQuestion.id] === option.id
                  ? showAnswers
                    ? option.is_correct
                      ? 'border-emerald-500 bg-emerald-50/60'
                      : 'border-red-500 bg-red-50/60'
                    : 'border-emerald-500 bg-emerald-50/40'
                  : showAnswers && option.is_correct
                  ? 'border-emerald-400 bg-emerald-50/40'
                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/80'
              ]"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                  <div :class="[
                    'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors',
                    userAnswers[currentQuestion.id] === option.id
                      ? showAnswers
                        ? option.is_correct
                          ? 'border-emerald-600 bg-emerald-600'
                          : 'border-red-600 bg-red-600'
                        : 'border-emerald-600 bg-emerald-600'
                      : showAnswers && option.is_correct
                      ? 'border-emerald-600 bg-emerald-600'
                      : 'border-gray-300'
                  ]">
                    <svg v-if="userAnswers[currentQuestion.id] === option.id || (showAnswers && option.is_correct)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-3 flex-1">
                  <p class="text-sm sm:text-base text-gray-900 font-medium">{{ formatOptionText(option.option_text, optIndex) }}</p>
                  <div v-if="showAnswers && option.is_correct" class="mt-1 text-xs text-emerald-700 font-semibold">
                    ✓ Correct Answer
                  </div>
                  <div v-else-if="showAnswers && userAnswers[currentQuestion.id] === option.id && !option.is_correct" class="mt-1 text-xs text-red-600 font-semibold">
                    ✗ Incorrect
                  </div>
                </div>
              </div>
            </button>
          </div>

          <!-- Explanation (shown after answer selection) -->
          <div v-if="showAnswers && currentQuestion.explanation && userAnswers[currentQuestion.id]" class="mt-6 p-4 bg-primary-50/50 border border-primary-200/80 rounded-xl">
            <p class="text-xs font-bold text-primary-900 mb-1">Explanation & Source Citation:</p>
            <p class="text-xs sm:text-sm text-primary-800 leading-relaxed">{{ currentQuestion.explanation }}</p>
          </div>

          <!-- Toggle Answers Button -->
          <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap justify-between items-center gap-3">
            <button
              @click="showAnswers = !showAnswers"
              class="px-4 py-2 text-xs sm:text-sm font-medium border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 cursor-pointer"
            >
              {{ showAnswers ? 'Hide' : 'Show' }} Correct Answers
            </button>
            <div class="text-xs sm:text-sm text-gray-500 font-medium">
              Answered: {{ Object.keys(userAnswers).length }} / {{ questions.length }}
            </div>
          </div>
        </div>

        <!-- Summary Card -->
        <div v-if="Object.keys(userAnswers).length === questions.length" class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 p-6 sm:p-8 shadow-sm">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">All Questions Answered!</h3>
          <p class="text-sm text-gray-600 mb-5">
            You've answered all {{ questions.length }} questions. Review your answers and check the explanations to reinforce your learning.
          </p>
          <div class="flex flex-wrap gap-3">
            <button
              @click="retakeQuiz"
              class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-semibold text-sm shadow-sm transition-colors cursor-pointer"
            >
              Retake Quiz
            </button>
            <button
              @click="goToQuestion(0)"
              class="px-5 py-2.5 border border-emerald-300 bg-white rounded-xl hover:bg-emerald-50 text-emerald-800 font-medium text-sm shadow-sm transition-colors cursor-pointer"
            >
              Review from Start
            </button>
            <router-link
              to="/quizzes"
              class="px-5 py-2.5 border border-gray-300 bg-white rounded-xl hover:bg-gray-50 text-gray-700 font-medium text-sm shadow-sm transition-colors"
            >
              Back to Library
            </router-link>
          </div>
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
import { quizzesService } from '../services/quizzes'
import { authService } from '../services/auth'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'

const router = useRouter()
const route = useRoute()
const notify = useNotification()

const currentUser = authService.getUser()
const isAdmin = computed(() => currentUser?.role === 'admin')

const quiz = ref(null)
const material = ref(null)
const questions = ref([])
const currentQuestionIndex = ref(0)
const userAnswers = ref({}) // { questionId: selectedOptionId }
const showAnswers = ref(false)
const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')

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

const retakeQuiz = () => {
  userAnswers.value = {}
  currentQuestionIndex.value = 0
  showAnswers.value = false
  notify.info('Quiz reset. Good luck!')
}

const formatOptionText = (optionText, index) => {
  if (!optionText) return ''
  const trimmed = optionText.trim()
  if (/^[A-D]\.\s*/i.test(trimmed)) {
    return trimmed
  }
  const prefix = String.fromCharCode(65 + index)
  return `${prefix}. ${trimmed}`
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

const confirmDelete = async () => {
  const confirmed = await notify.confirm({
    title: 'Delete Quiz?',
    message: 'Are you sure you want to delete this quiz? This action cannot be undone.',
    confirmText: 'Delete Quiz',
    cancelText: 'Cancel',
    type: 'danger',
  })

  if (!confirmed) return
  await handleDelete()
}

const handleDelete = async () => {
  isDeleting.value = true

  try {
    await quizzesService.deleteQuiz(quizId.value)
    notify.success('Quiz deleted successfully.')
    router.push('/quizzes')
  } catch (error) {
    console.error('Failed to delete quiz:', error)
    
    if (error.response?.status === 403) {
      notify.error('You do not have permission to delete this quiz.')
    } else if (error.response?.status === 404) {
      notify.error('Quiz not found. It may have been deleted already.')
      setTimeout(() => router.push('/quizzes'), 2000)
    } else {
      notify.error(error.response?.data?.message || 'Failed to delete quiz. Please try again.')
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
</script>
