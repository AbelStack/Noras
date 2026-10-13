<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50/30">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <img src="/Noras logo-sm.png" alt="Noras" class="h-8 w-auto" />
            <span class="ml-2 text-xl font-bold text-gray-900">Noras Admin</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ user?.name }} (Admin)</span>
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
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">Manage students, courses, and materials</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Dashboard Stats -->
      <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Total Students</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.total_students || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Pending Approvals</div>
          <div class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.pending_students || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Course Materials</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.total_materials || 0 }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-600">Active Courses</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.active_courses || 0 }}</div>
        </div>
      </div>

      <!-- Admin Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <router-link
          to="/admin/students"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Student Management</h3>
          <p class="text-gray-600">Approve, reject, or manage students</p>
        </router-link>

        <router-link
          to="/admin/materials"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Course Materials</h3>
          <p class="text-gray-600">Upload and manage study materials</p>
        </router-link>

        <router-link
          to="/admin/courses"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Course Management</h3>
          <p class="text-gray-600">Manage courses and offerings</p>
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
const isLoading = ref(true)
const stats = ref({})

onMounted(async () => {
  try {
    const response = await api.get('/admin/dashboard')
    if (response.data.status === 'success') {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  } finally {
    isLoading.value = false
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
