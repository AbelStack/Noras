<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/departments')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Departments
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                {{ department?.name || 'Course Offerings' }}
              </h1>
              <span 
                v-if="isMyDepartment" 
                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"
              >
                Your Enrolled Department
              </span>
            </div>
            <p class="text-sm text-gray-600 mt-1">
              <span v-if="department?.university">{{ department.university.name }} &bull; </span>
              <span>{{ curriculumSubtitle }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Filters & Year Selection -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <!-- Year Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
          <button
            @click="selectedYear = 'all'"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
              selectedYear === 'all'
                ? 'bg-primary-600 text-white shadow-xs'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            All Years
          </button>
          <button
            v-for="yr in availableYears"
            :key="yr"
            @click="selectedYear = yr"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
              selectedYear === yr
                ? 'bg-primary-600 text-white shadow-xs'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Year {{ yr }}
          </button>
        </div>

        <!-- Course Search -->
        <div class="relative w-full md:w-72">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search courses or codes..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
          />
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
        <button @click="loadCourses" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="Object.keys(groupedOfferings).length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No course offerings found</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">
          {{ selectedYear !== 'all' || searchQuery ? 'No course offerings match your filter criteria.' : 'No active course offerings are currently scheduled for this department.' }}
        </p>
        <button 
          v-if="selectedYear !== 'all' || searchQuery"
          @click="resetFilters"
          class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition-colors"
        >
          Reset Filters
        </button>
      </div>

      <!-- Grouped Courses -->
      <div v-else class="space-y-8">
        <div v-for="(yearOfferings, year) in groupedOfferings" :key="year" class="space-y-4">
          <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
            <span class="w-2.5 h-2.5 rounded-full bg-primary-600"></span>
            <h2 class="text-xl font-bold text-gray-900">Year {{ year }}</h2>
          </div>
          
          <div v-for="(semesterOfferings, semester) in yearOfferings" :key="`${year}-${semester}`" class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 pl-1">Semester {{ semester }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
              <router-link
                v-for="offering in semesterOfferings"
                :key="offering.id"
                :to="`/offerings/${offering.id}/materials`"
                class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-primary-300 transition-all group flex flex-col justify-between"
              >
                <div>
                  <div class="flex justify-between items-start gap-2 mb-2">
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-primary-700 border border-blue-100">
                      {{ offering.course?.code }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium">
                      {{ offering.academic_year }}
                    </span>
                  </div>
                  
                  <h4 class="text-base font-bold text-gray-900 group-hover:text-primary-600 transition-colors mb-2">
                    {{ offering.course?.title || offering.course?.name }}
                  </h4>
                  
                  <p v-if="offering.course?.description" class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                    {{ offering.course.description }}
                  </p>
                </div>
                
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100 text-xs">
                  <span class="font-medium text-gray-600">
                    {{ offering.course?.credit_hours || offering.course?.credits || 3 }} credit hours
                  </span>
                  <span class="inline-flex items-center gap-1 font-semibold text-primary-600 group-hover:translate-x-1 transition-transform">
                    View Materials &rarr;
                  </span>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { catalogService } from '../services/catalog'
import { studentNavigationItems } from '../config/navigation'
import { authService } from '../services/auth'

const route = useRoute()
const department = ref(null)
const offerings = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const user = authService.getUser()
const isMyDepartment = computed(() => {
  return user?.department_id && String(user.department_id) === String(route.params.departmentId)
})

const selectedYear = ref('all')
const searchQuery = ref('')

const curriculumSubtitle = computed(() => {
  if (!department.value) return 'Curriculum and semester course offerings'
  const name = (department.value.name || '').toLowerCase()
  const code = (department.value.code || '').toLowerCase()
  if (name.includes('software') || code === 'se') {
    return 'Bachelor of Science in Software Engineering (Years 2 – 5 Curriculum)'
  }
  if (name.includes('computer') || code === 'cs') {
    return 'Bachelor of Science in Computer Science (Years 2 – 4 Curriculum)'
  }
  return 'Curriculum courses and academic materials'
})

const availableYears = computed(() => {
  const years = new Set()
  offerings.value.forEach(o => {
    if (o.study_year) years.add(o.study_year)
  })
  return Array.from(years).sort((a, b) => a - b)
})

const filteredOfferings = computed(() => {
  return offerings.value.filter(offering => {
    const matchesYear = selectedYear.value === 'all' || offering.study_year === selectedYear.value
    
    const code = offering.course?.code || ''
    const title = offering.course?.title || offering.course?.name || ''
    const matchesSearch = !searchQuery.value ||
      code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      title.toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesYear && matchesSearch
  })
})

const groupedOfferings = computed(() => {
  const grouped = {}
  filteredOfferings.value.forEach(offering => {
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

const resetFilters = () => {
  selectedYear.value = 'all'
  searchQuery.value = ''
}

onMounted(() => {
  loadCourses()
})

const loadCourses = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const departmentId = route.params.departmentId
    department.value = await catalogService.getDepartment(departmentId)
    offerings.value = await catalogService.getCourseOfferings({ department_id: departmentId })
  } catch (error) {
    console.error('Failed to load courses:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load courses. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>
