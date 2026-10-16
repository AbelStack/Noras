import api from './api'

export const userService = {
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
