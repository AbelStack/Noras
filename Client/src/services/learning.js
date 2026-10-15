import apiClient from './api'

/**
 * Learning History & Progress Service
 * Tracks student's learning activities and progress metrics
 */

export const learningService = {
  /**
   * Get unified learning history (notes and quizzes combined)
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.per_page - Items per page
   * @returns {Promise<Object>} { data: history[], pagination: {...} }
   */
  async getHistory(params = {}) {
    const response = await apiClient.get('/learning/history', { params })
    return response.data
  },

  /**
   * Get learning progress summary
   * @returns {Promise<Object>} Progress metrics
   */
  async getProgress() {
    const response = await apiClient.get('/learning/progress')
    return response.data
  },
}
