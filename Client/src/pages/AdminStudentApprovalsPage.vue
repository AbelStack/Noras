<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Student Approvals</h1>
        <p class="text-gray-600 mt-1">Review and approve student registrations</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
        <p class="text-red-700">{{ error }}</p>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="status in statuses"
              :key="status.value"
              @click="filterStatus = status.value"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                filterStatus === status.value
                  ? 'bg-primary-500 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ status.label }} ({{ getStatusCount(status.value) }})
            </button>
          </div>
        </div>

        <!-- Students List -->
        <div v-if="filteredStudents.length === 0" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <p class="text-gray-600">No {{ filterStatus === 'all' ? '' : filterStatus }} students found</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="student in filteredStudents"
            :key="student.id"
            class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                    {{ getInitials(student.name) }}
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ student.name }}</h3>
                    <p class="text-sm text-gray-600">{{ student.email }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                  <div>
                    <p class="text-xs text-gray-500">Student ID</p>
                    <p class="text-sm font-medium text-gray-900">{{ student.student_id || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <span :class="getStatusBadgeClass(student.account_status)">
                      {{ student.account_status }}
                    </span>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500">Registered</p>
                    <p class="text-sm font-medium text-gray-900">{{ formatDate(student.created_at) }}</p>
                  </div>
                  <div v-if="student.approved_at">
                    <p class="text-xs text-gray-500">Approved On</p>
                    <p class="text-sm font-medium text-gray-900">{{ formatDate(student.approved_at) }}</p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div v-if="student.account_status === 'pending'" class="flex gap-2 ml-4">
                <button
                  @click="approveStudent(student.id)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <svg v-if="processingId === student.id" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Approve
                </button>
                <button
                  @click="rejectStudent(student.id)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Reject
                </button>
              </div>
              <div v-else-if="student.account_status === 'approved'" class="flex gap-2 ml-4">
                <button
                  @click="suspendStudent(student.id)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Suspend
                </button>
              </div>
              <div v-else-if="student.account_status === 'suspended'" class="flex gap-2 ml-4">
                <button
                  @click="reactivateStudent(student.id)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Reactivate
                </button>
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
import api from '../services/api'

const router = useRouter()
const students = ref([])
const loading = ref(true)
const error = ref(null)
const filterStatus = ref('all')
const processingId = ref(null)

const statuses = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Suspended', value: 'suspended' },
]

const navigationItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  { path: '/admin/students', label: 'Student Approvals', icon: 'ApprovalIcon' },
  { path: '/admin/universities', label: 'Universities', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const filteredStudents = computed(() => {
  if (filterStatus.value === 'all') return students.value
  return students.value.filter(s => s.account_status === filterStatus.value)
})

const getStatusCount = (status) => {
  if (status === 'all') return students.value.length
  return students.value.filter(s => s.account_status === status).length
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getStatusBadgeClass = (status) => {
  const baseClasses = 'text-xs font-medium px-2 py-1 rounded-full'
  const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    suspended: 'bg-gray-100 text-gray-800',
  }
  return `${baseClasses} ${statusClasses[status] || ''}`
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const fetchStudents = async () => {
  try {
    loading.value = true
    const response = await api.get('/admin/students')
    if (response.data.status === 'success') {
      students.value = response.data.data.students
    }
  } catch (err) {
    console.error('Failed to fetch students:', err)
    error.value = 'Failed to load students'
  } finally {
    loading.value = false
  }
}

const approveStudent = async (id) => {
  if (!confirm('Are you sure you want to approve this student?')) return
  
  try {
    processingId.value = id
    const response = await api.post(`/admin/students/${id}/approve`)
    if (response.data.status === 'success') {
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to approve student:', err)
    alert('Failed to approve student')
  } finally {
    processingId.value = null
  }
}

const rejectStudent = async (id) => {
  if (!confirm('Are you sure you want to reject this student? This action cannot be undone.')) return
  
  try {
    processingId.value = id
    const response = await api.post(`/admin/students/${id}/reject`)
    if (response.data.status === 'success') {
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to reject student:', err)
    alert('Failed to reject student')
  } finally {
    processingId.value = null
  }
}

const suspendStudent = async (id) => {
  if (!confirm('Are you sure you want to suspend this student?')) return
  
  try {
    processingId.value = id
    const response = await api.post(`/admin/students/${id}/suspend`)
    if (response.data.status === 'success') {
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to suspend student:', err)
    alert('Failed to suspend student')
  } finally {
    processingId.value = null
  }
}

const reactivateStudent = async (id) => {
  if (!confirm('Are you sure you want to reactivate this student?')) return
  
  try {
    processingId.value = id
    const response = await api.post(`/admin/students/${id}/reactivate`)
    if (response.data.status === 'success') {
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to reactivate student:', err)
    alert('Failed to reactivate student')
  } finally {
    processingId.value = null
  }
}

onMounted(() => {
  fetchStudents()
})
</script>
