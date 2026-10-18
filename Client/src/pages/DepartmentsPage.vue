<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
            {{ myDepartment?.name || 'Department' }} Curriculum Years
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Browse Year 2 to Year {{ departmentDuration }} courses, lecture notes, and learning materials
          </p>
        </div>
      </div>

      <!-- Loading State (Skeleton Cards) -->
      <div v-if="isLoading" class="py-2">
        <SkeletonLoader type="cards" :count="6" columnsClass="grid-cols-1 md:grid-cols-2 lg:grid-cols-3" />
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <button @click="loadDepartments" class="text-sm text-red-700 hover:text-red-800 underline font-medium cursor-pointer">
          Try again
        </button>
      </div>

      <!-- Main Content when Loaded -->
      <div v-else class="space-y-6">
        <!-- Enrolled Department Quick Info Banner -->
        <div 
          v-if="myDepartment"
          class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs"
        >
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-primary-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-primary-700 uppercase tracking-wider">Your Enrolled Department</span>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-primary-800 border border-blue-200">Enrolled</span>
              </div>
              <h3 class="text-base sm:text-lg font-bold text-gray-900 mt-0.5">
                {{ myDepartment.name }} ({{ myDepartment.code }})
              </h3>
              <p class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                <span v-if="myDepartment.university">{{ myDepartment.university.name }} &bull;</span>
                <span>{{ departmentDuration }}-Year Academic Program</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Academic Years View -->
        <div class="space-y-4">
          <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <div>
              <h2 class="text-base font-bold text-gray-900">Academic Years (Year 2 &ndash; Year {{ departmentDuration }})</h2>
              <p class="text-xs text-gray-500">Select a study year to browse Semester 1 and Semester 2 courses</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600">
              {{ yearCards.length }} Academic Years
            </span>
          </div>

          <!-- Academic Years Grid (Years 2 to Duration) -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <router-link
              v-for="yr in yearCards"
              :key="yr.year"
              :to="`/departments/${targetDepartmentId}/courses?year=${yr.year}`"
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
                        {{ myDepartment?.name || 'Department' }}
                      </p>
                    </div>
                  </div>
                  <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md bg-blue-50 text-primary-700 border border-blue-100">
                    Year {{ yr.year }} of {{ departmentDuration }}
                  </span>
                </div>

                <p class="text-xs text-gray-600 mt-3 line-clamp-2 leading-relaxed">
                  Curriculum courses, lecture presentations, and AI study notes for Year {{ yr.year }} (Semester 1 &amp; 2).
                </p>

                <div class="mt-4 flex flex-wrap gap-1.5">
                  <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">Semester 1</span>
                  <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">Semester 2</span>
                </div>
              </div>

              <div class="flex items-center justify-between text-xs font-semibold text-primary-600 pt-4 mt-4 border-t border-gray-100">
                <span>Explore Courses</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { catalogService } from '../services/catalog'
import { studentNavigationItems } from '../config/navigation'
import { authService } from '../services/auth'

const user = computed(() => authService.getUser())
const departments = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const myDepartment = computed(() => {
  const deptId = user.value?.department_id || user.value?.department?.id
  if (deptId) {
    const found = departments.value.find(d => d.id === deptId)
    if (found) return found
    if (user.value?.department) return user.value.department
  }
  return departments.value.length > 0 ? departments.value[0] : null
})

const targetDepartmentId = computed(() => {
  return myDepartment.value?.id || user.value?.department_id || (departments.value.length > 0 ? departments.value[0].id : 1)
})

const departmentDuration = computed(() => {
  return myDepartment.value?.total_years || 4
})

const yearCards = computed(() => {
  const total = departmentDuration.value
  const years = []
  // Show years starting from Year 2 up to total_years (e.g. 2, 3, 4 for 4-year degree)
  for (let y = 2; y <= total; y++) {
    years.push({
      year: y,
      totalYears: total,
    })
  }
  // Fallback if department duration is less than 2
  if (years.length === 0) {
    years.push({ year: 1, totalYears: 1 })
  }
  return years
})

onMounted(async () => {
  await loadDepartments()
})

const loadDepartments = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    try {
      await authService.me()
    } catch (e) {
      console.warn('Could not refresh user profile in DepartmentsPage:', e)
    }
    departments.value = await catalogService.getDepartments()
  } catch (error) {
    console.error('Failed to load departments:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load curriculum years. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>
