<template>
  <DashboardLayout 
    pageTitle="Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-8">
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
          <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.pending_students || 0 }}</p>
        </div>

        <!-- Total Students -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Total Students</p>
          <p class="text-4xl font-bold text-gray-900 mb-1">{{ stats.total_students || 0 }}</p>
          <p class="text-xs text-gray-500">approved accounts</p>
        </div>

        <!-- Course Materials -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Course Materials</p>
          <p class="text-4xl font-bold text-accent-500 mb-1">{{ stats.total_materials || 0 }}</p>
          <a href="#" class="text-xs text-accent-500 hover:underline">View all →</a>
        </div>

        <!-- Active Courses -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <p class="text-sm text-gray-600 mb-2">Active Course Offerings</p>
          <p class="text-2xl font-bold text-gray-900 mb-1">{{ stats.active_courses || 0 }}</p>
          <p class="text-xs text-gray-500">This semester</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Student Status Distribution (Donut Chart) -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Student Status Distribution</h3>
          <div class="flex flex-col items-center">
            <!-- Donut Chart Placeholder -->
            <div class="relative w-64 h-64">
              <svg viewBox="0 0 200 200" class="w-full h-full">
                <!-- Approved (Blue) - 100% for now -->
                <circle
                  cx="100"
                  cy="100"
                  r="80"
                  fill="none"
                  stroke="#2E5CCC"
                  stroke-width="40"
                  stroke-dasharray="502.4"
                  stroke-dashoffset="0"
                  transform="rotate(-90 100 100)"
                />
              </svg>
            </div>
            <!-- Legend -->
            <div class="flex items-center space-x-6 mt-4">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-primary-500 rounded"></div>
                <span class="text-sm text-gray-700">Approved</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-accent-500 rounded"></div>
                <span class="text-sm text-gray-700">Pending</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-3 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700">Suspended</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Material Uploads Trend (Line Chart) -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Material Uploads — This Month</h3>
          <div class="h-64 flex items-end justify-between space-x-2">
            <!-- Simple line chart placeholder -->
            <div class="relative w-full h-full bg-gray-50 rounded">
              <svg viewBox="0 0 400 200" class="w-full h-full" preserveAspectRatio="none">
                <polyline
                  points="0,180 100,175 200,170 300,100 400,20"
                  fill="none"
                  stroke="#2E5CCC"
                  stroke-width="2"
                />
                <circle cx="400" cy="20" r="4" fill="#2E5CCC" />
              </svg>
              <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 pb-2 text-xs text-gray-500">
                <span>Week 1</span>
                <span>Week 2</span>
                <span>Week 3</span>
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
const stats = ref({})

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
    path: '/admin/universities', 
    label: 'Universities', 
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
    const response = await api.get('/admin/dashboard')
    if (response.data.status === 'success') {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  }
})
</script>
