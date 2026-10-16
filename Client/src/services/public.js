import api from './api'

export const publicService = {
  async getStats() {
    const response = await api.get('/public/stats')
    return response.data.data
  }
}
