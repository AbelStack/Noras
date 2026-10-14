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
      <CatalogBreadcrumb :items="breadcrumbs" />

      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Academic Departments</h1>
        <p class="mt-2 text-gray-600">Browse courses by department</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading departments...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadDepartments"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="departments.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments found</h3>
        <p class="mt-1 text-sm text-gray-500">No active departments are available at this time.</p>
      </div>

      <!-- Departments Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link
          v-for="department in departments"
          :key="department.id"
          :to="`/departments/${department.id}/courses`"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all hover:border-blue-300"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-bold text-gray-900 mb-1">{{ department.name }}</h3>
              <p class="text-sm text-gray-500 mb-2">{{ department.code }}</p>
              <p v-if="department.university" class="text-sm text-gray-600">
                {{ department.university.name }}
              </p>
            </div>
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { catalogService } from '../services/catalog'
import CatalogBreadcrumb from '../components/CatalogBreadcrumb.vue'

const router = useRouter()
const user = ref(authService.getUser())
const departments = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const breadcrumbs = [
  { label: 'Departments' }
]

onMounted(() => {
  loadDepartments()
})

const loadDepartments = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    departments.value = await catalogService.getDepartments()
  } catch (error) {
    console.error('Failed to load departments:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load departments. Please try again.'
  } finally {
    isLoading.value = false
  }
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
