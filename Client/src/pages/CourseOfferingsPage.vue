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
        <h1 class="text-3xl font-bold text-gray-900">{{ department?.name || 'Courses' }}</h1>
        <p class="mt-2 text-gray-600">Browse available course offerings by year and semester</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading courses...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadCourses"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="Object.keys(groupedOfferings).length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
        <p class="mt-1 text-sm text-gray-500">No active course offerings are available for this department.</p>
      </div>

      <!-- Grouped Courses by Year and Semester -->
      <div v-else class="space-y-8">
        <div v-for="(yearOfferings, year) in groupedOfferings" :key="year">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Year {{ year }}</h2>
          
          <div v-for="(semesterOfferings, semester) in yearOfferings" :key="`${year}-${semester}`" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">Semester {{ semester }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <router-link
                v-for="offering in semesterOfferings"
                :key="offering.id"
                :to="`/offerings/${offering.id}/materials`"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all hover:border-blue-300"
              >
                <div class="flex justify-between items-start mb-2">
                  <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 rounded-full">
                    {{ offering.course?.code }}
                  </span>
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
                
                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ offering.course?.title }}</h4>
                
                <p v-if="offering.course?.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                  {{ offering.course.description }}
                </p>
                
                <div class="flex items-center text-sm text-gray-500 space-x-4">
                  <span v-if="offering.course?.credit_hours">{{ offering.course.credit_hours }} Credits</span>
                  <span>{{ offering.academic_year }}</span>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/auth'
import { catalogService } from '../services/catalog'
import CatalogBreadcrumb from '../components/CatalogBreadcrumb.vue'

const router = useRouter()
const route = useRoute()
const user = ref(authService.getUser())
const department = ref(null)
const offerings = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const breadcrumbs = computed(() => {
  const items = [
    { label: 'Departments', to: '/departments' },
  ]
  if (department.value) {
    items.push({ label: department.value.name })
  }
  return items
})

const groupedOfferings = computed(() => {
  const grouped = {}
  offerings.value.forEach(offering => {
    const year = offering.study_year
    const semester = offering.semester
    
    if (!grouped[year]) {
      grouped[year] = {}
    }
    if (!grouped[year][semester]) {
      grouped[year][semester] = []
    }
    
    grouped[year][semester].push(offering)
  })
  return grouped
})

onMounted(() => {
  loadCourses()
})

const loadCourses = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const departmentId = route.params.departmentId
    
    // Load department details
    department.value = await catalogService.getDepartment(departmentId)
    
    // Load course offerings for this department
    offerings.value = await catalogService.getCourseOfferings({ department_id: departmentId })
  } catch (error) {
    console.error('Failed to load courses:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load courses. Please try again.'
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
