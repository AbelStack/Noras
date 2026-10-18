<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="navigationItems"
  >
    <div class="p-8">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="py-4">
        <SkeletonLoader type="dashboard" />
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Page Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Bookmarked Materials -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Saved Materials</p>
          <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.materials || 0 }}</p>
        </div>

        <!-- Notes Generated -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">AI Notes Generated</p>
          <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.notes || 0 }}</p>
          <p class="text-xs text-gray-500">study summaries</p>
        </div>

        <!-- Quizzes Taken -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Quizzes Completed</p>
          <p class="text-4xl font-bold text-accent-500 mb-1">{{ stats.quizzes || 0 }}</p>
          <div class="flex items-center justify-between text-xs mt-1">
            <span v-if="stats.avgScore" class="text-gray-500 font-medium">Avg. {{ stats.avgScore }}%</span>
            <span v-else class="text-gray-400">Track progress</span>
            <router-link to="/quizzes" class="text-primary-600 hover:text-primary-700 font-semibold hover:underline">Take quiz →</router-link>
          </div>
        </div>

        <!-- Study Progress -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Study Streak</p>
          <p class="text-2xl font-bold text-gray-900 mb-1">{{ stats.progress || 0 }} days</p>
          <p class="text-xs text-gray-500">Keep it up!</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Study Activity (Donut Chart) -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Study Activity Distribution</h3>
          <div class="flex flex-col items-center">
            <!-- Donut Chart Placeholder -->
            <div class="relative w-64 h-64">
              <svg viewBox="0 0 200 200" class="w-full h-full">
                <!-- Reading (Blue) -->
                <circle
                  cx="100"
                  cy="100"
                  r="80"
                  fill="none"
                  stroke="#2E5CCC"
                  stroke-width="40"
                  stroke-dasharray="251.2 251.2"
                  stroke-dashoffset="0"
                  transform="rotate(-90 100 100)"
                />
                <!-- Quizzes (Yellow) -->
                <circle
                  cx="100"
                  cy="100"
                  r="80"
                  fill="none"
                  stroke="#FDB022"
                  stroke-width="40"
                  stroke-dasharray="125.6 376.8"
                  stroke-dashoffset="-251.2"
                  transform="rotate(-90 100 100)"
                />
              </svg>
            </div>
            <!-- Legend -->
            <div class="flex items-center space-x-6 mt-4">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-primary-500 rounded"></div>
                <span class="text-sm text-gray-700">Reading Materials</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-accent-500 rounded"></div>
                <span class="text-sm text-gray-700">Taking Quizzes</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700">Creating Notes</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Weekly Study Hours (Line Chart) -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Study Hours — This Week</h3>
          <div class="h-64 flex items-end justify-between space-x-2">
            <!-- Simple line chart placeholder -->
            <div class="relative w-full h-full bg-gray-50 rounded">
              <svg viewBox="0 0 400 200" class="w-full h-full" preserveAspectRatio="none">
                <polyline
                  points="0,180 50,160 100,140 150,150 200,120 250,100 300,80 350,60 400,40"
                  fill="none"
                  stroke="#2E5CCC"
                  stroke-width="2"
                />
                <circle cx="400" cy="40" r="4" fill="#2E5CCC" />
              </svg>
              <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 pb-2 text-xs text-gray-500">
                <span>Mon</span>
                <span>Wed</span>
                <span>Fri</span>
              </div>
            </div>
          </div>
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
import { authService } from '../services/auth'
import api from '../services/api'
import { studentNavigationItems } from '../config/navigation'

const router = useRouter()
const user = authService.getUser()
const isLoading = ref(true)
const stats = ref({
  materials: 0,
  notes: 0,
  quizzes: 0,
  avgScore: 0,
  progress: 0,
})

const navigationItems = studentNavigationItems

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await api.get('/learning/progress')
    if (response.data.status === 'success' || response.data.success) {
      const data = response.data.data
      stats.value = {
        materials: data.bookmarks || 0,
        notes: data.notes_generated || 0,
        quizzes: data.quizzes_completed !== undefined ? data.quizzes_completed : (data.quizzes_generated || 0),
        avgScore: data.average_quiz_score || 0,
        progress: data.progress_percentage || 0,
      }
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
