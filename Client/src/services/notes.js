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

  /**
   * Download a generated note as a Markdown file by ID
   * 
   * @param {number} id - Note ID
   * @param {string} defaultFilename - Target file name
   */
  async downloadNote(id, defaultFilename = 'study-notes.md') {
    const data = await this.getNote(id)
    const content = data.note?.content || ''
    this.downloadNoteContent(content, defaultFilename)
  },

  /**
   * Trigger browser download for markdown text content
   * 
   * @param {string} content - Markdown text content
   * @param {string} filename - Target filename
   */
  downloadNoteContent(content, filename = 'study-notes.md') {
    const blob = new Blob([content], { type: 'text/markdown;charset=utf-8;' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename.endsWith('.md') ? filename : `${filename}.md`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}

