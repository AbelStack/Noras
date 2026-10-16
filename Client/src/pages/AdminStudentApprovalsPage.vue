<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Student Approvals</h1>
            <p class="text-sm text-gray-600 mt-1">Review student registrations and manage enrollment access</p>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
          <button
            v-for="status in statuses"
            :key="status.value"
            @click="filterStatus = status.value"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium transition-all',
              filterStatus === status.value
                ? 'bg-primary-600 text-white shadow-sm'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ status.label }} ({{ getStatusCount(status.value) }})
          </button>
        </div>

        <div class="relative w-full md:w-72">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search student or ID..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
          />
        </div>
      </div>

      <!-- Loading State (Skeleton Table) -->
      <div v-if="loading" class="py-2">
        <SkeletonLoader type="table" :rows="6" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ error }}</p>
        </div>
        <button @click="fetchStudents" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredStudents.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No students found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto">
            {{ searchQuery ? 'No students matched your search criteria.' : `No students currently have the status "${filterStatus}".` }}
          </p>
        </div>

        <!-- Student Cards List -->
        <div v-else class="space-y-4">
          <div
            v-for="student in filteredStudents"
            :key="student.id"
            class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-6"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg flex-shrink-0 shadow-sm">
                  {{ getInitials(student.name) }}
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-bold text-gray-900 truncate">{{ student.name }}</h3>
                    <span :class="getStatusBadgeClass(student.account_status)">
                      {{ student.account_status }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-500 truncate">{{ student.email }}</p>
                </div>
              </div>

              <!-- Details Grid -->
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 text-xs">
                <div>
                  <span class="text-gray-400 block font-medium">Student ID</span>
                  <span class="font-mono font-semibold text-gray-800">{{ student.student_id || 'N/A' }}</span>
                </div>
                <div>
                  <span class="text-gray-400 block font-medium">Registered Date</span>
                  <span class="font-medium text-gray-800">{{ formatDate(student.created_at) }}</span>
                </div>
                <div v-if="student.approved_at">
                  <span class="text-gray-400 block font-medium">Approved Date</span>
                  <span class="font-medium text-gray-800">{{ formatDate(student.approved_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100 flex-shrink-0">
              <template v-if="student.account_status === 'pending'">
                <button
                  @click="approveStudent(student)"
                  :disabled="processingId === student.id"
                  class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 active:bg-emerald-800 transition-colors shadow-sm disabled:opacity-50"
                >
                  <svg v-if="processingId === student.id" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Approve</span>
                </button>
                <button
                  @click="rejectStudent(student)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl hover:bg-rose-100 transition-colors disabled:opacity-50"
                >
                  Reject
                </button>
              </template>

              <template v-else-if="student.account_status === 'approved'">
                <button
                  @click="suspendStudent(student)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold rounded-xl hover:bg-amber-100 transition-colors disabled:opacity-50"
                >
                  Suspend
                </button>
              </template>

              <template v-else-if="student.account_status === 'suspended' || student.account_status === 'rejected'">
                <button
                  @click="reactivateStudent(student)"
                  :disabled="processingId === student.id"
                  class="px-4 py-2 bg-blue-50 text-primary-700 border border-blue-200 text-xs font-semibold rounded-xl hover:bg-blue-100 transition-colors disabled:opacity-50"
                >
                  Reactivate
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const students = ref([])
const loading = ref(true)
const error = ref(null)
const filterStatus = ref('all')
const searchQuery = ref('')
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
  { path: '/admin/notifications', label: 'Notifications', icon: 'ReportsIcon' },
  { path: '/admin/campus', label: 'Campuses', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const filteredStudents = computed(() => {
  return students.value.filter(s => {
    const matchesStatus = filterStatus.value === 'all' || s.account_status === filterStatus.value
    const matchesSearch = !searchQuery.value ||
      s.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      s.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      s.student_id?.toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesStatus && matchesSearch
  })
})

const getStatusCount = (status) => {
  if (status === 'all') return students.value.length
  return students.value.filter(s => s.account_status === status).length
}

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getStatusBadgeClass = (status) => {
  const baseClasses = 'text-xs font-semibold px-2.5 py-0.5 rounded-full capitalize'
  const statusClasses = {
    pending: 'bg-amber-50 text-amber-700 border border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border border-rose-200',
    suspended: 'bg-gray-100 text-gray-700 border border-gray-200',
  }
  return `${baseClasses} ${statusClasses[status] || 'bg-gray-100 text-gray-700'}`
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const fetchStudents = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/students')
    if (response.data.status === 'success') {
      students.value = response.data.data.students || []
    }
  } catch (err) {
    console.error('Failed to fetch students:', err)
    error.value = 'Failed to load student records'
  } finally {
    loading.value = false
  }
}

const approveStudent = async (student) => {
  const confirmed = await notify.confirm({
    title: `Approve Student "${student.name}"?`,
    message: 'Are you sure you want to approve this student account? They will immediately receive access to student materials and tools.',
    confirmText: 'Approve Student',
    type: 'primary',
  })

  if (!confirmed) return
  
  try {
    processingId.value = student.id
    const response = await api.post(`/admin/students/${student.id}/approve`)
    if (response.data.status === 'success') {
      notify.success(`Student "${student.name}" approved successfully.`)
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to approve student:', err)
    notify.error(err.response?.data?.message || 'Failed to approve student')
  } finally {
    processingId.value = null
  }
}

const rejectStudent = async (student) => {
  const confirmed = await notify.confirm({
    title: `Reject Student "${student.name}"?`,
    message: 'Are you sure you want to reject this student registration? This action cannot be undone.',
    confirmText: 'Reject Student',
    type: 'danger',
  })

  if (!confirmed) return
  
  try {
    processingId.value = student.id
    const response = await api.post(`/admin/students/${student.id}/reject`)
    if (response.data.status === 'success') {
      notify.success(`Student "${student.name}" rejected.`)
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to reject student:', err)
    notify.error(err.response?.data?.message || 'Failed to reject student')
  } finally {
    processingId.value = null
  }
}

const suspendStudent = async (student) => {
  const confirmed = await notify.confirm({
    title: `Suspend Student "${student.name}"?`,
    message: 'Are you sure you want to suspend this student account? Their access will be temporarily locked.',
    confirmText: 'Suspend Account',
    type: 'danger',
  })

  if (!confirmed) return
  
  try {
    processingId.value = student.id
    const response = await api.post(`/admin/students/${student.id}/suspend`)
    if (response.data.status === 'success') {
      notify.success(`Student "${student.name}" suspended.`)
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to suspend student:', err)
    notify.error(err.response?.data?.message || 'Failed to suspend student')
  } finally {
    processingId.value = null
  }
}

const reactivateStudent = async (student) => {
  const confirmed = await notify.confirm({
    title: `Reactivate Student "${student.name}"?`,
    message: 'Are you sure you want to reactivate this student account?',
    confirmText: 'Reactivate Account',
    type: 'primary',
  })

  if (!confirmed) return
  
  try {
    processingId.value = student.id
    const response = await api.post(`/admin/students/${student.id}/reactivate`)
    if (response.data.status === 'success') {
      notify.success(`Student "${student.name}" reactivated successfully.`)
      await fetchStudents()
    }
  } catch (err) {
    console.error('Failed to reactivate student:', err)
    notify.error(err.response?.data?.message || 'Failed to reactivate student')
  } finally {
    processingId.value = null
  }
}

onMounted(() => {
  fetchStudents()
})
</script>
