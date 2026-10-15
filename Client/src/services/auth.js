import api from './api'

export const authService = {
  async getCampuses() {
    const response = await api.get('/auth/campuses')
    return response.data.data.campuses
  },

  async getDepartments(campusId = null) {
    const params = campusId ? { university_id: campusId } : {}
    const response = await api.get('/auth/departments', { params })
    return response.data.data.departments
  },

  async register(data) {
    const response = await api.post('/auth/register', data)
    return response.data
  },

  async login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    if (response.data.status === 'success') {
      const { token, user } = response.data.data
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      return { token, user }
    }
    throw new Error(response.data.message || 'Login failed')
  },

  async logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  },

  async me() {
    const response = await api.get('/auth/me')
    if (response.data.status === 'success') {
      const user = response.data.data.user
      localStorage.setItem('user', JSON.stringify(user))
      return user
    }
    throw new Error('Failed to fetch user')
  },

  getUser() {
    const userStr = localStorage.getItem('user')
    return userStr ? JSON.parse(userStr) : null
  },

  getToken() {
    return localStorage.getItem('auth_token')
  },

  isAuthenticated() {
    return !!this.getToken()
  },

  isAdmin() {
    const user = this.getUser()
    return user?.role === 'admin'
  },

  isApproved() {
    const user = this.getUser()
    return user?.account_status === 'approved'
  },
}
