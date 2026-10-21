<template>
  <DashboardLayout pageTitle="Admin" :navigationItems="adminNavigationItems">
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back button & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <button 
            @click="$router.push('/admin/dashboard')"
            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
          >
            <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
          </button>
          <div class="flex items-center gap-2.5">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Notifications</h1>
            <span 
              v-if="unreadCount > 0"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200"
            >
              {{ unreadBadge }} Unread
            </span>
          </div>
          <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Real-time alerts for student registrations and administrative actions</p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-center">
          <button
            @click="markAllAsRead"
            :disabled="unreadCount === 0 || isMarkingAll"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-2xs transition-colors"
          >
            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ isMarkingAll ? 'Marking...' : 'Mark all as read' }}</span>
          </button>
          <button
            @click="loadNotifications(currentPage)"
            :disabled="isLoading"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors"
            title="Refresh notifications"
          >
            <svg :class="['w-4 h-4', isLoading ? 'animate-spin' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="changeTab(tab.value)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium transition-colors flex items-center gap-2',
            currentTab === tab.value
              ? 'bg-primary-50 text-primary-700 font-semibold shadow-2xs'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span 
            v-if="tab.value === 'unread' && unreadCount > 0"
            class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-red-500 text-white"
          >
            {{ unreadBadge }}
          </span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="tasks" :count="5" />
      </div>

      <!-- Empty State -->
      <div v-else-if="notifications.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-xs min-h-[480px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No notifications</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">
          {{ currentTab === 'unread' ? 'You have read all notifications. No unread items.' : 'There are no notifications in this view.' }}
        </p>
      </div>

      <!-- Notifications List (Large Box) -->
      <div v-else class="min-h-[480px] flex flex-col justify-between space-y-4">
        <div class="space-y-2.5 flex-1">
          <div
            v-for="item in notifications"
            :key="item.id"
            :class="[
              'p-4 sm:p-5 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3.5',
              !item.read_at 
                ? 'bg-blue-50/50 border-blue-200 shadow-xs' 
                : 'bg-white border-gray-200 hover:border-gray-300'
            ]"
          >
            <div class="flex items-start gap-3.5 flex-1">
              <!-- Icon -->
              <div 
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
                  !item.read_at ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-500'
                ]"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>

              <!-- Content -->
              <div class="space-y-1 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h4 class="text-sm font-bold text-gray-900">{{ item.title }}</h4>
                  <span 
                    v-if="!item.read_at"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded-full"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    New
                  </span>
                  <span class="text-xs text-gray-400">&bull; {{ formatRelativeTime(item.created_at) }}</span>
                </div>

                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">{{ item.message }}</p>

                <!-- Tags if data present -->
                <div v-if="item.data" class="flex flex-wrap items-center gap-1.5 pt-0.5">
                  <span v-if="item.data.student_id_code" class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    ID: {{ item.data.student_id_code }}
                  </span>
                  <span v-if="item.data.campus" class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                    {{ item.data.campus }}
                  </span>
                  <span v-if="item.data.department" class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                    {{ item.data.department }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 sm:self-center ml-13 sm:ml-0 flex-shrink-0">
              <button
                v-if="item.action_url"
                @click="handleActionClick(item)"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-primary-600 text-white hover:bg-primary-700 transition-colors shadow-2xs"
              >
                <span>Review Approval</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </button>

              <button
                v-if="!item.read_at"
                @click="markAsRead(item.id)"
                :disabled="actioningId === item.id"
                class="px-3 py-1.5 rounded-xl text-xs font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-2xs"
                title="Mark as read"
              >
                Mark Read
              </button>

              <button
                @click="deleteNotification(item.id)"
                :disabled="actioningId === item.id"
                class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                title="Delete notification"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Dynamic Pagination -->
        <DynamicPagination
          v-if="pagination && pagination.total > 0"
          :currentPage="pagination.current_page"
          :totalPages="pagination.last_page"
          :totalItems="pagination.total"
          :perPage="pagination.per_page || 10"
          :showPerPage="false"
          itemLabel="notifications"
          @update:currentPage="loadNotifications"
          class="mt-4 rounded-xl border border-gray-200 shadow-2xs"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import DynamicPagination from '../components/DynamicPagination.vue'
import { adminNavigationItems } from '../config/navigation'
import { notificationService } from '../services/notifications'
import { useNotification } from '../composables/useNotification'

const router = useRouter()
const notify = useNotification()

const notifications = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const unreadCount = ref(0)
const unreadBadge = ref('0')
const isLoading = ref(true)
const isMarkingAll = ref(false)
const actioningId = ref(null)
const currentTab = ref('all')

const tabs = [
  { label: 'All Notifications', value: 'all' },
  { label: 'Unread', value: 'unread' },
  { label: 'Read', value: 'read' },
]

const changeTab = (tab) => {
  currentTab.value = tab
  currentPage.value = 1
  loadNotifications(1)
}

const loadNotifications = async (page = 1) => {
  isLoading.value = true
  try {
    const filter = currentTab.value === 'all' ? null : currentTab.value
    const data = await notificationService.getNotifications(filter, page)
    notifications.value = data.notifications
    pagination.value = data.pagination
    currentPage.value = data.pagination?.current_page || 1
    unreadCount.value = data.unread_count
    unreadBadge.value = data.unread_badge
  } catch (error) {
    console.error('Failed to load notifications:', error)
    notify.error('Failed to load notifications')
  } finally {
    isLoading.value = false
  }
}

const markAsRead = async (id) => {
  actioningId.value = id
  try {
    const data = await notificationService.markAsRead(id)
    unreadCount.value = data.unread_count
    unreadBadge.value = data.unread_badge
    
    // Update local item
    const item = notifications.value.find(n => n.id === id)
    if (item) {
      item.read_at = data.notification?.read_at || new Date().toISOString()
    }
  } catch (error) {
    console.error('Failed to mark read:', error)
  } finally {
    actioningId.value = null
  }
}

const markAllAsRead = async () => {
  if (isMarkingAll.value) return
  isMarkingAll.value = true
  try {
    const data = await notificationService.markAllAsRead()
    unreadCount.value = 0
    unreadBadge.value = '0'
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString()
    })
    notify.success('All notifications marked as read')
  } catch (error) {
    console.error('Failed to mark all as read:', error)
    notify.error('Failed to mark all as read')
  } finally {
    isMarkingAll.value = false
  }
}

const deleteNotification = async (id) => {
  actioningId.value = id
  try {
    const data = await notificationService.deleteNotification(id)
    notifications.value = notifications.value.filter(n => n.id !== id)
    unreadCount.value = data.unread_count
    unreadBadge.value = data.unread_badge
    notify.success('Notification removed')
  } catch (error) {
    console.error('Failed to delete notification:', error)
    notify.error('Failed to delete notification')
  } finally {
    actioningId.value = null
  }
}

const handleActionClick = async (item) => {
  if (!item.read_at) {
    try {
      await notificationService.markAsRead(item.id)
      unreadCount.value = Math.max(0, unreadCount.value - 1)
      unreadBadge.value = unreadCount.value >= 10 ? '9+' : String(unreadCount.value)
    } catch (e) {
      // continue anyway
    }
  }
  const url = item.action_url || '/admin/students'
  router.push(url === '/admin/approvals' ? '/admin/students' : url)
}

const formatRelativeTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
  return date.toLocaleDateString()
}

onMounted(() => {
  loadNotifications(1)
})
</script>
