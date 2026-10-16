import api from './api'

export const notificationService = {
  /**
   * Get notifications (optional filter: 'unread' or 'read')
   */
  async getNotifications(filter = null, page = 1) {
    const params = { page }
    if (filter) {
      params.filter = filter
    }
    const response = await api.get('/notifications', { params })
    return response.data.data
  },

  /**
   * Mark a single notification as read
   */
  async markAsRead(id) {
    const response = await api.patch(`/notifications/${id}/read`)
    return response.data.data
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead() {
    const response = await api.post('/notifications/mark-all-read')
    return response.data.data
  },

  /**
   * Delete a notification
   */
  async deleteNotification(id) {
    const response = await api.delete(`/notifications/${id}`)
    return response.data.data
  },
}
