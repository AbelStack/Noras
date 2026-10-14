import api from './api'

export const notesService = {
  /**
   * Generate study notes from a course material
   * POST /api/v1/notes/generate/{materialId}
   * Rate limit: 10 generations per hour per user
   * 
   * @param {number} materialId - Course material ID
   * @param {boolean} regenerate - Force regeneration even if notes exist
   * @returns {Promise<{note, material}>}
   */
  async generateNotes(materialId, regenerate = false) {
    const response = await api.post(`/notes/generate/${materialId}`, {
      regenerate,
    })
    return response.data.data
  },

  /**
   * Get all notes for the authenticated user
   * GET /api/v1/notes
   * 
   * @param {number} page - Page number (default: 1)
   * @param {number} perPage - Items per page (default: 20, max: 100)
   * @returns {Promise<{notes, pagination}>}
   */
  async getNotes(page = 1, perPage = 20) {
    const response = await api.get('/notes', {
      params: { page, per_page: perPage },
    })
    return response.data.data
  },

  /**
   * Get a specific note by ID
   * GET /api/v1/notes/{id}
   * 
   * @param {number} id - Note ID
   * @returns {Promise<{note, material}>}
   */
  async getNote(id) {
    const response = await api.get(`/notes/${id}`)
    return response.data.data
  },

  /**
   * Delete a generated note
   * DELETE /api/v1/notes/{id}
   * 
   * @param {number} id - Note ID
   * @returns {Promise<void>}
   */
  async deleteNote(id) {
    await api.delete(`/notes/${id}`)
  },
}
