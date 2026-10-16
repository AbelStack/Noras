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
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Courses Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage academic courses, catalog codes, and credit hours</p>
          </div>
          <button 
            @click="openAddModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 active:bg-primary-800 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Course
          </button>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
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
        <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Department:</label>
            <select 
              v-model="filterDepartment"
              class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700 max-w-[220px]"
            >
              <option value="all">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }} {{ dept.university ? `(${dept.university.name})` : '' }}
              </option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Status:</label>
            <select 
              v-model="filterStatus"
              class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="all">All Courses</option>
              <option value="active">Active Only</option>
              <option value="inactive">Inactive Only</option>
            </select>
          </div>
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
        <button @click="fetchCourses" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Course List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredCourses.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No courses found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            {{ searchQuery || filterStatus !== 'all' || filterDepartment !== 'all' ? 'No courses match your filter criteria.' : 'Create academic courses to enable curriculum course offerings.' }}
          </p>
          <button 
            v-if="!searchQuery && filterStatus === 'all' && filterDepartment === 'all'"
            @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add your first course
          </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Code</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Title</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department & Curriculum</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Credits</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="course in filteredCourses" :key="course.id" class="hover:bg-gray-50/80 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-primary-700 border border-blue-100">
                      {{ course.code }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-bold text-gray-900">{{ course.title || course.name }}</div>
                    <div v-if="course.description" class="text-xs text-gray-500 mt-0.5 line-clamp-1 max-w-sm">
                      {{ course.description }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="course.offerings && course.offerings.length > 0" class="flex flex-wrap gap-1.5 max-w-sm">
                      <span 
                        v-for="off in getCourseOfferingsSummary(course)" 
                        :key="off.key"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-medium border border-indigo-100 shadow-xs"
                      >
                        <span class="font-semibold">{{ off.deptName }}</span>
                        <span class="text-indigo-300">|</span>
                        <span>Yr {{ off.years.join(', ') }} (S{{ off.semesters.join(',') }})</span>
                      </span>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">No department assigned</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs font-medium text-gray-700">
                      {{ course.credit_hours ?? course.credits ?? 3 }} credit hours
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full"
                      :class="course.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                    >
                      {{ course.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button 
                      @click="openEditModal(course)" 
                      class="text-xs font-medium text-primary-600 hover:text-primary-800 px-2.5 py-1 rounded-md hover:bg-primary-50 transition-colors mr-2"
                    >
                      Edit
                    </button>
                    <button 
                      @click="deleteCourse(course)" 
                      class="text-xs font-medium text-red-600 hover:text-red-800 px-2.5 py-1 rounded-md hover:bg-red-50 transition-colors"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Modal (Add / Edit) -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div 
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
            @click.stop
          >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <h3 class="text-lg font-bold text-gray-900">
                {{ isEditing ? 'Edit Course' : 'Add New Course' }}
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveCourse" class="p-6 space-y-4">
              <!-- Error banner if any -->
              <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                {{ formError }}
              </div>

              <!-- Course Code -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Course Code <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="form.code"
                  type="text" 
                  required
                  maxlength="20"
                  placeholder="e.g. CS101, IT204, or MATH101"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all uppercase"
                />
              </div>

              <!-- Course Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Course Title <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="form.title"
                  type="text" 
                  required
                  placeholder="e.g. Introduction to Computer Science"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>

              <!-- Credit Hours -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Credit Hours <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.number="form.credit_hours"
                  type="number" 
                  required
                  min="1"
                  max="10"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
                <p class="text-xs text-gray-400 mt-1">Value between 1 and 10.</p>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Description
                </label>
                <textarea 
                  v-model.trim="form.description"
                  rows="3"
                  placeholder="Summary of course content, objectives, or syllabus overview..."
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all resize-none"
                ></textarea>
              </div>

              <!-- Department & Curriculum Assignment -->
              <div class="space-y-3 pt-1">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Assign to Department <span class="text-primary-600 font-normal">(Curriculum)</span>
                  </label>
                  <select 
                    v-model="form.department_id"
                    @change="onDepartmentSelect"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all bg-white text-gray-900"
                  >
                    <option value="">-- No Department (General Course) --</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                      {{ dept.name }} {{ dept.university ? `(${dept.university.name})` : '' }}
                    </option>
                  </select>
                  <p class="text-[11px] text-gray-400 mt-1">
                    Select a department to automatically make this course available in that department's student curriculum.
                  </p>
                </div>

                <!-- Study Years & Semester (shown when department selected) -->
                <div v-if="form.department_id" class="p-4 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3">
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Study Year(s) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                      <button 
                        type="button" 
                        @click="form.study_years = [2, 3, 4, 5]" 
                        class="px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors font-semibold text-[11px]"
                      >
                        Years 2–5 (SE)
                      </button>
                      <button 
                        type="button" 
                        @click="form.study_years = [2, 3, 4]" 
                        class="px-2 py-0.5 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors font-semibold text-[11px]"
                      >
                        Years 2–4 (CS)
                      </button>
                      <button 
                        type="button" 
                        @click="form.study_years = [1, 2, 3, 4, 5]" 
                        class="px-2 py-0.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors font-semibold text-[11px]"
                      >
                        All
                      </button>
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <label 
                      v-for="yr in [1, 2, 3, 4, 5]" 
                      :key="yr"
                      :class="[
                        'flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer transition-all select-none',
                        form.study_years.includes(yr)
                          ? 'bg-primary-600 text-white border-primary-600 shadow-xs'
                          : 'bg-white text-gray-700 border-gray-300 hover:border-primary-400'
                      ]"
                    >
                      <input 
                        type="checkbox" 
                        :value="yr" 
                        v-model="form.study_years" 
                        class="sr-only"
                      />
                      <span>Year {{ yr }}</span>
                      <svg v-if="form.study_years.includes(yr)" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </label>
                  </div>

                  <p class="text-[11px] text-gray-500">
                    Software Engineering spans Years 2 to 5; Computer Science spans Years 2 to 4.
                  </p>

                  <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Semester</label>
                      <select 
                        v-model="form.semester"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                      >
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="both">Both Semesters</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Academic Year</label>
                      <input 
                        v-model.number="form.academic_year"
                        type="number"
                        min="2020"
                        max="2035"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Active Status -->
              <div class="flex items-center gap-3 pt-1">
                <input 
                  id="course-active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                />
                <label for="course-active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">
                  Active (available for semester offerings and materials)
                </label>
              </div>

              <!-- Modal Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm"
                >
                  <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ saving ? 'Saving...' : (isEditing ? 'Update Course' : 'Create Course') }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const courses = ref([])
const departments = ref([])
const loading = ref(true)
const error = ref(null)

const searchQuery = ref('')
const filterStatus = ref('all')
const filterDepartment = ref('all')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  code: '',
  title: '',
  description: '',
  credit_hours: 3,
  is_active: true,
  department_id: '',
  study_years: [2],
  semester: '1',
  academic_year: 2026,
})

const navigationItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  { path: '/admin/students', label: 'Student Approvals', icon: 'ApprovalIcon' },
  { path: '/admin/campus', label: 'Campuses', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const filteredCourses = computed(() => {
  return courses.value.filter(c => {
    const code = c.code || ''
    const title = c.title || c.name || ''
    const matchesSearch = !searchQuery.value ||
      code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      title.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = filterStatus.value === 'all' ||
      (filterStatus.value === 'active' && c.is_active) ||
      (filterStatus.value === 'inactive' && !c.is_active)

    const matchesDepartment = filterDepartment.value === 'all' ||
      (c.offerings && c.offerings.some(o => o.department_id === filterDepartment.value))

    return matchesSearch && matchesStatus && matchesDepartment
  })
})

const fetchCourses = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/courses')
    if (response.data.status === 'success') {
      courses.value = response.data.data.courses || []
    }
  } catch (err) {
    console.error('Failed to fetch courses:', err)
    error.value = 'Failed to load courses'
  } finally {
    loading.value = false
  }
}

const fetchDepartments = async () => {
  try {
    const response = await api.get('/admin/departments')
    if (response.data.status === 'success') {
      departments.value = response.data.data.departments || []
    }
  } catch (err) {
    console.error('Failed to fetch departments:', err)
  }
}

const getCourseOfferingsSummary = (course) => {
  if (!course.offerings || course.offerings.length === 0) return []
  const depts = {}
  course.offerings.forEach(off => {
    const deptName = off.department?.name || 'Department'
    const key = off.department_id || deptName
    if (!depts[key]) {
      depts[key] = {
        key,
        deptName,
        years: new Set(),
        semesters: new Set(),
      }
    }
    if (off.study_year) depts[key].years.add(off.study_year)
    if (off.semester) depts[key].semesters.add(off.semester)
  })
  return Object.values(depts).map(d => ({
    key: d.key,
    deptName: d.deptName,
    years: Array.from(d.years).sort((a, b) => a - b),
    semesters: Array.from(d.semesters).sort(),
  }))
}

const onDepartmentSelect = () => {
  if (!form.value.department_id) {
    form.value.study_years = [2]
    return
  }

  const selectedDept = departments.value.find(d => d.id === form.value.department_id)
  if (selectedDept) {
    const nameLower = selectedDept.name.toLowerCase()
    const codeLower = (selectedDept.code || '').toLowerCase()
    if (nameLower.includes('software') || codeLower === 'se') {
      form.value.study_years = [2, 3, 4, 5]
    } else if (nameLower.includes('computer') || codeLower === 'cs') {
      form.value.study_years = [2, 3, 4]
    } else {
      form.value.study_years = [2]
    }
  }
}

const openAddModal = () => {
  isEditing.value = false
  editingId.value = null
  form.value = {
    code: '',
    title: '',
    description: '',
    credit_hours: 3,
    is_active: true,
    department_id: '',
    study_years: [2],
    semester: '1',
    academic_year: 2026,
  }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (course) => {
  isEditing.value = true
  editingId.value = course.id
  
  const existingOffering = course.offerings && course.offerings.length > 0 ? course.offerings[0] : null
  const existingYears = course.offerings && course.offerings.length > 0 
    ? [...new Set(course.offerings.map(o => o.study_year))].sort((a, b) => a - b)
    : [2]

  form.value = {
    code: course.code,
    title: course.title || course.name || '',
    description: course.description || '',
    credit_hours: course.credit_hours ?? course.credits ?? 3,
    is_active: Boolean(course.is_active),
    department_id: existingOffering ? existingOffering.department_id : '',
    study_years: existingYears,
    semester: existingOffering ? existingOffering.semester : '1',
    academic_year: existingOffering?.academic_year || 2026,
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
}

const saveCourse = async () => {
  if (!form.value.code.trim() || !form.value.title.trim()) {
    formError.value = 'Please provide both course code and course title.'
    return
  }
  if (!form.value.credit_hours || form.value.credit_hours < 1 || form.value.credit_hours > 10) {
    formError.value = 'Credit hours must be an integer between 1 and 10.'
    return
  }
  if (form.value.department_id && (!form.value.study_years || form.value.study_years.length === 0)) {
    formError.value = 'Please select at least one study year for the curriculum assignment.'
    return
  }

  saving.value = true
  formError.value = ''

  try {
    const payload = {
      code: form.value.code.trim().toUpperCase(),
      title: form.value.title.trim(),
      description: form.value.description ? form.value.description.trim() : null,
      credit_hours: parseInt(form.value.credit_hours),
      is_active: form.value.is_active,
      department_id: form.value.department_id || null,
      study_years: form.value.study_years,
      semester: form.value.semester,
      academic_year: form.value.academic_year || 2026,
    }

    if (isEditing.value) {
      await api.put(`/admin/courses/${editingId.value}`, payload)
      notify.success(`Course "${payload.code}" updated successfully.`)
    } else {
      await api.post('/admin/courses', payload)
      notify.success(`Course "${payload.code}" created and assigned to curriculum.`)
    }

    closeModal()
    await fetchCourses()
  } catch (err) {
    console.error('Failed to save course:', err)
    if (err.response?.data?.message) {
      formError.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      const firstError = Object.values(err.response.data.errors)[0]
      formError.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else {
      formError.value = 'An error occurred while saving the course.'
    }
  } finally {
    saving.value = false
  }
}

const deleteCourse = async (course) => {
  const confirmed = await notify.confirm({
    title: `Delete Course "${course.code}"?`,
    message: `Are you sure you want to permanently delete "${course.title || course.code}"? Associated offerings and course materials will be removed.`,
    confirmText: 'Delete Course',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/courses/${course.id}?force=true`)
    if (response.data.status === 'success') {
      notify.success(`Course "${course.code}" deleted successfully.`)
      await fetchCourses()
    } else {
      notify.error(response.data.message || 'Failed to delete course')
    }
  } catch (err) {
    console.error('Failed to delete course:', err)
    const msg = err.response?.data?.message || 'Failed to delete course'
    notify.error(msg)
  }
}

onMounted(() => {
  fetchCourses()
  fetchDepartments()
})
</script>
