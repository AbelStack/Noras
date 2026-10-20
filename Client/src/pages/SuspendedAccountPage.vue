<template>
  <div class="min-h-screen bg-gradient-to-br from-beige-50 via-white to-red-50/30 flex flex-col justify-between p-4 sm:p-6 lg:p-8">
    <!-- Top Header -->
    <header class="max-w-4xl w-full mx-auto flex items-center justify-between">
      <router-link to="/" class="inline-flex items-center gap-2">
        <img 
          src="/Noras logo-sm.png" 
          alt="Noras"
          class="h-9 w-auto object-contain"
          loading="eager"
        />
        <div class="h-6 w-px bg-gray-300"></div>
        <span class="text-xl font-bold text-gray-900 tracking-tight">Noras</span>
      </router-link>

      <button
        @click="handleLogout"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-white transition-colors cursor-pointer shadow-2xs"
      >
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Back to Login</span>
      </button>
    </header>

    <!-- Main Suspension Notice Card -->
    <main class="max-w-xl w-full mx-auto my-8">
      <div class="bg-white rounded-3xl shadow-xl border border-red-100 p-6 sm:p-10 text-center relative overflow-hidden">
        <!-- Top accent banner -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-red-500 via-amber-500 to-red-500"></div>

        <!-- Alert Icon -->
        <div class="mx-auto w-20 h-20 rounded-3xl bg-red-50 border-8 border-red-100/60 flex items-center justify-center mb-6 shadow-sm">
          <svg class="w-10 h-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
        </div>

        <!-- Heading -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider mb-3">
          <span>Status: Suspended</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
          Your Account is Suspended
        </h1>
        <p class="text-sm sm:text-base text-gray-600 max-w-md mx-auto leading-relaxed mb-6">
          Access to your Noras student portal has been temporarily suspended by the university administration.
        </p>

        <!-- User Information snippet if available -->
        <div v-if="accountEmail || accountName" class="mb-6 p-4 rounded-2xl bg-gray-50 border border-gray-200 text-left text-xs space-y-1">
          <div v-if="accountName" class="flex justify-between">
            <span class="text-gray-500">Account Name:</span>
            <span class="font-semibold text-gray-900">{{ accountName }}</span>
          </div>
          <div v-if="accountEmail" class="flex justify-between">
            <span class="text-gray-500">Email Address:</span>
            <span class="font-semibold text-gray-900">{{ accountEmail }}</span>
          </div>
        </div>

        <!-- Admin Contact Box -->
        <div class="bg-gradient-to-br from-amber-50/70 to-red-50/40 rounded-2xl p-5 border border-amber-200/70 text-left mb-6">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">How to resolve this:</h4>
              <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                Please contact the administrator or your campus registrar office to clarify the reason for suspension and request account reactivation.
              </p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
          <!-- Direct Email Admin Button -->
          <a
            :href="adminMailtoLink"
            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm transition-all shadow-md hover:shadow-lg cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Email Admin (support@noras.edu.et)</span>
          </a>

          <!-- Contact Support Page Button -->
          <router-link
            to="/legal/contact"
            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 font-semibold text-sm transition-all cursor-pointer"
          >
            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>Contact Support via Help Form</span>
          </router-link>
        </div>

        <!-- Footer Help -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-center gap-4 text-xs text-gray-400">
          <span>Wollo University Portal</span>
          <span>&bull;</span>
          <button @click="handleLogout" class="hover:text-primary-600 underline font-medium cursor-pointer">
            Sign in with another account
          </button>
        </div>
      </div>
    </main>

    <!-- Page Footer -->
    <footer class="max-w-4xl w-full mx-auto text-center text-xs text-gray-400">
      <p>&copy; 2026 Noras. Developed by Abel T. Wollo University.</p>
    </footer>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/auth'

const router = useRouter()
const route = useRoute()

const accountEmail = ref('')
const accountName = ref('')

onMounted(() => {
  // Read from query or stored user
  if (route.query.email) {
    accountEmail.value = String(route.query.email)
  }
  const user = authService.getUser()
  if (user) {
    accountEmail.value = accountEmail.value || user.email || ''
    accountName.value = user.name || ''
  }
})

const adminMailtoLink = computed(() => {
  const email = accountEmail.value || 'my student account'
  const subject = encodeURIComponent(`Account Suspension Inquiry - ${email}`)
  const body = encodeURIComponent(
    `Dear Noras Administration,\n\nMy student account (${email}) has been suspended. Please review my account status and let me know the necessary steps to reactivate it.\n\nThank you,\n${accountName.value || 'Student'}`
  )
  return `mailto:support@noras.edu.et?subject=${subject}&body=${body}`
})

const handleLogout = async () => {
  await authService.logout()
  router.push('/login')
}
</script>
