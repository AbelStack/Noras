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
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user?.name }}!</h1>
        <p class="mt-2 text-gray-600">Student ID: {{ user?.student_id }}</p>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Materials</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.materials || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Notes Generated</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.notes || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Quizzes</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.quizzes || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Active Tasks</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.tasks || 0 }}</div>
        </div>
      </div>

      <!-- Feature Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <router-link
          to="/materials"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Course Materials</h3>
          <p class="text-gray-600">Browse and download study materials</p>
        </router-link>

        <router-link
          to="/notes"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">AI Study Notes</h3>
          <p class="text-gray-600">Generate AI-powered summaries</p>
        </router-link>

        <router-link
          to="/quizzes"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">AI Quizzes</h3>
          <p class="text-gray-600">Test your knowledge</p>
        </router-link>

        <router-link
          to="/tasks"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Study Tasks</h3>
          <p class="text-gray-600">Manage your study schedule</p>
        </router-link>

        <router-link
          to="/bookmarks"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Bookmarks</h3>
          <p class="text-gray-600">Your saved resources</p>
        </router-link>

        <router-link
          to="/history"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Learning History</h3>
          <p class="text-gray-600">Track your progress</p>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import api from '../services/api'

const router = useRouter()
const user = ref(authService.getUser())
const stats = ref({
  materials: 0,
  notes: 0,
  quizzes: 0,
  tasks: 0,
})

onMounted(async () => {
  try {
    // Fetch learning progress stats
    const response = await api.get('/learning/progress')
    if (response.data.status === 'success') {
      const data = response.data.data
      stats.value = {
        materials: data.bookmarks || 0,
        notes: data.notes_generated || 0,
        quizzes: data.quizzes_generated || 0,
        tasks: data.active_tasks || 0,
      }
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
})

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
