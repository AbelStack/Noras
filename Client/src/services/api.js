import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor to handle errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    } else if (error.response?.status === 403) {
      const data = error.response?.data
      if (data?.account_status === 'suspended' || data?.message?.toLowerCase().includes('suspended')) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        if (window.location.pathname !== '/suspended' && window.location.pathname !== '/login' && window.location.pathname !== '/register') {
          window.location.href = '/suspended'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
