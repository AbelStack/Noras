import api from './api'

export const userService = {
  /**
   * Get current user profile and preferences
   */
  async getProfile() {
    const response = await api.get('/user/profile')
    return response.data.data.user
  },

  /**
   * Update user profile and preferences
   */
  async updateProfile(data) {
    const response = await api.put('/user/profile', data)
    return response.data.data.user
  },

  /**
   * Update account password
   */
  async updatePassword(data) {
    const response = await api.put('/user/password', data)
    return response.data
  },

  /**
   * Upload profile picture (max 2MB)
   */
  async uploadProfilePicture(file) {
    const formData = new FormData()
    formData.append('avatar', file)

    const response = await api.post('/user/profile-picture', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data.data.user
  },

  /**
   * Remove profile picture
   */
  async removeProfilePicture() {
    const response = await api.delete('/user/profile-picture')
    return response.data.data.user
  },
}
