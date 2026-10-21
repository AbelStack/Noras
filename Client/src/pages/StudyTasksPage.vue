<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <button 
            @click="$router.push('/dashboard')"
            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
          >
            <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
          </button>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Study Tasks</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Organize your learning goals and track progress</p>
          </div>
        </div>

        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-semibold text-xs sm:text-sm shadow-2xs transition-colors self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Task
        </button>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <button
          @click="filterTasks(null)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === null
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          All Tasks
        </button>
        <button
          @click="filterTasks(false)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === false
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          Active
        </button>
        <button
          @click="filterTasks(true)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition-all',
            currentFilter === true
              ? 'bg-primary-600 text-white shadow-sm'
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          Completed
        </button>
      </div>

      <!-- Error State -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <button
          @click="loadTasks(currentPage)"
          class="text-sm text-red-700 hover:text-red-800 underline font-medium"
        >
          Try Again
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="tasks" :count="5" />
      </div>

      <!-- Empty State -->
      <div v-else-if="tasks.length === 0 && !errorMessage" class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center shadow-sm min-h-[440px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No tasks found</h3>
        <p class="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto mb-5">
          {{ currentFilter === null ? 'Get started by creating your first study task.' : 'No tasks match this filter.' }}
        </p>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-semibold text-xs sm:text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-2xs transition-colors"
        >
          Create Task
        </button>
      </div>

      <!-- Overdue Tasks Banner Alert -->
      <div 
        v-if="overdueTasksCount > 0 && currentFilter !== true"
        class="bg-red-50 border border-red-200 rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-2xs"
      >
        <div class="flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-red-100 text-red-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-red-900">
              {{ overdueTasksCount }} Study {{ overdueTasksCount === 1 ? 'Task is' : 'Tasks are' }} Overdue!
            </h4>
            <p class="text-xs text-red-700 mt-0.5">
              These tasks passed their due date before being marked complete. Please review and complete or update them.
            </p>
          </div>
        </div>
        <button
          @click="currentFilter = false"
          class="text-xs font-bold text-red-800 bg-red-100 hover:bg-red-200 border border-red-300 px-3 py-1.5 rounded-xl transition-colors hidden sm:inline-flex"
        >
          View Active Tasks
        </button>
      </div>

      <!-- Tasks List -->
      <div v-else class="space-y-3 min-h-[460px]">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm hover:shadow-md hover:border-primary-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div class="flex items-start gap-4 flex-1 min-w-0">
            <!-- Icon Badge -->
            <div class="flex-shrink-0 mt-0.5">
              <span 
                :class="[
                  'inline-flex items-center justify-center w-10 h-10 rounded-xl border transition-colors',
                  task.completed 
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-600' 
                    : (isOverdue(task) ? 'bg-red-50 border-red-200 text-red-600' : 'bg-primary-50 border-primary-100 text-primary-600')
                ]"
              >
                <svg v-if="task.completed" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else-if="isOverdue(task)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              </span>
            </div>

            <!-- Task Content -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <h3
                  :class="[
                    'text-base sm:text-lg font-bold tracking-tight',
                    task.completed ? 'text-gray-400 line-through' : 'text-gray-900'
                  ]"
                >
                  {{ task.title }}
                </h3>

                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                    task.completed 
                      ? 'bg-emerald-100 text-emerald-800' 
                      : (isOverdue(task) ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-blue-100 text-primary-800')
                  ]"
                >
                  {{ task.completed ? 'Completed' : (isOverdue(task) ? 'Overdue' : 'Active') }}
                </span>

                <!-- Overdue Notice Badge -->
                <span 
                  v-if="isOverdue(task)"
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300 shadow-2xs"
                  title="This task has passed its due date!"
                >
                  <svg class="w-3.5 h-3.5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  <span>Overdue Notice &bull; Due {{ formatDueDate(task.due_date) }}</span>
                </span>

                <!-- Normal / Today Due Date Badge -->
                <span v-else-if="task.due_date" :class="getDueDateClass(task)">
                  <svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  Due {{ formatDueDate(task.due_date) }}
                </span>

                <span v-if="task.completed" class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Finished {{ formatDate(task.completed_at) }}
                </span>
              </div>
              
              <p v-if="task.description" class="text-sm text-gray-600 whitespace-pre-line leading-relaxed mb-2">
                {{ task.description }}
              </p>

              <!-- Linked Resources -->
              <div v-if="hasLinkedResources(task)" class="flex flex-wrap gap-2 pt-1">
                <router-link
                  v-if="task.course_material"
                  :to="`/offerings/${task.course_material.course_offering_id}/materials`"
                  class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors"
                >
                  <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                  </svg>
                  {{ task.course_material.title }}
                </router-link>

                <router-link
                  v-if="task.generated_note"
                  :to="`/notes/${task.generated_note.id}`"
                  class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 transition-colors"
                >
                  <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                  </svg>
                  Study Notes
                </router-link>

                <router-link
                  v-if="task.generated_quiz"
                  :to="`/quizzes/${task.generated_quiz.id}`"
                  class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors"
                >
                  <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                  Practice Quiz
                </router-link>

                <span
                  v-if="!task.course_material && !task.generated_note && !task.generated_quiz && (task.course_material_id || task.generated_note_id || task.generated_quiz_id)"
                  class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200"
                >
                  Resource unavailable
                </span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 sm:self-center ml-14 sm:ml-0 flex-shrink-0">
            <!-- Toggle Done Button -->
            <button
              @click="toggleTaskCompletion(task)"
              :disabled="actioningTaskId === task.id"
              :class="[
                'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold border transition-all shadow-sm',
                task.completed
                  ? 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'
                  : 'bg-emerald-600 text-white border-transparent hover:bg-emerald-700'
              ]"
              :title="task.completed ? 'Reopen this task' : 'Mark task as completed'"
            >
              <svg v-if="task.completed" class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ task.completed ? 'Reopen' : 'Mark Done' }}</span>
            </button>

            <!-- Edit Task -->
            <button
              @click="editTask(task)"
              class="p-2 text-gray-400 hover:text-primary-600 rounded-xl hover:bg-gray-100 transition-colors"
              title="Edit task"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>

            <!-- Delete Task -->
            <button
              @click="confirmDeleteTask(task)"
              class="p-2 text-gray-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition-colors"
              title="Delete task"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Dynamic Pagination -->
        <DynamicPagination
          v-if="pagination && pagination.total > 0"
          :currentPage="pagination.current_page"
          :totalPages="pagination.last_page"
          :totalItems="pagination.total"
          :perPage="pagination.per_page || 10"
          :showPerPage="false"
          itemLabel="tasks"
          @update:currentPage="changePage"
          class="pt-4 rounded-2xl border border-gray-200 shadow-sm"
        />
      </div>
    </div>

    <!-- Create/Edit Task Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100">
        <div class="p-6 sm:p-7">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 tracking-tight">
            {{ editingTask ? 'Edit Task' : 'New Task' }}
          </h2>

          <form @submit.prevent="saveTask">
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="taskForm.title"
                  type="text"
                  required
                  maxlength="255"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
                  placeholder="e.g., Review Chapter 3"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">
                  Description
                </label>
                <textarea
                  v-model="taskForm.description"
                  rows="3"
                  maxlength="5000"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
                  placeholder="Optional details about this task..."
                ></textarea>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">
                  Due Date
                </label>
                <input
                  v-model="taskForm.due_date"
                  type="date"
                  :min="today"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
                />
              </div>
            </div>

            <div v-if="formError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
              <p class="text-xs sm:text-sm text-red-600">{{ formError }}</p>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSaving"
                class="px-5 py-2 text-sm font-medium bg-primary-600 text-white rounded-xl hover:bg-primary-700 disabled:opacity-50 transition-colors shadow-sm"
              >
                {{ isSaving ? 'Saving...' : (editingTask ? 'Update Task' : 'Create Task') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import DynamicPagination from '../components/DynamicPagination.vue'
import { authService } from '../services/auth'
import { tasksService } from '../services/tasks'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'

const router = useRouter()
const notify = useNotification()
const user = ref(authService.getUser())
const tasks = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const currentFilter = ref(null)
const isLoading = ref(true)
const errorMessage = ref('')
const actioningTaskId = ref(null)

// Modal states
const showCreateModal = ref(false)
const editingTask = ref(null)
const isSaving = ref(false)
const isDeleting = ref(false)
const formError = ref('')

// Task form
const taskForm = ref({
  title: '',
  description: '',
  due_date: '',
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

onMounted(() => {
  loadTasks()
})

const loadTasks = async (page = 1) => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const params = { page, per_page: 15 }
    if (currentFilter.value !== null) {
      params.completed = currentFilter.value
    }
    
    const result = await tasksService.listTasks(params)
    tasks.value = result.data
    pagination.value = result.pagination
    currentPage.value = page
  } catch (error) {
    console.error('Failed to load tasks:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load tasks. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const filterTasks = (completedStatus) => {
  currentFilter.value = completedStatus
  loadTasks(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadTasks(page)
  }
}

const toggleTaskCompletion = async (task) => {
  if (actioningTaskId.value) return
  
  actioningTaskId.value = task.id
  try {
    if (task.completed) {
      await tasksService.reopenTask(task.id)
      notify.info('Task reopened.')
    } else {
      await tasksService.completeTask(task.id)
      notify.success('Task marked as completed!')
    }
    await loadTasks(currentPage.value)
  } catch (error) {
    console.error('Failed to toggle task completion:', error)
    notify.error(error.response?.data?.message || 'Failed to update task.')
  } finally {
    actioningTaskId.value = null
  }
}

const openCreateModal = () => {
  editingTask.value = null
  taskForm.value = {
    title: '',
    description: '',
    due_date: '',
  }
  formError.value = ''
  showCreateModal.value = true
}

const editTask = (task) => {
  editingTask.value = task
  taskForm.value = {
    title: task.title,
    description: task.description || '',
    due_date: task.due_date ? task.due_date.split('T')[0] : '',
  }
  formError.value = ''
  showCreateModal.value = true
}

const saveTask = async () => {
  if (isSaving.value) return
  
  formError.value = ''
  const title = (taskForm.value.title || '').trim()
  if (!title) {
    formError.value = 'Please enter a task title.'
    return
  }

  isSaving.value = true
  
  try {
    const taskData = { title }
    
    const desc = typeof taskForm.value.description === 'string' ? taskForm.value.description.trim() : ''
    if (desc) {
      taskData.description = desc
    } else if (editingTask.value) {
      taskData.description = null
    }

    if (taskForm.value.due_date) {
      taskData.due_date = taskForm.value.due_date
    } else if (editingTask.value) {
      taskData.due_date = null
    }
    
    if (editingTask.value) {
      await tasksService.updateTask(editingTask.value.id, taskData)
      notify.success('Task updated successfully.')
      await loadTasks(currentPage.value)
    } else {
      await tasksService.createTask(taskData)
      notify.success('Task created successfully.')
      // Reset filter and return to page 1 so newly created task is immediately visible
      currentFilter.value = null
      await loadTasks(1)
    }
    
    closeModal()
  } catch (error) {
    console.error('Failed to save task:', error)
    if (error.response?.data?.errors) {
      formError.value = Object.values(error.response.data.errors).flat().join(' ')
    } else {
      formError.value = error.response?.data?.message || error.message || 'Failed to save task. Please try again.'
    }
  } finally {
    isSaving.value = false
  }
}

const confirmDeleteTask = async (task) => {
  const confirmed = await notify.confirm({
    title: 'Delete Task?',
    message: `Are you sure you want to delete "${task.title}"? This action cannot be undone.`,
    confirmText: 'Delete Task',
    cancelText: 'Cancel',
    type: 'danger',
  })

  if (!confirmed) return

  isDeleting.value = true
  try {
    await tasksService.deleteTask(task.id)
    notify.success('Task deleted successfully.')
    await loadTasks(currentPage.value)
  } catch (error) {
    console.error('Failed to delete task:', error)
    notify.error(error.response?.data?.message || 'Failed to delete task. Please try again.')
  } finally {
    isDeleting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingTask.value = null
  taskForm.value = {
    title: '',
    description: '',
    due_date: '',
  }
  formError.value = ''
}

const hasLinkedResources = (task) => {
  return task.course_material || task.generated_note || task.generated_quiz ||
         task.course_material_id || task.generated_note_id || task.generated_quiz_id
}

const isOverdue = (task) => {
  if (task.completed || !task.due_date) return false
  const dueDate = new Date(task.due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  dueDate.setHours(0, 0, 0, 0)
  return dueDate < today
}

const overdueTasksCount = computed(() => {
  return tasks.value.filter(t => isOverdue(t)).length
})

const getDueDateClass = (task) => {
  if (task.completed) return 'text-gray-400 text-xs'
  
  const dueDate = new Date(task.due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  dueDate.setHours(0, 0, 0, 0)
  
  if (dueDate < today) return 'text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full text-xs font-semibold'
  if (dueDate.getTime() === today.getTime()) return 'text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-xs font-semibold'
  return 'text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-full text-xs font-medium'
}

const formatDueDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  date.setHours(0, 0, 0, 0)
  
  const diffTime = date - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return 'today'
  if (diffDays === 1) return 'tomorrow'
  if (diffDays === -1) return 'yesterday'
  if (diffDays < -1) return `${Math.abs(diffDays)} days ago`
  if (diffDays < 7) return `in ${diffDays} days`
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>
