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
          Back to Academic Years
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                {{ department?.name || 'Department' }} &mdash; Year {{ selectedYear }}
              </h1>
              <span 
                v-if="isMyDepartment" 
                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-primary-800 border border-blue-200"
              >
                Your Enrolled Department
              </span>
            </div>
            <p class="text-sm text-gray-600 mt-1">
              <span v-if="department?.university">{{ department.university.name }} &bull; </span>
              <span>Year {{ selectedYear }} Curriculum &bull; Semester 1 &amp; Semester 2</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Year Selector & Filters Card -->
      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <!-- Top row: Year Switcher & Course Search -->
        <div class="flex flex-col md:flex-row gap-3 items-start md:items-center justify-between">
          <!-- Year Selector Buttons (Years 2 to Duration) -->
          <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
            <span class="text-xs font-semibold text-gray-500 mr-1.5">Academic Year:</span>
            <button
              v-for="yr in yearRange"
              :key="yr"
              @click="changeYear(yr)"
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
              All
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

          <span class="text-xs text-gray-500 font-medium">
            Showing {{ filteredOfferings.length }} course{{ filteredOfferings.length === 1 ? '' : 's' }}
          </span>
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
      <div v-else-if="filteredOfferings.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No course offerings found</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">
          {{ selectedSemester !== 'all' || searchQuery ? 'No courses match the selected semester or search criteria.' : `No courses are currently registered for Year ${selectedYear}.` }}
        </p>
        <button 
          v-if="selectedSemester !== 'all' || searchQuery"
          @click="resetFilters"
          class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-semibold hover:bg-primary-700 transition-colors cursor-pointer"
        >
          Reset Filters
        </button>
      </div>

      <!-- Course Offerings Grid -->
      <div v-else class="space-y-6">
        <!-- If "All" is selected, group courses by Semester for clean layout -->
        <div v-if="selectedSemester === 'all'" class="space-y-8">
          <div 
            v-for="sem in [1, 2]" 
            :key="sem"
            v-show="offeringsBySemester[sem] && offeringsBySemester[sem].length > 0"
            class="space-y-3"
          >
            <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
              <span class="w-2.5 h-2.5 rounded-full bg-primary-600"></span>
              <h2 class="text-base sm:text-lg font-bold text-gray-900">
                Semester {{ sem }} Courses
              </h2>
              <span class="text-xs text-gray-500">({{ offeringsBySemester[sem]?.length || 0 }})</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
              <router-link
                v-for="offering in offeringsBySemester[sem]"
                :key="offering.id"
                :to="`/offerings/${offering.id}/materials`"
                class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-primary-300 transition-all group flex flex-col justify-between cursor-pointer"
              >
                <div>
                  <div class="flex justify-between items-start gap-2 mb-2">
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-primary-700 border border-blue-100">
                      {{ offering.course?.code }}
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                      Semester {{ offering.semester }} &bull; {{ offering.academic_year }}
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
                    Explore Course &rarr;
                  </span>
                </div>
              </router-link>
            </div>
          </div>
        </div>

        <!-- If specific semester is selected (Semester 1 or Semester 2) -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <router-link
            v-for="offering in filteredOfferings"
            :key="offering.id"
            :to="`/offerings/${offering.id}/materials`"
            class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-primary-300 transition-all group flex flex-col justify-between cursor-pointer"
          >
            <div>
              <div class="flex justify-between items-start gap-2 mb-2">
                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-primary-700 border border-blue-100">
                  {{ offering.course?.code }}
                </span>
                <span class="text-xs text-gray-500 font-medium">
                  Semester {{ offering.semester }} &bull; {{ offering.academic_year }}
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
                Explore Course &rarr;
              </span>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { catalogService } from '../services/catalog'
import { studentNavigationItems } from '../config/navigation'
import { authService } from '../services/auth'

const route = useRoute()
const router = useRouter()
const department = ref(null)
const offerings = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const user = authService.getUser()
const isMyDepartment = computed(() => {
  return user?.department_id && String(user.department_id) === String(route.params.departmentId)
})

// Initialize selectedYear from query param or default to 2
const selectedYear = ref(route.query.year ? Number(route.query.year) : 2)
const selectedSemester = ref('all')
const searchQuery = ref('')

watch(() => route.query.year, (newYear) => {
  if (newYear) {
    selectedYear.value = Number(newYear)
  }
})

const departmentDuration = computed(() => {
  return department.value?.total_years || 4
})

const yearRange = computed(() => {
  const total = departmentDuration.value
  const years = []
  // Show years starting from Year 2 up to total_years
  for (let y = 2; y <= total; y++) {
    years.push(y)
  }
  // If department offerings include other years, include them too
  offerings.value.forEach(o => {
    if (o.study_year && !years.includes(o.study_year)) {
      years.push(o.study_year)
    }
  })
  return years.sort((a, b) => a - b)
})

const changeYear = (yr) => {
  selectedYear.value = yr
  router.replace({ query: { ...route.query, year: yr } })
}

const filteredOfferings = computed(() => {
  return offerings.value.filter(offering => {
    const matchesYear = offering.study_year === selectedYear.value
    
    const matchesSemester = selectedSemester.value === 'all' || 
      String(offering.semester) === String(selectedSemester.value)
    
    const code = offering.course?.code || ''
    const title = offering.course?.title || offering.course?.name || ''
    const matchesSearch = !searchQuery.value ||
      code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      title.toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesYear && matchesSemester && matchesSearch
  })
})

const offeringsBySemester = computed(() => {
  const grouped = { 1: [], 2: [] }
  filteredOfferings.value.forEach(offering => {
    const sem = Number(offering.semester) || 1
    if (!grouped[sem]) grouped[sem] = []
    grouped[sem].push(offering)
  })
  return grouped
})

const resetFilters = () => {
  selectedSemester.value = 'all'
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
    
    // If route didn't have year query param, check if selectedYear exists in yearRange, otherwise pick first
    if (!route.query.year && yearRange.value.length > 0) {
      if (!yearRange.value.includes(selectedYear.value)) {
        selectedYear.value = yearRange.value[0]
      }
    }
  } catch (error) {
    console.error('Failed to load courses:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load courses. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>
