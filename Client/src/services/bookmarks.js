import apiClient from './api'

/**
 * Bookmarks Service
 * Manages bookmarks for course materials, notes, and quizzes
 */

// Backend expects full class names for bookmarkable_type
const RESOURCE_TYPES = {
  MATERIAL: 'App\\Models\\CourseMaterial',
  NOTE: 'App\\Models\\GeneratedNote',
  QUIZ: 'App\\Models\\GeneratedQuiz',
}

export const bookmarksService = {
  RESOURCE_TYPES,

  /**
   * List the authenticated user's bookmarks
   * @param {Object} params - Query parameters
   * @param {number} params.page - Page number
   * @param {number} params.per_page - Items per page
   * @param {string} params.bookmarkable_type - Filter by resource type
   * @returns {Promise<Object>} { data: bookmarks[], pagination: {...} }
   */
  async listBookmarks(params = {}) {
    const response = await apiClient.get('/bookmarks', { params })
    return response.data
  },

  /**
   * Get a specific bookmark by ID
   * @param {number} id - Bookmark ID
   * @returns {Promise<Object>} Bookmark data
   */
  async getBookmark(id) {
    const response = await apiClient.get(`/bookmarks/${id}`)
    return response.data
  },

  /**
   * Create a new bookmark
   * @param {string} bookmarkableType - Resource type (use RESOURCE_TYPES constants)
   * @param {number} bookmarkableId - Resource ID
   * @returns {Promise<Object>} Created bookmark
   */
  async createBookmark(bookmarkableType, bookmarkableId) {
    const response = await apiClient.post('/bookmarks', {
      bookmarkable_type: bookmarkableType,
      bookmarkable_id: bookmarkableId,
    })
    return response.data
  },

  /**
   * Delete a bookmark
   * @param {number} id - Bookmark ID
   * @returns {Promise<Object>} Success response
   */
  async deleteBookmark(id) {
    const response = await apiClient.delete(`/bookmarks/${id}`)
    return response.data
  },

  /**
   * Helper: Create material bookmark
   */
  async bookmarkMaterial(materialId) {
    return this.createBookmark(RESOURCE_TYPES.MATERIAL, materialId)
  },

  /**
   * Helper: Create note bookmark
   */
  async bookmarkNote(noteId) {
    return this.createBookmark(RESOURCE_TYPES.NOTE, noteId)
  },

  /**
   * Helper: Create quiz bookmark
   */
  async bookmarkQuiz(quizId) {
    return this.createBookmark(RESOURCE_TYPES.QUIZ, quizId)
  },
}
