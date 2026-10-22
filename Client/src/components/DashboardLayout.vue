<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <!-- Mobile Backdrop Overlay -->
    <div 
      v-if="mobileMenuOpen" 
      @click="mobileMenuOpen = false" 
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden transition-opacity"
      aria-hidden="true"
    ></div>

    <!-- Sidebar / Mobile App Drawer -->
    <aside 
      :class="[
        'bg-white border-r border-gray-200 transition-all duration-300 flex flex-col z-50',
        'fixed inset-y-0 left-0 lg:static lg:translate-x-0',
        mobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
        'w-72 sm:w-80 lg:w-64',
        isMobileView ? 'rounded-r-3xl' : ''
      ]"
    >
      <!-- Logo & Toggle Button -->
      <div class="h-16 flex items-center border-b border-gray-200 px-4 justify-between">
        <!-- Expanded state on desktop OR on mobile: Logo on left, collapse/close button on right -->
        <template v-if="!sidebarCollapsed || isMobileView">
          <router-link to="/" class="flex items-center min-w-0" title="Noras Home" @click="mobileMenuOpen = false">
            <NorasLogo imageClass="h-8 w-auto" textClass="text-xl text-gray-900" dividerClass="h-6" />
          </router-link>
          <button 
            type="button"
            @click="handleSidebarCloseOrCollapse"
            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors flex-shrink-0 ml-1 touch-press"
            :title="isMobileView ? 'Close navigation' : 'Collapse sidebar'"
          >
            <!-- Close icon on mobile, left arrows on desktop -->
            <svg v-if="isMobileView" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>
        </template>

        <!-- Collapsed state on desktop: Centered logo and clear expand button -->
        <template v-else>
          <div class="w-full flex items-center justify-between px-0.5">
            <router-link to="/" class="flex items-center justify-center p-1" title="Noras Home">
              <img src="/Noras logo-sm.png" alt="Noras" class="h-7 w-auto object-contain" />
            </router-link>
            <button 
              type="button"
              @click="toggleSidebarCollapse(false)"
              class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-blue-50 rounded-lg transition-colors flex-shrink-0"
              title="Expand sidebar"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </template>
      </div>

      <!-- Mobile Profile Card Snippet inside Drawer -->
      <div v-if="isMobileView" class="px-3.5 py-3 mx-3 mt-3 bg-gradient-to-br from-blue-50/80 to-gray-50/60 rounded-2xl border border-blue-100/60 flex items-center gap-3">
        <img 
          v-if="user?.avatar_url" 
          :src="user.avatar_url" 
          :alt="userName" 
          class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-xs" 
        />
        <div 
          v-else 
          class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-xs shadow-xs"
        >
          {{ userInitials }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold text-gray-900 truncate">{{ userName }}</p>
          <p class="text-[11px] text-gray-500 truncate">{{ user?.department?.name || user?.email }}</p>
        </div>
      </div>

      <!-- MENU Label -->
      <div class="px-4 py-2">
        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider" v-if="!sidebarCollapsed || isMobileView">
          {{ isMobileView ? 'Quick Navigation' : 'MENU' }}
        </p>
      </div>

      <!-- Navigation Items -->
      <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in effectiveNavItems"
          :key="item.path"
          :to="item.path"
          v-slot="{ isActive }"
          custom
        >
          <a
            :href="item.path"
            @click.prevent="navigateAndCloseMenu(item.path)"
            :class="[
              'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors group relative',
              isActive 
                ? 'bg-blue-50 text-primary-500' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <component 
              :is="iconComponents[item.icon]" 
              :class="[
                'flex-shrink-0',
                sidebarCollapsed && !isMobileView ? 'w-5 h-5' : 'w-5 h-5 mr-3'
              ]"
            />
            <span v-if="!sidebarCollapsed || isMobileView" class="flex-1">{{ item.label }}</span>
            <!-- Red Badge in Sidebar for Notifications -->
            <span 
              v-if="(!sidebarCollapsed || isMobileView) && item.path === '/admin/notifications' && unreadCount > 0" 
              class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white leading-none ml-1.5"
            >
              {{ unreadCount >= 10 ? '9+' : unreadCount }}
            </span>
            <span 
              v-else-if="sidebarCollapsed && !isMobileView && item.path === '/admin/notifications' && unreadCount > 0"
              class="w-2 h-2 rounded-full bg-red-600 absolute top-2 right-2 ring-2 ring-white"
            ></span>
            <svg 
              v-if="(!sidebarCollapsed || isMobileView) && item.children" 
              :class="[
                'w-4 h-4 transition-transform',
                item.expanded ? 'transform rotate-180' : ''
              ]"
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </a>
        </router-link>
      </nav>

      <!-- Logout Button at Bottom -->
      <div class="p-3 border-t border-gray-200">
        <button
          @click="handleLogout"
          class="w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
        >
          <svg class="w-5 h-5" :class="sidebarCollapsed && !isMobileView ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span v-if="!sidebarCollapsed || isMobileView">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Navigation Bar (Mobile Native App Bar + Desktop Header) -->
      <header class="h-14 sm:h-16 bg-white/95 backdrop-blur-md border-b border-gray-200/80 sticky top-0 z-30 flex items-center justify-between px-3.5 sm:px-6">
        <div class="flex items-center space-x-2.5 sm:space-x-3">
          <button 
            type="button"
            @click="handleMenuToggle"
            class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 touch-press"
            :title="isMobileView ? 'Open navigation' : (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar')"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <!-- Mobile Brand Indicator -->
          <div class="flex items-center gap-2 lg:hidden">
            <router-link to="/" class="flex items-center gap-1.5 touch-press">
              <img src="/Noras logo-sm.png" alt="Noras" class="h-7 w-auto object-contain" />
              <span class="font-bold text-gray-900 text-base tracking-tight">Noras</span>
            </router-link>
            <span 
              :class="[
                'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                user?.role === 'admin' 
                  ? 'bg-purple-100 text-purple-700 border border-purple-200' 
                  : 'bg-blue-100 text-primary-700 border border-blue-200'
              ]"
            >
              {{ user?.role === 'admin' ? 'Admin' : 'Student' }}
            </span>
          </div>

          <!-- Desktop Page Title -->
          <h1 class="hidden lg:block text-lg sm:text-xl font-bold text-gray-900">
            {{ pageTitle === 'Admin' ? 'Noras Admin' : (pageTitle === 'Student' ? 'Noras Student' : pageTitle) }}
          </h1>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Notification Bell (Desktop & Top Bar) -->
          <div class="relative">
            <button 
              type="button"
              @click.stop="toggleNotifications"
              class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors relative focus:outline-none touch-press"
              title="Notifications"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <!-- Red Badge with 9+ rule -->
              <span 
                v-if="unreadCount > 0" 
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white shadow-xs leading-none"
              >
                {{ unreadCount >= 10 ? '9+' : unreadCount }}
              </span>
            </button>
            
            <!-- Desktop Notification Dropdown (shown strictly on desktop) -->
            <div 
              v-if="showNotifications && !isMobileView"
              @click.stop
              v-click-outside="() => showNotifications = false"
              class="absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-xl border border-gray-200 z-50 overflow-hidden divide-y divide-gray-100"
            >
              <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-gray-900 text-sm">Notifications</h3>
                  <span 
                    v-if="unreadCount > 0" 
                    class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-700"
                  >
                    {{ unreadCount >= 10 ? '9+' : unreadCount }} new
                  </span>
                </div>
                <button
                  v-if="unreadCount > 0"
                  type="button"
                  @click="markAllNotificationsAsRead"
                  class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors"
                >
                  Mark all read
                </button>
              </div>

              <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                <div v-if="notifications.length === 0" class="p-8 text-center text-gray-500 text-sm">
                  <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                  </div>
                  <p class="font-bold text-gray-800">No notifications yet</p>
                  <p class="text-xs text-gray-400 mt-1">We'll alert you when there are new updates</p>
                </div>
                <div 
                  v-for="notif in notifications.slice(0, 8)" 
                  :key="notif.id" 
                  @click="handleNotificationItemClick(notif)"
                  :class="[
                    'p-3.5 hover:bg-gray-50 cursor-pointer transition-colors flex items-start gap-3',
                    !notif.read_at ? 'bg-blue-50/40' : ''
                  ]"
                >
                  <div 
                    :class="[
                      'w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5',
                      !notif.read_at ? 'bg-blue-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-500'
                    ]"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                      <p class="text-xs font-bold text-gray-900 truncate">{{ notif.title }}</p>
                      <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ formatRelativeTime(notif.created_at) }}</span>
                    </div>
                    <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
                  </div>
                  <span v-if="!notif.read_at" class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 mt-2"></span>
                </div>
              </div>

              <!-- Footer view all link -->
              <div class="p-2.5 bg-gray-50 border-t border-gray-100 text-center">
                <router-link
                  :to="user?.role === 'admin' ? '/admin/notifications' : '/dashboard'"
                  @click="showNotifications = false"
                  class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors inline-flex items-center gap-1"
                >
                  <span>{{ user?.role === 'admin' ? 'View all notifications' : 'Back to Dashboard' }}</span>
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
              </div>
            </div>
          </div>

          <!-- User Profile Dropdown -->
          <div class="relative">
            <button
              @click.stop="toggleUserMenu"
              class="flex items-center space-x-2.5 hover:bg-gray-100/80 rounded-xl px-2.5 py-1.5 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20"
            >
              <img 
                v-if="user?.avatar_url" 
                :src="user.avatar_url" 
                :alt="userName" 
                class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-500/30" 
              />
              <div 
                v-else 
                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-xs shadow-xs"
              >
                {{ userInitials }}
              </div>
              <div class="hidden md:block text-left">
                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ userName }}</p>
              </div>
              <svg 
                :class="['w-4 h-4 text-gray-500 transition-transform duration-200', showUserMenu ? 'transform rotate-180' : '']" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- User Dropdown Menu -->
            <div 
              v-if="showUserMenu"
              @click.stop
              v-click-outside="() => showUserMenu = false"
              class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-200 z-50 overflow-hidden divide-y divide-gray-100"
            >
              <!-- User Info Card Header -->
              <div class="p-4 bg-gradient-to-br from-blue-50/60 via-gray-50/50 to-white">
                <div class="flex items-center gap-3 mb-2">
                  <div class="relative">
                    <img 
                      v-if="user?.avatar_url" 
                      :src="user.avatar_url" 
                      :alt="userName" 
                      class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-xs" 
                    />
                    <div 
                      v-else 
                      class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-sm shadow-xs"
                    >
                      {{ userInitials }}
                    </div>
                    <button
                      @click="openAvatarModal"
                      class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full shadow-xs border border-gray-200 flex items-center justify-center text-gray-600 hover:text-primary-600 hover:border-primary-300 transition-colors"
                      title="Change photo"
                    >
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 text-sm truncate">{{ userName }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                    <div class="mt-1">
                      <span 
                        :class="[
                          'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                          user?.role === 'admin' 
                            ? 'bg-purple-100 text-purple-700 border border-purple-200' 
                            : 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                        ]"
                      >
                        {{ user?.role === 'admin' ? 'Administrator' : 'Student' }}
                      </span>
                    </div>
                  </div>
                </div>

                <div v-if="user?.department || user?.university" class="text-[11px] text-gray-500 font-medium pt-1 border-t border-gray-100/80">
                  <span v-if="user?.department">{{ user.department.name }}</span>
                  <span v-if="user?.department && user?.university"> &bull; </span>
                  <span v-if="user?.university">{{ user.university.name }}</span>
                </div>
              </div>

              <!-- Quick Links for Admin -->
              <div v-if="user?.role === 'admin'" class="py-1.5">
                <button
                  @click="openAvatarModal"
                  class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>Upload Profile Photo</span>
                </button>
                <router-link
                  to="/admin/notifications"
                  class="flex items-center justify-between px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>Notifications</span>
                  </div>
                  <span v-if="unreadCount > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-red-500 text-white">
                    {{ unreadCount >= 10 ? '9+' : unreadCount }}
                  </span>
                </router-link>
                <router-link
                  to="/admin/students"
                  class="flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Student Approvals</span>
                </router-link>
                <router-link
                  to="/admin/settings"
                  class="flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>System Settings</span>
                </router-link>
              </div>

              <!-- Quick Links for Student -->
              <div v-else class="py-1.5">
                <button
                  @click="openAvatarModal"
                  class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>Upload Profile Photo</span>
                </button>
                <button
                  @click="openPasswordModal"
                  class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <span>Change Password</span>
                </button>
                <router-link
                  to="/dashboard"
                  class="flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                  <span>Student Dashboard</span>
                </router-link>
                <router-link
                  to="/departments"
                  class="flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                  <span>Browse Courses</span>
                </router-link>
                <router-link
                  to="/progress"
                  class="flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  <span>Learning Progress</span>
                </router-link>
              </div>

              <!-- Logout Button -->
              <div class="py-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-2.5 px-4 py-2 text-xs sm:text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Sign Out</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content (padded on mobile so content never clips behind bottom bar) -->
      <main class="flex-1 overflow-y-auto bg-gray-50 pb-20 lg:pb-0">
        <slot></slot>
      </main>

      <!-- ============================================== -->
      <!-- MOBILE APP BOTTOM NAVIGATION BAR (iOS / Android) -->
      <!-- ============================================== -->
      <nav 
        class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/92 backdrop-blur-xl border-t border-gray-200/80 shadow-[0_-4px_24px_rgba(0,0,0,0.06)] pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-1.5 px-1 select-none"
        aria-label="Mobile Navigation Bar"
      >
        <!-- STUDENT MOBILE TABS (5 Buttons) -->
        <div v-if="user?.role !== 'admin'" class="grid grid-cols-5 items-center">
          <!-- 1. Dashboard -->
          <button
            type="button"
            @click="handleMobileNav('/dashboard')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('dashboard') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('dashboard') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('dashboard') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('dashboard') ? '2.2' : '1.8'" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('dashboard') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Home
            </span>
          </button>

          <!-- 2. Courses -->
          <button
            type="button"
            @click="handleMobileNav('/departments')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('courses') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('courses') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('courses') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('courses') ? '2.2' : '1.8'" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('courses') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Courses
            </span>
          </button>

          <!-- 3. AI Notes -->
          <button
            type="button"
            @click="handleMobileNav('/notes')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('notes') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('notes') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('notes') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('notes') ? '2.2' : '1.8'" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('notes') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              AI Notes
            </span>
          </button>

          <!-- 4. AI Quizzes -->
          <button
            type="button"
            @click="handleMobileNav('/quizzes')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('quizzes') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('quizzes') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('quizzes') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('quizzes') ? '2.2' : '1.8'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('quizzes') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Quizzes
            </span>
          </button>

          <!-- 5. Notifications -->
          <button
            type="button"
            @click.stop="handleMobileNav(null, 'notifications')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press relative',
              showNotifications ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all relative',
                showNotifications ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="showNotifications ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="showNotifications ? '2.2' : '1.8'" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <!-- Real-time Unread Badge -->
              <span 
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-red-600 text-white text-[9px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white shadow-xs leading-none"
              >
                {{ unreadCount >= 10 ? '9+' : unreadCount }}
              </span>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', showNotifications ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Alerts
            </span>
          </button>
        </div>

        <!-- ADMIN MOBILE TABS (5 Buttons) -->
        <div v-else class="grid grid-cols-5 items-center">
          <!-- 1. Admin Dashboard -->
          <button
            type="button"
            @click="handleMobileNav('/admin/dashboard')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('dashboard') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('dashboard') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('dashboard') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('dashboard') ? '2.2' : '1.8'" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('dashboard') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Admin
            </span>
          </button>

          <!-- 2. Courses -->
          <button
            type="button"
            @click="handleMobileNav('/admin/courses')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('courses') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('courses') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('courses') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('courses') ? '2.2' : '1.8'" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('courses') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Courses
            </span>
          </button>

          <!-- 3. Course Materials -->
          <button
            type="button"
            @click="handleMobileNav('/admin/materials')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('materials') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('materials') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('materials') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('materials') ? '2.2' : '1.8'" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('materials') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Materials
            </span>
          </button>

          <!-- 4. Student Approvals -->
          <button
            type="button"
            @click="handleMobileNav('/admin/students')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press',
              isTabActive('approvals') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all',
                isTabActive('approvals') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('approvals') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('approvals') ? '2.2' : '1.8'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('approvals') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Approvals
            </span>
          </button>

          <!-- 5. Notifications -->
          <button
            type="button"
            @click.stop="handleMobileNav('/admin/notifications')"
            :class="[
              'flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all duration-150 touch-press relative',
              isTabActive('notifications') ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600'
            ]"
          >
            <div 
              :class="[
                'w-9 h-7 rounded-full flex items-center justify-center transition-all relative',
                isTabActive('notifications') ? 'bg-primary-50 text-primary-600 font-bold scale-105 shadow-2xs ring-1 ring-primary-100' : ''
              ]"
            >
              <svg class="w-5 h-5" :fill="isTabActive('notifications') ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isTabActive('notifications') ? '2.2' : '1.8'" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <!-- Real-time Unread Badge -->
              <span 
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-red-600 text-white text-[9px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white shadow-xs leading-none"
              >
                {{ unreadCount >= 10 ? '9+' : unreadCount }}
              </span>
            </div>
            <span :class="['text-[10px] mt-0.5 tracking-tight leading-none', isTabActive('notifications') ? 'font-bold text-primary-600' : 'font-medium text-gray-500']">
              Alerts
            </span>
          </button>
        </div>
      </nav>
    </div>

    <!-- Profile Picture Modal -->
    <div 
      v-if="showAvatarModal" 
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
      @click.self="closeAvatarModal"
      @keydown.esc="closeAvatarModal"
    >
      <div 
        class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="font-bold text-gray-900 text-base">Update Profile Photo</h3>
          </div>
          <button 
            @click="closeAvatarModal" 
            class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-5">
          <!-- Avatar Preview Center -->
          <div class="flex flex-col items-center">
            <div class="relative group">
              <img 
                v-if="avatarPreview" 
                :src="avatarPreview" 
                :alt="userName" 
                class="w-28 h-28 rounded-full object-cover ring-4 ring-primary-100 shadow-md"
              />
              <div 
                v-else 
                class="w-28 h-28 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-3xl shadow-md ring-4 ring-primary-100"
              >
                {{ userInitials }}
              </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 font-medium">Preview</p>
          </div>

          <!-- Hidden File Input -->
          <input 
            ref="fileInputRef"
            type="file" 
            accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" 
            class="hidden" 
            @change="handleFileChange"
          />

          <!-- Upload Dropzone / Action Button -->
          <div 
            @click="triggerFileInput"
            class="border-2 border-dashed border-gray-200 hover:border-primary-400 rounded-xl p-4 text-center cursor-pointer transition-colors bg-gray-50/50 hover:bg-primary-50/20"
          >
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">
              <span class="text-primary-600 hover:underline">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500 mt-1">
              JPG, PNG, WEBP, or GIF (Strict max size: 2MB)
            </p>
          </div>

          <!-- Error notice if any -->
          <div v-if="avatarError" class="p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2.5 text-xs text-red-700">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ avatarError }}</span>
          </div>

          <!-- Remove photo option if user currently has one -->
          <div v-if="user?.avatar_url && !selectedFile" class="pt-1 text-center">
            <button 
              type="button"
              @click="handleRemoveAvatar"
              :disabled="isSavingAvatar"
              class="text-xs font-semibold text-red-600 hover:text-red-700 transition-colors inline-flex items-center gap-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>Remove current photo</span>
            </button>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
          <button 
            type="button" 
            @click="closeAvatarModal" 
            :disabled="isSavingAvatar"
            class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-50"
          >
            Cancel
          </button>
          <button 
            type="button" 
            @click="handleUploadAvatar" 
            :disabled="!selectedFile || isSavingAvatar"
            class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors disabled:opacity-50 inline-flex items-center gap-2 shadow-xs"
          >
            <svg v-if="isSavingAvatar" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isSavingAvatar ? 'Uploading...' : 'Save Photo' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div 
      v-if="showPasswordModal" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
      @click.self="closePasswordModal"
      @keydown.esc="closePasswordModal"
    >
      <div 
        class="bg-white rounded-3xl shadow-2xl max-w-md w-full border border-gray-100 overflow-hidden transform transition-all"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-blue-700 via-primary-600 to-indigo-700 text-white flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md ring-2 ring-white/30 flex items-center justify-center text-white">
              <svg class="w-5 h-5 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-tight">Change Password</h3>
              <p class="text-xs text-blue-100">Ensure your account stays secure</p>
            </div>
          </div>
          <button 
            type="button" 
            @click="closePasswordModal" 
            class="text-white/80 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="handleChangePassword" class="p-6 space-y-4">
          <div v-if="pwdError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 font-medium">
            {{ pwdError }}
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Current Password</label>
            <div class="relative">
              <input 
                v-model="pwdForm.currentPassword"
                :type="showCurrentPwd ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
              />
              <button
                type="button"
                tabindex="-1"
                @click="showCurrentPwd = !showCurrentPwd"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                aria-label="Toggle current password visibility"
              >
                <Eye v-if="!showCurrentPwd" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">New Password</label>
            <div class="relative">
              <input 
                v-model="pwdForm.newPassword"
                :type="showNewPwd ? 'text' : 'password'"
                required
                placeholder="Minimum 8 characters"
                class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
              />
              <button
                type="button"
                tabindex="-1"
                @click="showNewPwd = !showNewPwd"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                aria-label="Toggle new password visibility"
              >
                <Eye v-if="!showNewPwd" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
            <PasswordRequirements :password="pwdForm.newPassword" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New Password</label>
            <div class="relative">
              <input 
                v-model="pwdForm.confirmPassword"
                :type="showConfirmPwd ? 'text' : 'password'"
                required
                placeholder="Repeat new password"
                class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
              />
              <button
                type="button"
                tabindex="-1"
                @click="showConfirmPwd = !showConfirmPwd"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                aria-label="Toggle confirm password visibility"
              >
                <Eye v-if="!showConfirmPwd" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
            <p v-if="pwdForm.confirmPassword && pwdForm.newPassword !== pwdForm.confirmPassword" class="mt-1 text-xs text-rose-600 font-medium">
              Passwords do not match
            </p>
            <p v-else-if="pwdForm.confirmPassword && pwdForm.newPassword === pwdForm.confirmPassword" class="mt-1 text-xs text-emerald-600 font-medium">
              Passwords match
            </p>
          </div>

          <!-- Footer Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button 
              type="button" 
              @click="closePasswordModal" 
              :disabled="isSavingPassword"
              class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-50 cursor-pointer"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="isSavingPassword"
              class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors disabled:opacity-50 inline-flex items-center gap-2 shadow-xs cursor-pointer"
            >
              <svg v-if="isSavingPassword" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isSavingPassword ? 'Updating...' : 'Update Password' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MOBILE NOTIFICATIONS BOTTOM SHEET (TELEPORTED) -->
    <!-- ============================================== -->
    <Teleport to="body">
      <div 
        v-if="isMobileView && showNotifications" 
        class="fixed inset-0 z-[100] flex flex-col justify-end"
        @keydown.esc="showNotifications = false"
      >
        <!-- Dark Blur Backdrop -->
        <div 
          class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity animate-fade-in"
          @click="showNotifications = false"
          aria-hidden="true"
        ></div>

        <!-- Sliding Bottom Sheet Panel -->
        <div 
          class="relative z-10 bg-white rounded-t-3xl shadow-2xl border-t border-gray-200/90 max-h-[85vh] flex flex-col pb-[max(env(safe-area-inset-bottom),1rem)] animate-sheet-up select-none overflow-hidden"
          @click.stop
        >
          <!-- Drag Handle -->
          <div class="pt-3 pb-1 flex justify-center cursor-pointer" @click="showNotifications = false">
            <div class="w-12 h-1.5 bg-gray-300 hover:bg-gray-400 rounded-full transition-colors"></div>
          </div>

          <!-- Bottom Sheet Header -->
          <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 text-base leading-tight">Notifications</h3>
                <p class="text-[11px] text-gray-500 font-medium">
                  {{ unreadCount > 0 ? `${unreadCount} unread update${unreadCount > 1 ? 's' : ''}` : 'You are all caught up' }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="unreadCount > 0"
                type="button"
                @click="markAllNotificationsAsRead"
                class="text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 px-2.5 py-1.5 rounded-xl transition-colors cursor-pointer"
              >
                Mark all read
              </button>
              <button 
                type="button"
                @click="showNotifications = false"
                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors cursor-pointer"
                aria-label="Close notifications"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Notification Items List (Scrollable) -->
          <div class="flex-1 overflow-y-auto divide-y divide-gray-100 max-h-[60vh] overscroll-contain px-1">
            <div v-if="notifications.length === 0" class="p-10 text-center text-gray-500 text-sm">
              <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <p class="font-bold text-gray-800 text-base">No notifications yet</p>
              <p class="text-xs text-gray-400 mt-1">We'll alert you here when new materials or tasks arrive</p>
            </div>
            <div 
              v-for="notif in notifications" 
              :key="notif.id" 
              @click="handleNotificationItemClick(notif)"
              :class="[
                'p-4 hover:bg-gray-50 active:bg-gray-100 cursor-pointer transition-colors flex items-start gap-3.5',
                !notif.read_at ? 'bg-blue-50/50' : ''
              ]"
            >
              <div 
                :class="[
                  'w-9 h-9 rounded-2xl flex items-center justify-center flex-shrink-0 mt-0.5',
                  !notif.read_at ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-500'
                ]"
              >
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1">
                  <p :class="['text-xs truncate', !notif.read_at ? 'font-bold text-gray-900' : 'font-semibold text-gray-700']">
                    {{ notif.title }}
                  </p>
                  <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ formatRelativeTime(notif.created_at) }}</span>
                </div>
                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
              </div>
              <span v-if="!notif.read_at" class="w-2.5 h-2.5 rounded-full bg-blue-600 ring-2 ring-blue-100 flex-shrink-0 mt-2"></span>
            </div>
          </div>

          <!-- Bottom Sheet Footer -->
          <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="text-gray-400 text-[11px] font-medium">Notifications sync in real time</span>
            <router-link
              v-if="user?.role === 'admin'"
              to="/admin/notifications"
              @click="showNotifications = false"
              class="font-semibold text-primary-600 hover:text-primary-700 transition-colors inline-flex items-center gap-1"
            >
              <span>Full admin panel</span>
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
            <button
              v-else
              type="button"
              @click="showNotifications = false"
              class="font-semibold text-gray-600 hover:text-gray-900 transition-colors px-3 py-1"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Centralized Toast & Confirmation Modals -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Eye, EyeOff } from 'lucide-vue-next'
import { authService } from '../services/auth'
import { notificationService } from '../services/notifications'
import { userService } from '../services/userService'
import { useNotification } from '../composables/useNotification'
import { adminNavigationItems, studentNavigationItems } from '../config/navigation'
import NorasLogo from './NorasLogo.vue'
import NotificationContainer from './NotificationContainer.vue'
import PasswordRequirements from './PasswordRequirements.vue'

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  },
  navigationItems: {
    type: Array,
    default: () => []
  }
})

// Current user state
const toast = useNotification()
const currentUser = ref(authService.getUser())
const user = computed(() => currentUser.value)
const userName = computed(() => user.value?.name || 'User')
const userInitials = computed(() => {
  const name = userName.value
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

// Notifications state
const showNotifications = ref(false)
const showUserMenu = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const unreadBadge = ref('0')
const notificationCount = computed(() => unreadCount.value)
let notifTimer = null

// Responsive viewport detection
const isMobileView = ref(typeof window !== 'undefined' ? window.innerWidth < 1024 : false)

const checkMobile = () => {
  if (typeof window !== 'undefined') {
    isMobileView.value = window.innerWidth < 1024
  }
}

// Mobile menu and sidebar collapse state
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(
  typeof localStorage !== 'undefined' 
    ? localStorage.getItem('noras_sidebar_collapsed') === 'true' 
    : false
)

const toggleSidebarCollapse = (val) => {
  sidebarCollapsed.value = val !== undefined ? val : !sidebarCollapsed.value
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('noras_sidebar_collapsed', sidebarCollapsed.value)
  }
}

const handleSidebarCloseOrCollapse = () => {
  if (isMobileView.value) {
    mobileMenuOpen.value = false
  } else {
    toggleSidebarCollapse()
  }
}

const handleMenuToggle = () => {
  if (isMobileView.value) {
    mobileMenuOpen.value = !mobileMenuOpen.value
  } else {
    toggleSidebarCollapse()
  }
}

const navigateAndCloseMenu = (path) => {
  mobileMenuOpen.value = false
  router.push(path)
}

// Automatically close mobile menu & notifications sheet whenever route changes
const route = useRoute()
const router = useRouter()

watch(() => route.path, () => {
  mobileMenuOpen.value = false
  showNotifications.value = false
})

// Body scroll lock when mobile bottom sheet is active
watch(showNotifications, (isOpen) => {
  if (typeof document !== 'undefined') {
    if (isOpen && isMobileView.value) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }
})

// Mobile bottom navigation active state detection
const isTabActive = (tabKey) => {
  const path = route.path
  if (tabKey === 'dashboard') {
    return path === '/dashboard' || path === '/admin/dashboard' || path === '/admin'
  }
  if (tabKey === 'courses') {
    return path.startsWith('/departments') || path.startsWith('/offerings') || path === '/admin/courses'
  }
  if (tabKey === 'materials') {
    return path === '/admin/materials'
  }
  if (tabKey === 'notes') {
    return path.startsWith('/notes') || path === '/admin/notes'
  }
  if (tabKey === 'quizzes') {
    return path.startsWith('/quizzes') || path === '/admin/quizzes'
  }
  if (tabKey === 'approvals') {
    return path === '/admin/students'
  }
  if (tabKey === 'notifications') {
    return path === '/admin/notifications' || showNotifications.value
  }
  return false
}

// Mobile bottom navigation tap handler
const handleMobileNav = (targetPath, isAction = null) => {
  if (isAction === 'notifications') {
    toggleNotifications()
    return
  }
  showNotifications.value = false
  mobileMenuOpen.value = false
  if (targetPath && route.path !== targetPath) {
    router.push(targetPath)
  }
}

// Create icon components using h() render function
const DashboardIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
])

const ApprovalIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
])

const SalesIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' })
])

const ProductsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })
])

const InventoryIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' })
])

const ReportsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
])

const SettingsIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
])

const BookIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
])

const NotesIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
])

const QuizIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
])

const UsersIcon = () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
])

// Compute navigation items: ensure admin has Notifications with real-time badge
const effectiveNavItems = computed(() => {
  const baseItems = props.navigationItems && props.navigationItems.length > 0
    ? props.navigationItems
    : (user.value?.role === 'admin' ? adminNavigationItems : studentNavigationItems)

  if (user.value?.role === 'admin') {
    const hasNotifications = baseItems.some(i => i.path === '/admin/notifications')
    if (!hasNotifications) {
      const copy = [...baseItems]
      const studentIdx = copy.findIndex(i => i.path === '/admin/students' || i.path === '/admin/approvals')
      const notifItem = { path: '/admin/notifications', label: 'Notifications', icon: 'ReportsIcon' }
      if (studentIdx !== -1) {
        copy.splice(studentIdx + 1, 0, notifItem)
      } else {
        copy.push(notifItem)
      }
      return copy
    }
  }
  return baseItems
})

// Profile Picture Modal State
const showAvatarModal = ref(false)
const selectedFile = ref(null)
const avatarPreview = ref(null)
const avatarError = ref('')
const isSavingAvatar = ref(false)
const fileInputRef = ref(null)

const formatRelativeTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)
  if (diffSec < 60) return 'Just now'
  const diffMin = Math.floor(diffSec / 60)
  if (diffMin < 60) return `${diffMin}m ago`
  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours}h ago`
  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`
  return date.toLocaleDateString()
}

const fetchNotifications = async () => {
  if (!authService.isAuthenticated()) return
  try {
    const data = await notificationService.getNotifications(null, 1)
    notifications.value = data.notifications || []
    unreadCount.value = Number(data.unread_count || 0)
    unreadBadge.value = data.unread_badge || (unreadCount.value >= 10 ? '9+' : String(unreadCount.value))
  } catch (err) {
    // Silently ignore background polling errors
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    showUserMenu.value = false
    fetchNotifications()
  }
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
  if (showUserMenu.value) {
    showNotifications.value = false
  }
}

const markAllNotificationsAsRead = async () => {
  try {
    await notificationService.markAllAsRead()
    unreadCount.value = 0
    unreadBadge.value = '0'
    notifications.value = notifications.value.map(n => ({
      ...n,
      read_at: n.read_at || new Date().toISOString()
    }))
  } catch (err) {
    console.error('Failed to mark all notifications as read', err)
  }
}

const handleNotificationItemClick = async (notif) => {
  if (!notif.read_at) {
    try {
      await notificationService.markAsRead(notif.id)
      notif.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
      unreadBadge.value = unreadCount.value >= 10 ? '9+' : String(unreadCount.value)
    } catch (err) {
      console.error('Failed to mark notification as read', err)
    }
  }
  showNotifications.value = false
  if (notif.action_url) {
    router.push(notif.action_url)
  } else if (user.value?.role === 'admin') {
    router.push('/admin/notifications')
  }
}

// Profile picture handlers
const openAvatarModal = () => {
  avatarError.value = ''
  selectedFile.value = null
  avatarPreview.value = currentUser.value?.avatar_url || null
  showAvatarModal.value = true
  showUserMenu.value = false
}

const closeAvatarModal = () => {
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
  showAvatarModal.value = false
  selectedFile.value = null
  avatarPreview.value = null
  avatarError.value = ''
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const handleFileChange = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Max 2MB (2 * 1024 * 1024 bytes)
  const MAX_SIZE = 2 * 1024 * 1024
  if (file.size > MAX_SIZE) {
    const sizeInMb = (file.size / (1024 * 1024)).toFixed(2)
    avatarError.value = `File size is ${sizeInMb}MB, which exceeds the 2MB limit. Please choose a smaller photo.`
    selectedFile.value = null
    return
  }

  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif']
  if (!validTypes.includes(file.type)) {
    avatarError.value = 'Invalid file type. Supported formats are JPG, PNG, WEBP, or GIF.'
    selectedFile.value = null
    return
  }

  avatarError.value = ''
  selectedFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
}

const handleUploadAvatar = async () => {
  if (!selectedFile.value) return
  isSavingAvatar.value = true
  avatarError.value = ''
  try {
    const updatedUser = await userService.uploadProfilePicture(selectedFile.value)
    currentUser.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
    window.dispatchEvent(new Event('user-profile-updated'))
    toast.success('Profile picture updated successfully!')
    closeAvatarModal()
  } catch (err) {
    avatarError.value = err.response?.data?.message || 'Failed to upload photo. Please ensure it is under 2MB.'
  } finally {
    isSavingAvatar.value = false
  }
}

const handleRemoveAvatar = async () => {
  const confirmed = await toast.confirm({
    title: 'Remove Profile Picture',
    message: 'Are you sure you want to remove your profile picture?',
    confirmText: 'Remove Photo',
    type: 'danger'
  })
  if (!confirmed) return

  isSavingAvatar.value = true
  avatarError.value = ''
  try {
    const updatedUser = await userService.removeProfilePicture()
    currentUser.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
    window.dispatchEvent(new Event('user-profile-updated'))
    toast.success('Profile picture removed.')
    closeAvatarModal()
  } catch (err) {
    avatarError.value = err.response?.data?.message || 'Failed to remove photo.'
  } finally {
    isSavingAvatar.value = false
  }
}

// Password Modal State & Handlers
const showPasswordModal = ref(false)
const isSavingPassword = ref(false)
const pwdError = ref('')
const showCurrentPwd = ref(false)
const showNewPwd = ref(false)
const showConfirmPwd = ref(false)
const pwdForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const openPasswordModal = () => {
  showUserMenu.value = false
  pwdError.value = ''
  showCurrentPwd.value = false
  showNewPwd.value = false
  showConfirmPwd.value = false
  pwdForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
  showPasswordModal.value = true
}

const closePasswordModal = () => {
  showPasswordModal.value = false
  pwdError.value = ''
  showCurrentPwd.value = false
  showNewPwd.value = false
  showConfirmPwd.value = false
  pwdForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
}

const handleChangePassword = async () => {
  pwdError.value = ''

  if (!pwdForm.value.currentPassword || !pwdForm.value.newPassword || !pwdForm.value.confirmPassword) {
    pwdError.value = 'Please complete all password fields.'
    return
  }

  if (pwdForm.value.newPassword.length < 8) {
    pwdError.value = 'New password must be at least 8 characters long.'
    return
  }

  if (!/[a-zA-Z]/.test(pwdForm.value.newPassword)) {
    pwdError.value = 'New password must contain at least one letter.'
    return
  }

  if (!/[0-9]/.test(pwdForm.value.newPassword)) {
    pwdError.value = 'New password must contain at least one number.'
    return
  }

  if (!/[^a-zA-Z0-9]/.test(pwdForm.value.newPassword)) {
    pwdError.value = 'New password must contain at least one symbol (e.g. !@#$%^&*).'
    return
  }

  if (pwdForm.value.newPassword !== pwdForm.value.confirmPassword) {
    pwdError.value = 'New password and confirmation do not match.'
    return
  }

  isSavingPassword.value = true
  try {
    await userService.updatePassword({
      current_password: pwdForm.value.currentPassword,
      new_password: pwdForm.value.newPassword,
      new_password_confirmation: pwdForm.value.confirmPassword,
    })

    toast.success('Password updated successfully!')
    window.dispatchEvent(new Event('user-profile-updated'))
    closePasswordModal()
  } catch (err) {
    pwdError.value = err.response?.data?.message || err.response?.data?.errors?.new_password?.[0] || 'Failed to update password.'
  } finally {
    isSavingPassword.value = false
  }
}

const handleProfileUpdated = () => {
  currentUser.value = authService.getUser()
}

onMounted(async () => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('user-profile-updated', handleProfileUpdated)

  if (authService.isAuthenticated()) {
    try {
      const refreshedUser = await authService.me()
      currentUser.value = refreshedUser
    } catch {
      // Retain local user if network request fails
    }
    await fetchNotifications()
    notifTimer = setInterval(fetchNotifications, 20000)
  }
})

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = ''
  }
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('user-profile-updated', handleProfileUpdated)
  if (notifTimer) {
    clearInterval(notifTimer)
  }
})

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (el === event.target || el.contains(event.target)) {
        return
      }
      binding.value(event)
    }
    setTimeout(() => {
      document.addEventListener('click', el.clickOutsideEvent)
    }, 0)
  },
  unmounted(el) {
    if (el.clickOutsideEvent) {
      document.removeEventListener('click', el.clickOutsideEvent)
    }
  }
}

const handleLogout = async () => {
  try {
    await authService.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
    router.push('/login')
  }
}

const iconComponents = {
  DashboardIcon,
  ApprovalIcon,
  SalesIcon,
  ProductsIcon,
  InventoryIcon,
  ReportsIcon,
  SettingsIcon,
  BookIcon,
  NotesIcon,
  QuizIcon,
  UsersIcon,
}
</script>
