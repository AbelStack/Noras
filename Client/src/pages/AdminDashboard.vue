<template>
  <DashboardLayout 
    pageTitle="Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
        <p class="text-red-700">{{ error }}</p>
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Page Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-gray-600 mt-1">Overview of academic operations</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Pending Approvals -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Pending Student Approvals</p>
            <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.students.pending }}</p>
            <router-link to="/admin/students" class="text-xs text-primary-500 hover:underline">
              Review pending →
            </router-link>
          </div>

          <!-- Total Students -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Total Students</p>
            <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.students.total }}</p>
            <p class="text-xs text-gray-500">{{ stats.students.approved }} approved</p>
          </div>

          <!-- Course Materials -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Course Materials</p>
            <p class="text-4xl font-bold text-accent-500 mb-1">{{ stats.materials.total }}</p>
            <router-link to="/admin/materials" class="text-xs text-accent-500 hover:underline">
              View all →
            </router-link>
          </div>

          <!-- Active Courses -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Active Course Offerings</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">{{ stats.catalog.course_offerings }}</p>
            <p class="text-xs text-gray-500">{{ stats.catalog.courses }} total courses</p>
          </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Campuses</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.catalog.universities }}</p>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">Departments</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.catalog.departments }}</p>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">AI Notes Generated</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.ai_generated.notes }}</p>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-2">AI Quizzes Generated</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.ai_generated.quizzes }}</p>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Student Status Distribution (Donut Chart) -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Student Status Distribution</h3>
            <div v-if="stats.students.total === 0" class="flex items-center justify-center h-64 text-gray-500">
              No students registered yet
            </div>
            <div v-else class="flex flex-col items-center">
              <!-- Donut Chart with Real Data -->
              <div class="relative w-64 h-64">
                <svg viewBox="0 0 200 200" class="w-full h-full -rotate-90">
                  <circle
                    v-for="(segment, index) in donutSegments"
                    :key="index"
                    cx="100"
                    cy="100"
                    r="80"
                    fill="none"
                    :stroke="segment.color"
                    stroke-width="40"
                    :stroke-dasharray="segment.dasharray"
                    :stroke-dashoffset="segment.dashoffset"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center">
                    <p class="text-3xl font-bold text-gray-900">{{ stats.students.total }}</p>
                    <p class="text-sm text-gray-500">Students</p>
                  </div>
                </div>
              </div>
              <!-- Legend -->
              <div class="grid grid-cols-2 gap-4 mt-6 w-full max-w-sm">
                <div v-for="segment in donutSegments" :key="segment.label" class="flex items-center space-x-2">
                  <div class="w-4 h-3 rounded" :style="{ backgroundColor: segment.color }"></div>
                  <div>
                    <p class="text-sm font-medium text-gray-700">{{ segment.label }}</p>
                    <p class="text-xs text-gray-500">{{ segment.count }} ({{ segment.percentage }}%)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Material Status Distribution -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Course Materials Status</h3>
            <div v-if="stats.materials.total === 0" class="flex items-center justify-center h-64 text-gray-500">
              No materials uploaded yet
            </div>
            <div v-else class="space-y-4">
              <!-- Published Materials Bar -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Published</span>
                  <span class="text-sm text-gray-600">{{ stats.materials.published }} / {{ stats.materials.total }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    class="bg-green-500 h-3 rounded-full transition-all duration-500"
                    :style="{ width: `${(stats.materials.published / stats.materials.total) * 100}%` }"
                  ></div>
                </div>
              </div>

              <!-- Draft Materials Bar -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Draft</span>
                  <span class="text-sm text-gray-600">{{ stats.materials.draft }} / {{ stats.materials.total }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    class="bg-yellow-500 h-3 rounded-full transition-all duration-500"
                    :style="{ width: `${(stats.materials.draft / stats.materials.total) * 100}%` }"
                  ></div>
                </div>
              </div>

              <!-- Quick Stats -->
              <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-gray-200">
                <div class="text-center">
                  <p class="text-2xl font-bold text-green-600">{{ stats.materials.published }}</p>
                  <p class="text-xs text-gray-600 mt-1">Published</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-yellow-600">{{ stats.materials.draft }}</p>
                  <p class="text-xs text-gray-600 mt-1">Drafts</p>
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import { authService } from '../services/auth'
import api from '../services/api'

const router = useRouter()
const stats = ref({
  students: {
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    suspended: 0
  },
  catalog: {
    universities: 0,
    departments: 0,
    courses: 0,
    course_offerings: 0
  },
  materials: {
    total: 0,
    published: 0,
    draft: 0
  },
  ai_generated: {
    notes: 0,
    quizzes: 0
  }
})

const loading = ref(true)
const error = ref(null)

// Calculate percentages for the donut chart
const studentStatusPercentages = computed(() => {
  const total = stats.value.students.total
  if (total === 0) return { approved: 100, pending: 0, suspended: 0, rejected: 0 }
  
  return {
    approved: (stats.value.students.approved / total) * 100,
    pending: (stats.value.students.pending / total) * 100,
    suspended: (stats.value.students.suspended / total) * 100,
    rejected: (stats.value.students.rejected / total) * 100
  }
})

// Calculate stroke dash arrays for donut chart
const donutSegments = computed(() => {
  const percentages = studentStatusPercentages.value
  const circumference = 2 * Math.PI * 80 // r=80
  
  let currentOffset = 0
  const segments = []
  
  // Approved (Blue)
  if (percentages.approved > 0) {
    const dashLength = (percentages.approved / 100) * circumference
    segments.push({
      color: '#2E5CCC',
      dasharray: `${dashLength} ${circumference}`,
      dashoffset: -currentOffset,
      label: 'Approved',
      count: stats.value.students.approved,
      percentage: percentages.approved.toFixed(1)
    })
    currentOffset += dashLength
  }
  
  // Pending (Yellow)
  if (percentages.pending > 0) {
    const dashLength = (percentages.pending / 100) * circumference
    segments.push({
      color: '#F59E0B',
      dasharray: `${dashLength} ${circumference}`,
      dashoffset: -currentOffset,
      label: 'Pending',
      count: stats.value.students.pending,
      percentage: percentages.pending.toFixed(1)
    })
    currentOffset += dashLength
  }
  
  // Suspended (Red)
  if (percentages.suspended > 0) {
    const dashLength = (percentages.suspended / 100) * circumference
    segments.push({
      color: '#EF4444',
      dasharray: `${dashLength} ${circumference}`,
      dashoffset: -currentOffset,
      label: 'Suspended',
      count: stats.value.students.suspended,
      percentage: percentages.suspended.toFixed(1)
    })
    currentOffset += dashLength
  }
  
  // Rejected (Gray)
  if (percentages.rejected > 0) {
    const dashLength = (percentages.rejected / 100) * circumference
    segments.push({
      color: '#9CA3AF',
      dasharray: `${dashLength} ${circumference}`,
      dashoffset: -currentOffset,
      label: 'Rejected',
      count: stats.value.students.rejected,
      percentage: percentages.rejected.toFixed(1)
    })
  }
  
  return segments
})

const navigationItems = [
  { 
    path: '/admin/dashboard', 
    label: 'Dashboard', 
    icon: 'DashboardIcon',
  },
  { 
    path: '/admin/students', 
    label: 'Student Approvals', 
    icon: 'ApprovalIcon',
  },
  { 
    path: '/admin/campus', 
    label: 'Campuses', 
    icon: 'ProductsIcon',
  },
  { 
    path: '/admin/departments', 
    label: 'Departments', 
    icon: 'InventoryIcon',
  },
  { 
    path: '/admin/courses', 
    label: 'Courses', 
    icon: 'BookIcon',
  },
  { 
    path: '/admin/materials', 
    label: 'Course Materials', 
    icon: 'SalesIcon',
  },
  { 
    path: '/admin/settings', 
    label: 'Settings', 
    icon: 'SettingsIcon',
  },
]

onMounted(async () => {
  try {
    loading.value = true
    const response = await api.get('/admin/dashboard')
    if (response.data.status === 'success') {
      stats.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch dashboard stats:', err)
    error.value = 'Failed to load dashboard data'
  } finally {
    loading.value = false
  }
})
</script>
