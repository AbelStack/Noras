<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="navigationItems"
  >
    <div class="p-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user?.name }}!</h1>
        <p class="text-gray-600 mt-1">Continue your learning journey</p>
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
          <a href="#" class="text-xs text-accent-500 hover:underline">View scores →</a>
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
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { authService } from '../services/auth'
import api from '../services/api'

const router = useRouter()
const user = authService.getUser()
const stats = ref({
  materials: 0,
  notes: 0,
  quizzes: 0,
  progress: 0,
})

const navigationItems = [
  { 
    path: '/dashboard', 
    label: 'Dashboard', 
    icon: 'DashboardIcon',
  },
  { 
    path: '/departments', 
    label: 'Browse Courses', 
    icon: 'BookIcon',
  },
  { 
    path: '/notes', 
    label: 'AI Study Notes', 
    icon: 'ProductsIcon',
  },
  { 
    path: '/quizzes', 
    label: 'AI Quizzes', 
    icon: 'ApprovalIcon',
  },
  { 
    path: '/tasks', 
    label: 'Study Tasks', 
    icon: 'ReportsIcon',
  },
  { 
    path: '/bookmarks', 
    label: 'My Bookmarks', 
    icon: 'SalesIcon',
  },
  { 
    path: '/settings', 
    label: 'Settings', 
    icon: 'SettingsIcon',
  },
]

onMounted(async () => {
  try {
    const response = await api.get('/learning/progress')
    if (response.data.status === 'success') {
      const data = response.data.data
      stats.value = {
        materials: data.bookmarks || 0,
        notes: data.notes_generated || 0,
        quizzes: data.quizzes_generated || 0,
        progress: data.progress_percentage || 0,
      }
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
})
</script>
