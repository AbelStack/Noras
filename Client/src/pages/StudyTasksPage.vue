<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50/30">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <img src="/Noras logo-sm.png" alt="Noras" class="h-8 w-auto" />
            <span class="ml-2 text-xl font-bold text-gray-900">Noras</span>
          </div>
          <div class="flex items-center space-x-4">
            <router-link to="/dashboard" class="text-sm text-gray-600 hover:text-gray-900">Dashboard</router-link>
            <span class="text-sm text-gray-600">{{ user?.name }}</span>
            <button
              @click="handleLogout"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Study Tasks</h1>
          <p class="mt-2 text-gray-600">Organize your learning goals and track progress</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          + New Task
        </button>
      </div>

      <!-- Filter Tabs -->
      <div class="mb-6 flex space-x-2">
        <button
          @click="filterTasks(null)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === null
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          All Tasks
        </button>
        <button
          @click="filterTasks(false)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === false
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Active
        </button>
        <button
          @click="filterTasks(true)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === true
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Completed
        </button>
      </div>

      <!-- Error State -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <p class="text-red-600">{{ errorMessage }}</p>
        <button
          @click="loadTasks"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading tasks...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="tasks.length === 0 && !errorMessage" class="text-center py-12 bg-white rounded-xl shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ currentFilter === null ? 'Get started by creating your first study task.' : 'No tasks match this filter.' }}
        </p>
        <button
          @click="showCreateModal = true"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Task
        </button>
      </div>

      <!-- Tasks List -->
      <div v-else class="space-y-4">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4 flex-1">
              <!-- Completion Checkbox -->
              <button
                @click="toggleTaskCompletion(task)"
                :disabled="actioningTaskId === task.id"
                class="mt-1 flex-shrink-0"
              >
                <div
                  :class="[
                    'w-6 h-6 rounded border-2 flex items-center justify-center transition-colors',
                    task.completed
                      ? 'bg-green-500 border-green-500'
                      : 'border-gray-300 hover:border-gray-400'
                  ]"
                >
                  <svg v-if="task.completed" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </button>

              <!-- Task Content -->
              <div class="flex-1">
                <h3
                  :class="[
                    'text-lg font-semibold',
                    task.completed ? 'text-gray-500 line-through' : 'text-gray-900'
                  ]"
                >
                  {{ task.title }}
                </h3>
                
                <p v-if="task.description" class="mt-1 text-gray-600 whitespace-pre-line">
                  {{ task.description }}
                </p>

                <!-- Due Date & Status -->
                <div class="mt-2 flex items-center space-x-3 text-sm">
                  <span v-if="task.due_date" :class="getDueDateClass(task)">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Due {{ formatDueDate(task.due_date) }}
                  </span>
                  
                  <span v-if="task.completed" class="text-green-600">
                    ✓ Completed {{ formatDate(task.completed_at) }}
                  </span>
                </div>

                <!-- Linked Resources -->
                <div v-if="hasLinkedResources(task)" class="mt-3 flex flex-wrap gap-2">
                  <router-link
                    v-if="task.course_material"
                    :to="`/offerings/${task.course_material.course_offering_id}/materials`"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-50 text-red-700 hover:bg-red-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ task.course_material.title }}
                  </router-link>

                  <router-link
                    v-if="task.generated_note"
                    :to="`/notes/${task.generated_note.id}`"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 text-blue-700 hover:bg-blue-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                    </svg>
                    Study Notes
                  </router-link>

                  <router-link
                    v-if="task.generated_quiz"
                    :to="`/quizzes/${task.generated_quiz.id}`"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-50 text-green-700 hover:bg-green-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                      <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Practice Quiz
                  </router-link>

                  <span
                    v-if="!task.course_material && !task.generated_note && !task.generated_quiz && (task.course_material_id || task.generated_note_id || task.generated_quiz_id)"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600"
                  >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                    </svg>
                    Resource unavailable
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="editTask(task)"
                class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                title="Edit task"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button
                @click="confirmDelete(task)"
                class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                title="Delete task"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
          <nav class="inline-flex rounded-md shadow-sm space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Create/Edit Task Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">
            {{ editingTask ? 'Edit Task' : 'New Task' }}
          </h2>

          <form @submit.prevent="saveTask">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="taskForm.title"
                  type="text"
                  required
                  maxlength="255"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., Review Chapter 3"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Description
                </label>
                <textarea
                  v-model="taskForm.description"
                  rows="3"
                  maxlength="5000"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Optional details about this task..."
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Due Date
                </label>
                <input
                  v-model="taskForm.due_date"
                  type="date"
                  :min="today"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            <div v-if="formError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-600">{{ formError }}</p>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSaving"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ isSaving ? 'Saving...' : (editingTask ? 'Update Task' : 'Create Task') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Delete Task</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete "{{ taskToDelete?.title }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false; taskToDelete = null"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="deleteTask"
            :disabled="isDeleting"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ isDeleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { tasksService } from '../services/tasks'

const router = useRouter()
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
const showDeleteModal = ref(false)
const editingTask = ref(null)
const taskToDelete = ref(null)
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
    } else {
      await tasksService.completeTask(task.id)
    }
    await loadTasks(currentPage.value)
  } catch (error) {
    console.error('Failed to toggle task completion:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to update task. Please try again.'
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    actioningTaskId.value = null
  }
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
  
  isSaving.value = true
  formError.value = ''
  
  try {
    const taskData = {
      title: taskForm.value.title.trim(),
      description: taskForm.value.description.trim() || null,
      due_date: taskForm.value.due_date || null,
    }
    
    if (editingTask.value) {
      await tasksService.updateTask(editingTask.value.id, taskData)
    } else {
      await tasksService.createTask(taskData)
    }
    
    await loadTasks(currentPage.value)
    closeModal()
  } catch (error) {
    console.error('Failed to save task:', error)
    formError.value = error.response?.data?.message || 'Failed to save task. Please try again.'
  } finally {
    isSaving.value = false
  }
}

const confirmDelete = (task) => {
  taskToDelete.value = task
  showDeleteModal.value = true
}

const deleteTask = async () => {
  if (isDeleting.value) return
  
  isDeleting.value = true
  try {
    await tasksService.deleteTask(taskToDelete.value.id)
    showDeleteModal.value = false
    taskToDelete.value = null
    await loadTasks(currentPage.value)
  } catch (error) {
    console.error('Failed to delete task:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to delete task. Please try again.'
    showDeleteModal.value = false
    taskToDelete.value = null
    window.scrollTo({ top: 0, behavior: 'smooth' })
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

const getDueDateClass = (task) => {
  if (task.completed) return 'text-gray-500'
  
  const dueDate = new Date(task.due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  dueDate.setHours(0, 0, 0, 0)
  
  if (dueDate < today) return 'text-red-600 font-semibold'
  if (dueDate.getTime() === today.getTime()) return 'text-orange-600 font-semibold'
  return 'text-gray-600'
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

const handleLogout = async () => {
  try {
    await authService.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
    router.push('/login')
  }
}
</script>
