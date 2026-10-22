<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="navigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="py-4">
        <SkeletonLoader type="dashboard" />
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Page Header -->
        <div class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user?.name }}!</h1>
            <p class="text-gray-600 mt-1">
              <span v-if="user?.department">
                {{ user.department.name }} &bull; {{ user.university?.name || 'Campus' }}
              </span>
              <span v-else>Continue your learning journey</span>
            </p>
          </div>

          <router-link
            v-if="user?.department_id"
            :to="`/departments/${user.department_id}/courses`"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium text-sm shadow-sm transition-colors self-start sm:self-auto"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>My Department Courses &rarr;</span>
          </router-link>
        </div>

        <!-- Stats Cards (Compact 2x2 Grid on Mobile, 4 Cols on Desktop) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5 mb-5 sm:mb-6">
          <!-- Saved Materials -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-3.5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 line-clamp-1">Saved Materials</p>
              <p class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ stats.materials }}</p>
            </div>
            <div class="flex items-center justify-between text-[11px] sm:text-xs mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-gray-100">
              <span class="text-gray-400 hidden sm:inline">Bookmarked resources</span>
              <router-link to="/bookmarks" class="text-primary-600 hover:text-primary-700 font-semibold hover:underline">
                View saved &rarr;
              </router-link>
            </div>
          </div>

          <!-- AI Study Notes Available -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-3.5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 line-clamp-1">AI Study Notes</p>
              <p class="text-2xl sm:text-4xl font-extrabold text-primary-600 tracking-tight">{{ stats.notes }}</p>
            </div>
            <div class="flex items-center justify-between text-[11px] sm:text-xs mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-gray-100">
              <span class="text-gray-400 hidden sm:inline">Study summaries</span>
              <router-link to="/notes" class="text-primary-600 hover:text-primary-700 font-semibold hover:underline">
                Explore notes &rarr;
              </router-link>
            </div>
          </div>

          <!-- Quizzes Completed -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-3.5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 line-clamp-1">Quizzes Completed</p>
              <p class="text-2xl sm:text-4xl font-extrabold text-accent-500 tracking-tight">{{ stats.quizzes }}</p>
            </div>
            <div class="flex items-center justify-between text-[11px] sm:text-xs mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-gray-100">
              <span v-if="stats.avgScore" class="text-gray-500 font-medium">Avg. {{ stats.avgScore }}%</span>
              <span v-else class="text-gray-400">Track scores</span>
              <router-link to="/quizzes" class="text-primary-600 hover:text-primary-700 font-semibold hover:underline">
                Take quiz &rarr;
              </router-link>
            </div>
          </div>

          <!-- Study Streak (Active Consecutive Days) -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-3.5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 line-clamp-1">Study Streak</p>
              <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                <span class="text-accent-500 font-black">{{ stats.streak }}</span> {{ stats.streak === 1 ? 'day' : 'days' }}
              </p>
            </div>
            <div class="flex items-center justify-between text-[11px] sm:text-xs mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-gray-100">
              <span class="text-[11px] sm:text-xs text-gray-500 font-medium">
                {{ stats.streak > 0 ? '🔥 Active!' : 'Study today!' }}
              </span>
              <span class="text-[10px] sm:text-[11px] font-bold text-emerald-600 bg-emerald-50 px-1.5 sm:px-2 py-0.5 rounded-full border border-emerald-100">
                Daily Goal
              </span>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Study Activity Distribution (Full 360-degree circular Donut Chart) -->
          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900">Study Activity Distribution</h3>
                <p class="text-xs text-gray-500">Breakdown of your learning content &amp; quiz completions</p>
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 bg-primary-50 text-primary-700 rounded-lg border border-primary-100">
                100% Tracked
              </span>
            </div>

            <div class="flex flex-col items-center justify-center py-4">
              <!-- Full Circle Donut Chart -->
              <div class="relative w-56 h-56 flex items-center justify-center">
                <svg viewBox="0 0 200 200" class="w-full h-full transform -rotate-90">
                  <!-- Base Track (Light Background) -->
                  <circle
                    cx="100"
                    cy="100"
                    r="75"
                    fill="none"
                    stroke="#EEF2FF"
                    stroke-width="30"
                  />
                  <!-- Segment 1: Study Notes (Brand Blue #2E5CCC) -->
                  <circle
                    cx="100"
                    cy="100"
                    r="75"
                    fill="none"
                    stroke="#2E5CCC"
                    stroke-width="30"
                    :stroke-dasharray="`${chartSegments.notesLen} 471.24`"
                    :stroke-dashoffset="chartSegments.notesOffset"
                    class="transition-all duration-700 ease-out"
                  />
                  <!-- Segment 2: Quizzes (Brand Yellow #FDB022) -->
                  <circle
                    cx="100"
                    cy="100"
                    r="75"
                    fill="none"
                    stroke="#FDB022"
                    stroke-width="30"
                    :stroke-dasharray="`${chartSegments.quizzesLen} 471.24`"
                    :stroke-dashoffset="chartSegments.quizzesOffset"
                    class="transition-all duration-700 ease-out"
                  />
                  <!-- Segment 3: Saved Materials (Deep Brand Blue #1D3A80) -->
                  <circle
                    v-if="chartSegments.materialsLen > 0"
                    cx="100"
                    cy="100"
                    r="75"
                    fill="none"
                    stroke="#1D3A80"
                    stroke-width="30"
                    :stroke-dasharray="`${chartSegments.materialsLen} 471.24`"
                    :stroke-dashoffset="chartSegments.materialsOffset"
                    class="transition-all duration-700 ease-out"
                  />
                </svg>

                <!-- Centered Stat Counter inside Donut -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                  <span class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none">
                    {{ totalActivityCount }}
                  </span>
                  <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mt-1">
                    Activities
                  </span>
                </div>
              </div>

              <!-- Complete Dynamic Legend -->
              <div class="flex flex-wrap items-center justify-center gap-5 mt-6 pt-4 border-t border-gray-100 w-full">
                <div class="flex items-center space-x-2">
                  <div class="w-3.5 h-3.5 bg-primary-500 rounded-md shadow-2xs"></div>
                  <span class="text-xs font-semibold text-gray-700">Study Notes ({{ stats.notes }})</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="w-3.5 h-3.5 bg-accent-500 rounded-md shadow-2xs"></div>
                  <span class="text-xs font-semibold text-gray-700">Quizzes Completed ({{ stats.quizzes }})</span>
                </div>
                <div v-if="stats.materials > 0" class="flex items-center space-x-2">
                  <div class="w-3.5 h-3.5 bg-primary-700 rounded-md shadow-2xs"></div>
                  <span class="text-xs font-semibold text-gray-700">Saved Materials ({{ stats.materials }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Weekly Study Hours (Line Chart) -->
          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-base sm:text-lg font-bold text-gray-900">Study Hours &mdash; This Week</h3>
                  <p class="text-xs text-gray-500">Your daily engagement and learning activity hours</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-accent-600 rounded-lg border border-amber-200">
                  {{ stats.streak > 0 ? stats.streak + ' Day Streak' : 'Start Today' }}
                </span>
              </div>

              <div class="h-56 flex items-end justify-between space-x-2 pt-2">
                <div class="relative w-full h-full bg-gradient-to-b from-blue-50/40 to-white rounded-xl border border-gray-100 p-2">
                  <svg viewBox="0 0 400 200" class="w-full h-full" preserveAspectRatio="none">
                    <polyline
                      points="0,180 50,160 100,135 150,145 200,110 250,90 300,75 350,55 400,35"
                      fill="none"
                      stroke="#2E5CCC"
                      stroke-width="3"
                    />
                    <circle cx="400" cy="35" r="5" fill="#FDB022" stroke="#2E5CCC" stroke-width="2" />
                  </svg>
                  <div class="absolute bottom-1 left-0 right-0 flex justify-between px-4 text-xs font-medium text-gray-400">
                    <span>Mon</span>
                    <span>Wed</span>
                    <span>Fri</span>
                    <span>Sun</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
              <span>Goal: 5 study days per week</span>
              <span class="font-bold text-primary-600">{{ Math.min(stats.streak, 5) }}/5 completed</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { authService } from '../services/auth'
import api from '../services/api'
import { studentNavigationItems } from '../config/navigation'

const router = useRouter()
const user = computed(() => authService.getUser())
const isLoading = ref(true)

const stats = ref({
  materials: 0,
  notes: 0,
  quizzes: 0,
  avgScore: 0,
  streak: 0,
})

const navigationItems = studentNavigationItems

// Total items count for center of donut
const totalActivityCount = computed(() => {
  return (stats.value.notes || 0) + (stats.value.quizzes || 0) + (stats.value.materials || 0)
})

// Full 360-degree circle slice calculation (Circumference C = 2 * PI * 75 = 471.24)
const chartSegments = computed(() => {
  const C = 471.24
  const notes = stats.value.notes || 0
  const quizzes = stats.value.quizzes || 0
  const materials = stats.value.materials || 0
  const total = notes + quizzes + materials

  if (total === 0) {
    // Default 100% full circle when no activities exist yet (60% Blue, 40% Yellow)
    const len1 = C * 0.6
    const len2 = C * 0.4
    return {
      notesLen: len1,
      quizzesLen: len2,
      materialsLen: 0,
      notesOffset: 0,
      quizzesOffset: -len1,
      materialsOffset: 0,
    }
  }

  const pNotes = notes / total
  const pQuizzes = quizzes / total
  const lenNotes = pNotes * C
  const lenQuizzes = pQuizzes * C
  const lenMaterials = C - lenNotes - lenQuizzes

  return {
    notesLen: lenNotes,
    quizzesLen: lenQuizzes,
    materialsLen: Math.max(0, lenMaterials),
    notesOffset: 0,
    quizzesOffset: -lenNotes,
    materialsOffset: -(lenNotes + lenQuizzes),
  }
})

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await api.get('/learning/progress')
    if (response.data.status === 'success' || response.data.success) {
      const data = response.data.data
      stats.value = {
        materials: data.bookmarks !== undefined ? data.bookmarks : 0,
        notes: data.notes_available !== undefined ? data.notes_available : (data.notes_generated || 0),
        quizzes: data.quizzes_completed !== undefined ? data.quizzes_completed : (data.quizzes_generated || 0),
        avgScore: data.average_quiz_score || 0,
        streak: data.streak_days !== undefined ? data.streak_days : 0,
      }
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
