import api from './api'

export const quizzesService = {
  /**
   * Generate quiz from a course material
   * POST /api/v1/quizzes/generate/{materialId}
   * Rate limit: 10 generations per hour per user
   * 
   * @param {number} materialId - Course material ID
   * @param {number} questionCount - Number of questions (1-20, default: 5)
   * @param {number} optionsPerQuestion - Options per question (2-6, default: 4)
   * @returns {Promise<{quiz, material}>}
   */
  async generateQuiz(materialId, questionCount = 5, optionsPerQuestion = 4) {
    const response = await api.post(`/quizzes/generate/${materialId}`, {
      question_count: questionCount,
      options_per_question: optionsPerQuestion,
    })
    return response.data.data
  },

  /**
   * Get all quizzes for the authenticated user
   * GET /api/v1/quizzes
   * 
   * @param {number} page - Page number (default: 1)
   * @param {number} perPage - Items per page (default: 20, max: 100)
   * @returns {Promise<{quizzes, pagination}>}
   */
  async getQuizzes(page = 1, perPage = 20) {
    const response = await api.get('/quizzes', {
      params: { page, per_page: perPage },
    })
    return response.data.data
  },

  /**
   * Get a specific quiz with questions and options
   * WARNING: Backend exposes is_correct flag for all options immediately
   * 
   * GET /api/v1/quizzes/{id}
   * 
   * @param {number} id - Quiz ID
   * @returns {Promise<{quiz, material, questions}>}
   */
  async getQuiz(id) {
    const response = await api.get(`/quizzes/${id}`)
    return response.data.data
  },

  /**
   * Delete a generated quiz
   * DELETE /api/v1/quizzes/{id}
   * 
   * @param {number} id - Quiz ID
   * @returns {Promise<void>}
   */
  async deleteQuiz(id) {
    await api.delete(`/quizzes/${id}`)
  },

  /**
   * Submit quiz answers and record student attempt
   * POST /api/v1/quizzes/{id}/submit
   * 
   * @param {number} id - Quiz ID
   * @param {Object} answers - { question_id: option_id }
   * @returns {Promise<{attempt_id, score, total_questions, percentage, question_results}>}
   */
  async submitQuiz(id, answers) {
    const response = await api.post(`/quizzes/${id}/submit`, { answers })
    return response.data.data
  },

  /**
   * Get past attempts for a quiz
   * GET /api/v1/quizzes/{id}/attempts
   * 
   * @param {number} id - Quiz ID
   * @returns {Promise<Array>}
   */
  async getQuizAttempts(id) {
    const response = await api.get(`/quizzes/${id}/attempts`)
    return response.data.data.attempts
  },
}

