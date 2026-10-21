<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          v-if="selectedYear"
          @click="selectedYear = null"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Academic Years
        </button>
        <button 
          v-else
          @click="$router.push('/dashboard')"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
              <template v-if="selectedYear">
                Year {{ selectedYear }} AI Quizzes
              </template>
              <template v-else>
                AI Quizzes Library
              </template>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">
              <template v-if="selectedYear">
                {{ userDepartment?.name || 'Department' }} &bull; Semester 1 &amp; Semester 2 practice quizzes
              </template>
              <template v-else>
                Select an academic year to explore AI-generated quizzes and test your knowledge
              </template>
            </p>
          </div>

          <!-- Quick Toggle for All Quizzes -->
          <div v-if="!isLoading && quizzes.length > 0" class="flex items-center gap-2">
            <button
              v-if="selectedYear"
              @click="selectedYear = null"
              class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors cursor-pointer"
            >
              View All Years
            </button>
            <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-primary-50 text-primary-700 border border-primary-100">
              {{ quizzes.length }} Total Quizzes
            </span>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="cards" :count="6" />
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
          @click="loadQuizzes"
          class="text-sm text-red-700 hover:text-red-800 underline font-medium cursor-pointer"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State (No quizzes at all) -->
      <div v-else-if="quizzes.length === 0" class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center shadow-sm min-h-[440px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4 border border-primary-100">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No quizzes available yet</h3>
        <p class="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto mb-5">Explore course materials to find published quizzes for your academic program.</p>
        <router-link
          to="/departments"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-semibold text-xs sm:text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-2xs transition-colors"
        >
          Browse Course Materials
        </router-link>
      </div>

      <!-- MODE 1: Academic Years Selection Cards (when selectedYear === null) -->
      <div v-else-if="selectedYear === null" class="space-y-6">
        <!-- Enrolled Department Info -->
        <div 
          v-if="userDepartment"
          class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs"
        >
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-primary-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <span class="text-xs font-semibold text-primary-700 uppercase tracking-wider">Department Curriculum</span>
              <h3 class="text-base sm:text-lg font-bold text-gray-900 mt-0.5">
                {{ userDepartment.name }}
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">
                Browse quizzes organized by study year (Year 2 to Year {{ departmentDuration }})
              </p>
            </div>
          </div>
          <div class="text-xs font-medium text-primary-700 bg-white/80 border border-blue-100 rounded-xl px-4 py-2 self-start sm:self-auto">
            {{ departmentDuration }}-Year Academic Program
          </div>
        </div>

        <!-- Academic Years Grid -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Academic Years</h2>
            <span class="text-xs text-gray-500 font-medium">Select a year to view quizzes</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="yr in yearCards"
              :key="yr.year"
              @click="selectedYear = yr.year"
              class="bg-white rounded-2xl border border-gray-200 hover:border-primary-400 hover:ring-2 hover:ring-primary-100 p-6 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between cursor-pointer"
            >
              <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-black text-base border border-primary-100 group-hover:bg-primary-600 group-hover:text-white transition-colors shadow-2xs">
                      Y{{ yr.year }}
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors">
                        Year {{ yr.year }}
                      </h3>
                      <p class="text-xs text-gray-500 font-medium">
                        {{ userDepartment?.name || 'Curriculum' }}
                      </p>
                    </div>
                  </div>
                  <span 
                    class="text-xs font-semibold px-2.5 py-0.5 rounded-md"
                    :class="yr.quizzesCount > 0 ? 'bg-primary-50 text-primary-700 border border-primary-100' : 'bg-gray-100 text-gray-600'"
                  >
                    {{ yr.quizzesCount }} {{ yr.quizzesCount === 1 ? 'Quiz' : 'Quizzes' }}
                  </span>
                </div>

                <p class="text-xs text-gray-600 mt-3 line-clamp-2 leading-relaxed">
                  Interactive quizzes and practice question sets for Year {{ yr.year }} (Semester 1 &amp; 2).
                </p>

                <div class="mt-4 flex flex-wrap gap-1.5">
                  <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">Semester 1</span>
                  <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">Semester 2</span>
                </div>
              </div>

              <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-primary-600 group-hover:text-primary-700">
                <span>Explore Quizzes</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODE 2: Year Detail View (Filtered by Semester & Course) -->
      <div v-else class="space-y-6">
        <!-- Year Switcher & Semester Filters Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-sm space-y-4">
          <!-- Top row: Year Switcher & Search -->
          <div class="flex flex-col md:flex-row gap-3 items-start md:items-center justify-between">
            <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
              <span class="text-xs font-semibold text-gray-500 mr-1.5">Academic Year:</span>
              <button
                v-for="yr in yearRange"
                :key="yr"
                @click="selectedYear = yr"
                :class="[
                  'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition-all cursor-pointer',
                  selectedYear === yr
                    ? 'bg-primary-600 text-white shadow-xs'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                Year {{ yr }}
              </button>
            </div>

            <!-- Quiz & Course Search -->
            <div class="relative w-full md:w-72">
              <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search quizzes or courses..."
                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
              />
            </div>
          </div>

          <!-- Bottom row: Semester Tabs (All, Semester 1, Semester 2) -->
          <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl">
              <button
                @click="selectedSemester = 'all'"
                :class="[
                  'px-4 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer',
                  selectedSemester === 'all'
                    ? 'bg-white text-primary-600 shadow-xs'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                All Semesters
              </button>
              <button
                @click="selectedSemester = '1'"
                :class="[
                  'px-4 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer',
                  selectedSemester === '1'
                    ? 'bg-white text-primary-600 shadow-xs'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                Semester 1
              </button>
              <button
                @click="selectedSemester = '2'"
                :class="[
                  'px-4 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer',
                  selectedSemester === '2'
                    ? 'bg-white text-primary-600 shadow-xs'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                Semester 2
              </button>
            </div>

            <div class="text-xs text-gray-500 font-medium">
              Showing {{ filteredQuizzes.length }} {{ filteredQuizzes.length === 1 ? 'quiz' : 'quizzes' }}
            </div>
          </div>
        </div>

        <!-- Quizzes List for Selected Year/Semester -->
        <div v-if="filteredQuizzes.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
          </div>
          <h3 class="text-base font-bold text-gray-900 mb-1">
            No quizzes found for Year {{ selectedYear }}{{ selectedSemester !== 'all' ? `, Semester ${selectedSemester}` : '' }}
          </h3>
          <p class="text-xs text-gray-500 max-w-sm mx-auto mb-4">
            {{ searchQuery ? 'Try adjusting your search keywords.' : 'Quizzes generated from course materials in this curriculum year will appear here.' }}
          </p>
          <router-link
            to="/departments"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-xs font-semibold shadow-xs transition-colors"
          >
            Browse Materials
          </router-link>
        </div>

        <div v-else class="space-y-4 min-h-[460px]">
          <div
            v-for="quiz in filteredQuizzes"
            :key="quiz.id"
            class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-primary-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 border border-primary-100 flex-shrink-0">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                  </svg>
                </span>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                  {{ quiz.material?.title || 'Untitled Material' }}
                </h3>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-gray-500 ml-10 mb-2">
                <span v-if="quiz.material?.course" class="font-medium text-gray-700">
                  {{ quiz.material.course.code }} - {{ quiz.material.course.name }}
                </span>
                <span v-if="quiz.material?.study_year" class="text-gray-300">•</span>
                <span v-if="quiz.material?.study_year" class="font-semibold text-primary-700">
                  Year {{ quiz.material.study_year }}
                </span>
                <span v-if="quiz.material?.semester" class="text-gray-300">•</span>
                <span v-if="quiz.material?.semester" class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                  Semester {{ quiz.material.semester }}
                </span>
                <span v-if="quiz.material?.department && !quiz.material?.course" class="text-gray-300">•</span>
                <span v-if="quiz.material?.department && !quiz.material?.course">
                  {{ quiz.material.department.name }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-2 text-xs text-gray-400 ml-10">
                <span class="font-semibold text-primary-600">{{ quiz.question_count }} questions</span>
                <span>•</span>
                <span>Generated {{ formatDate(quiz.created_at) }}</span>
                <!-- Admin-only AI metadata (hidden from students) -->
                <template v-if="isAdmin">
                  <span v-if="quiz.ai_provider">•</span>
                  <span v-if="quiz.ai_provider" class="text-amber-700 font-mono">{{ quiz.ai_provider }} ({{ quiz.ai_model }})</span>
                </template>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex items-center gap-2 sm:self-center ml-10 sm:ml-0 flex-shrink-0">
              <router-link
                :to="`/quizzes/${quiz.id}`"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors text-xs sm:text-sm font-semibold shadow-xs cursor-pointer"
              >
                <span>Take Quiz</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
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

const router = useRouter()
const route = useRoute()

const user = computed(() => authService.getUser())
const isAdmin = computed(() => authService.isAdmin())
const userDepartment = computed(() => user.value?.department || null)

// Department duration: defaults to 4 if not set
const departmentDuration = computed(() => {
  const d = user.value?.department?.total_years
  return d ? Math.max(d, 3) : 4
})

// Academic Year Range: Year 2 up to department duration
const yearRange = computed(() => {
  const years = []
  const maxYear = departmentDuration.value
  for (let y = 2; y <= maxYear; y++) {
    years.push(y)
  }
  return years
})

const quizzes = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

// Selected filters
const selectedYear = ref(route.query.year ? parseInt(route.query.year) : null)
const selectedSemester = ref(route.query.semester ? String(route.query.semester) : 'all')
const searchQuery = ref('')

onMounted(() => {
  loadQuizzes()
})

const loadQuizzes = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    // Fetch quizzes with high per_page to organize by curriculum years locally
    const data = await quizzesService.getQuizzes(1, 100)
    quizzes.value = data.quizzes || []
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

// Compute quiz count for each year card
const yearCards = computed(() => {
  return yearRange.value.map(yr => {
    const count = quizzes.value.filter(q => {
      const qYear = Number(q.material?.study_year)
      if (qYear) return qYear === yr
      return yr === 2 // fallback to Year 2 if study_year not set
    }).length

    return {
      year: yr,
      quizzesCount: count,
    }
  })
})

// Filter quizzes for the selected year and semester
const filteredQuizzes = computed(() => {
  if (!selectedYear.value) return quizzes.value

  return quizzes.value.filter(quiz => {
    // Year filter (if quiz has study_year, match it; if undefined, let it appear under Year 2)
    const qYear = Number(quiz.material?.study_year)
    if (qYear && qYear !== selectedYear.value) {
      return false
    }
    if (!qYear && selectedYear.value !== 2) {
      return false
    }

    // Semester filter
    if (selectedSemester.value !== 'all') {
      const qSemester = String(quiz.material?.semester || '1')
      if (qSemester !== selectedSemester.value) {
        return false
      }
    }

    // Search query
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim()
      const title = (quiz.material?.title || '').toLowerCase()
      const courseCode = (quiz.material?.course?.code || '').toLowerCase()
      const courseName = (quiz.material?.course?.name || quiz.material?.course?.title || '').toLowerCase()
      return title.includes(query) || courseCode.includes(query) || courseName.includes(query)
    }

    return true
  })
})

const formatDate = (dateString) => {
  if (!dateString) return ''
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
