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
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Academic Departments</h1>
          <p class="text-sm text-gray-600 mt-1">Browse courses, curriculum subjects, and academic materials</p>
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
        <button @click="loadDepartments" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Enrolled Department Quick Action Banner -->
      <div 
        v-if="user?.department_id && myDepartment"
        class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div>
            <span class="text-xs font-semibold text-primary-600 uppercase tracking-wider">Your Enrolled Department</span>
            <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ myDepartment.name }} ({{ myDepartment.code }})</h3>
            <p v-if="myDepartment.university" class="text-xs text-gray-500">{{ myDepartment.university.name }}</p>
          </div>
        </div>

        <router-link
          :to="`/departments/${myDepartment.id}/courses`"
          class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-semibold text-sm shadow-sm transition-colors flex-shrink-0"
        >
          <span>View My Department Courses</span>
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </router-link>
      </div>

      <!-- Department Filter Tabs -->
      <div v-if="user?.department_id" class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-3">
        <button
          @click="deptFilter = 'mine'"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            deptFilter === 'mine'
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          My Department
        </button>
        <button
          v-if="user?.university_id"
          @click="deptFilter = 'campus'"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            deptFilter === 'campus'
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          My Campus ({{ user.university?.name || 'Campus' }})
        </button>
        <button
          @click="deptFilter = 'all'"
          :class="[
            'px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            deptFilter === 'all'
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          All Departments
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredDepartments.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No departments found</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">No academic departments match the selected filter.</p>
      </div>

      <!-- Departments Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link
          v-for="department in filteredDepartments"
          :key="department.id"
          :to="`/departments/${department.id}/courses`"
          :class="[
            'bg-white rounded-2xl border p-6 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between',
            user?.department_id === department.id ? 'border-primary-400 ring-2 ring-primary-100' : 'border-gray-200 hover:border-primary-300'
          ]"
        >
          <div>
            <div class="flex items-start justify-between gap-2 mb-3">
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors">
                {{ department.name }}
              </h3>
              <div class="flex items-center gap-1.5 flex-shrink-0">
                <span 
                  v-if="user?.department_id === department.id"
                  class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800"
                >
                  Enrolled
                </span>
                <span class="text-xs font-mono font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-700">
                  {{ department.code }}
                </span>
              </div>
            </div>
            <p v-if="department.university" class="text-xs text-gray-500 flex items-center gap-1.5 mt-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ department.university.name }}</span>
            </p>
          </div>

          <div class="flex items-center justify-between text-xs font-medium text-primary-600 pt-4 mt-4 border-t border-gray-100">
            <span>Explore Courses</span>
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </router-link>
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
const deptFilter = ref(user.value?.department_id ? 'mine' : 'all')

const myDepartment = computed(() => {
  if (!user.value?.department_id) return null
  return departments.value.find(d => d.id === user.value.department_id) || null
})

const filteredDepartments = computed(() => {
  if (deptFilter.value === 'mine' && user.value?.department_id) {
    return departments.value.filter(d => d.id === user.value.department_id)
  }
  if (deptFilter.value === 'campus' && user.value?.university_id) {
    return departments.value.filter(d => d.university_id === user.value.university_id)
  }
  return departments.value
})

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
</script>
