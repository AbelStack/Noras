import api from './api'

export const catalogService = {
  // Universities
  async getUniversities() {
    const response = await api.get('/universities')
    return response.data.data.universities
  },

  async getUniversity(id) {
    const response = await api.get(`/universities/${id}`)
    return response.data.data.university
  },

  // Departments
  async getDepartments(universityId = null) {
    const params = universityId ? { university_id: universityId } : {}
    const response = await api.get('/departments', { params })
    return response.data.data.departments
  },

  async getDepartment(id) {
    const response = await api.get(`/departments/${id}`)
    return response.data.data.department
  },

  // Courses
  async getCourses(filters = {}) {
    const response = await api.get('/courses', { params: filters })
    return response.data.data.courses
  },

  async getCourse(id) {
    const response = await api.get(`/courses/${id}`)
    return response.data.data.course
  },

  // Course Offerings
  async getCourseOfferings(filters = {}) {
    const response = await api.get('/course-offerings', { params: filters })
    return response.data.data.course_offerings
  },

  async getCourseOffering(id) {
    const response = await api.get(`/course-offerings/${id}`)
    return response.data.data.course_offering
  },

  // Materials
  async getMaterials(filters = {}, page = 1) {
    const params = { ...filters, page }
    const response = await api.get('/materials', { params })
    return {
      materials: response.data.data.materials,
      pagination: response.data.data.pagination,
    }
  },

  async getMaterial(id) {
    const response = await api.get(`/materials/${id}`)
    return response.data.data.material
  },

  async getCourseMaterials(offeringId, params = {}) {
    return this.getMaterials({ course_offering_id: offeringId, ...params })
  },

  async downloadMaterial(id, defaultFilename = 'document') {
    const response = await api.get(`/materials/${id}/download`, {
      responseType: 'blob',
    })

    let filename = defaultFilename
    const disposition = response.headers['content-disposition'] || response.headers['Content-Disposition']
    if (disposition && disposition.indexOf('filename=') !== -1) {
      const match = disposition.match(/filename="?([^";]+)"?/i)
      if (match && match[1]) {
        filename = match[1]
      }
    }

    const blob = new Blob([response.data], { type: response.headers['content-type'] })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    return response
  },
}
