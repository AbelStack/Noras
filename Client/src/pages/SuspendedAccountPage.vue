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

    <!-- Main Suspension Notice Card (Wide) -->
    <main class="max-w-3xl w-full mx-auto my-8">
      <div class="bg-white rounded-3xl shadow-xl border border-red-100 overflow-hidden text-left relative">
        <!-- Top accent banner -->
        <div class="h-2 w-full bg-gradient-to-r from-red-500 via-amber-500 to-rose-600"></div>

        <!-- Header -->
        <div class="px-6 py-6 sm:px-8 border-b border-gray-100 flex items-start gap-4 bg-gradient-to-b from-red-50/30 to-transparent">
          <div class="w-12 h-12 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center flex-shrink-0 shadow-xs">
            <ShieldAlert class="w-6 h-6" />
          </div>
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider mb-1.5">
              <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
              Status: Suspended
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
              Your Account is Suspended
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
              Access to your Noras student portal has been temporarily suspended by the university administration.
            </p>
          </div>
        </div>

        <!-- Body: 2-column layout -->
        <div class="p-6 sm:p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Left Column: Details & Impact -->
            <div class="md:col-span-7 space-y-4">
              <!-- User Info Card -->
              <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-2.5">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Account Details</h4>
                <div class="space-y-2 text-xs sm:text-sm">
                  <div v-if="accountName" class="flex items-center justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500">Account Name:</span>
                    <span class="font-semibold text-gray-900">{{ accountName }}</span>
                  </div>
                  <div class="flex items-center justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500">Email Address:</span>
                    <span class="font-mono font-semibold text-gray-900 break-all">{{ accountEmail || 'Not specified' }}</span>
                  </div>
                  <div class="flex items-center justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500">Status:</span>
                    <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold">
                      <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                      Suspended (Action Required)
                    </span>
                  </div>
                  <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Institution:</span>
                    <span class="font-medium text-gray-700">Wollo University</span>
                  </div>
                </div>
              </div>

              <!-- Information / Impact Notice -->
              <div class="rounded-2xl border border-red-100 bg-red-50/40 p-4 space-y-1.5">
                <h4 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center gap-1.5">
                  <AlertCircle class="w-4 h-4 text-red-600" />
                  What Does This Mean?
                </h4>
                <p class="text-xs text-gray-700 leading-relaxed">
                  While your account is suspended, access to lecture notes, course materials, AI study aids, and quiz features is paused.
                </p>
                <p class="text-xs text-gray-500 leading-relaxed">
                  Please contact the administrator or your campus registrar office to clarify the reason for suspension and request account reactivation.
                </p>
              </div>
            </div>

            <!-- Right Column: Resolution & Actions -->
            <div class="md:col-span-5 flex flex-col justify-between space-y-4">
              <!-- How to Resolve -->
              <div class="bg-gradient-to-br from-amber-50/80 via-white to-red-50/30 border border-amber-200/80 rounded-2xl p-4.5 space-y-2.5">
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                  <Info class="w-4 h-4 text-amber-600" />
                  How to Resolve This
                </h4>
                <ul class="text-xs text-gray-600 space-y-2">
                  <li class="flex items-start gap-2">
                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">1</span>
                    <span>Contact the university administrator or registrar.</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">2</span>
                    <span>State your registered email and student identification.</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">3</span>
                    <span>Once reviewed, access will be reinstated promptly.</span>
                  </li>
                </ul>
              </div>

              <!-- Action Buttons -->
              <div class="space-y-2.5 pt-1">
                <!-- Direct Email Admin Button -->
                <a
                  :href="adminMailtoLink"
                  class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer"
                >
                  <Mail class="w-4 h-4" />
                  <span>Email Admin (support@noras.edu.et)</span>
                </a>

                <!-- Contact Support Page Button -->
                <router-link
                  to="/legal/contact"
                  class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm transition-all cursor-pointer"
                >
                  <ExternalLink class="w-3.5 h-3.5 text-gray-500" />
                  <span>Contact Support via Help Form</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Help -->
        <div class="px-6 py-4 sm:px-8 border-t border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
          <div class="flex items-center gap-2">
            <Building2 class="w-4 h-4 text-gray-400" />
            <span>Wollo University Portal</span>
          </div>

          <button @click="handleLogout" class="hover:text-blue-600 font-semibold underline cursor-pointer">
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
import { 
  ShieldAlert, 
  Mail, 
  ExternalLink, 
  AlertCircle, 
  Info, 
  Building2 
} from 'lucide-vue-next'
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
