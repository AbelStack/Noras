import apiClient from './api'

/**
 * Study Tasks Service
 * Manages student's learning tasks with optional resource links
 */

export const tasksService = {
  /**
   * List the authenticated user's tasks
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.per_page - Items per page
   * @param {boolean} params.completed - Filter by completion status
   * @returns {Promise<Object>} { data: tasks[], pagination: {...} }
   */
  async listTasks(params = {}) {
    const response = await apiClient.get('/tasks', { params })
    return response.data
  },

  /**
   * Get a specific task by ID
   * @param {number} id - Task ID
   * @returns {Promise<Object>} Task data
   */
  async getTask(id) {
    const response = await apiClient.get(`/tasks/${id}`)
    return response.data
  },

  /**
   * Create a new study task
   * @param {Object} taskData
   * @param {string} taskData.title - Task title (required)
   * @param {string} taskData.description - Task description (optional)
   * @param {string} taskData.due_date - Due date in YYYY-MM-DD format (optional)
   * @param {number} taskData.course_material_id - Link to course material (optional)
   * @param {number} taskData.generated_note_id - Link to generated note (optional)
   * @param {number} taskData.generated_quiz_id - Link to generated quiz (optional)
   * @returns {Promise<Object>} Created task
   */
  async createTask(taskData) {
    const response = await apiClient.post('/tasks', taskData)
    return response.data
  },

  /**
   * Update an existing task
   * @param {number} id - Task ID
   * @param {Object} updates - Fields to update
   * @returns {Promise<Object>} Updated task
   */
  async updateTask(id, updates) {
    const response = await apiClient.put(`/tasks/${id}`, updates)
    return response.data
  },

  /**
   * Delete a task
   * @param {number} id - Task ID
   * @returns {Promise<Object>} Success response
   */
  async deleteTask(id) {
    const response = await apiClient.delete(`/tasks/${id}`)
    return response.data
  },

  /**
   * Mark a task as completed
   * @param {number} id - Task ID
   * @returns {Promise<Object>} Updated task
   */
  async completeTask(id) {
    const response = await apiClient.post(`/tasks/${id}/complete`)
    return response.data
  },

  /**
   * Reopen a completed task
   * @param {number} id - Task ID
   * @returns {Promise<Object>} Updated task
   */
  async reopenTask(id) {
    const response = await apiClient.post(`/tasks/${id}/reopen`)
    return response.data
  },
}
