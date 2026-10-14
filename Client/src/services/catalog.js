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

  async downloadMaterial(id) {
    // Request returns file blob, not JSON
    const response = await api.get(`/materials/${id}/download`, {
      responseType: 'blob',
    })
    return response
  },
}
