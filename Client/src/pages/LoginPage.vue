<template>
  <div class="min-h-screen lg:h-screen lg:overflow-hidden bg-white flex flex-col lg:flex-row">
    <!-- Left Sidebar (compacted so entire page fits in viewport without scrolling) -->
    <div class="hidden lg:flex lg:w-1/2 lg:h-full bg-gradient-to-br from-blue-100 via-blue-50 to-blue-100 relative overflow-hidden p-8 xl:p-12 flex-col justify-between flex-shrink-0">
      <div class="absolute top-0 left-0 w-full h-full opacity-20">
        <div class="absolute top-20 right-20 w-96 h-96 bg-blue-200 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-yellow-200 rounded-full blur-3xl"></div>
      </div>

      <div class="relative z-10">
        <router-link to="/" class="inline-flex items-center gap-2 mb-6 xl:mb-10">
          <img 
            src="/Noras logo-md.png" 
            alt="Noras"
            class="h-10 xl:h-12 w-auto object-contain"
            loading="eager"
          />
          <div class="h-8 xl:h-10 w-px bg-blue-300"></div>
          <span class="text-2xl xl:text-3xl font-bold text-blue-700 tracking-tight">Noras</span>
        </router-link>

        <div class="text-center max-w-md mx-auto mb-6 xl:mb-8">
          <h2 class="text-3xl xl:text-4xl font-bold text-blue-700 mb-2.5 xl:mb-3.5 leading-tight">
            Welcome Back to Your Learning Journey
          </h2>
          <p class="text-sm xl:text-base text-blue-700 font-normal leading-relaxed">
            Access your personalized study materials, AI-generated notes, and track your academic progress.
          </p>
        </div>

        <div class="space-y-3.5 xl:space-y-4">
          <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 xl:w-11 xl:h-11 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <BookOpen :size="20" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-base mb-0.5">Organized Materials</h3>
              <p class="text-blue-600 text-xs sm:text-sm font-normal">All your course materials in one place</p>
            </div>
          </div>
          <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 xl:w-11 xl:h-11 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <Brain :size="20" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-base mb-0.5">AI Study Notes</h3>
              <p class="text-blue-600 text-xs sm:text-sm font-normal">Smart summaries for efficient learning</p>
            </div>
          </div>
          <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 xl:w-11 xl:h-11 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <TrendingUp :size="20" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-base mb-0.5">Track Progress</h3>
              <p class="text-blue-600 text-xs sm:text-sm font-normal">Monitor your learning achievements</p>
            </div>
          </div>
        </div>
      </div>

      <div class="relative z-10 text-blue-600 font-normal text-xs xl:text-sm">
        <p>&copy; 2026 Noras. Developed by Abel T.</p>
      </div>
    </div>

    <!-- Right Content (centered, no scrolling on desktop) -->
    <div class="flex-1 lg:h-full flex items-center justify-center px-6 py-8 sm:py-10 bg-gradient-to-br from-beige-50 via-white to-blue-50/30 overflow-y-auto lg:overflow-hidden">
      <div class="max-w-md w-full my-auto">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-6">
          <router-link to="/" class="inline-flex items-center gap-2 justify-center">
            <img 
              src="/Noras logo-sm.png" 
              alt="Noras"
              class="h-10 w-auto object-contain"
              loading="eager"
            />
            <div class="h-8 w-px bg-gray-300"></div>
            <span class="text-2xl font-bold text-gray-900 tracking-tight">Noras</span>
          </router-link>
        </div>

        <div class="mb-6 text-center">
          <h1 class="text-3xl xl:text-4xl font-bold text-gray-900 mb-2 tracking-tight">Welcome Back</h1>
          <p class="text-gray-600 text-sm sm:text-base font-normal">Log in to continue your studies</p>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-gray-200/50 p-6 sm:p-8">
          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm text-red-600 font-medium">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-4 sm:space-y-5">
            <div>
              <label for="email" class="block text-sm font-semibold text-gray-900 mb-1.5">Email Address</label>
              <input 
                type="email" 
                id="email" 
                v-model="email"
                class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="your.email@example.com"
                required
              />
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold text-gray-900 mb-1.5">Password</label>
              <div class="relative">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password" 
                  v-model="password"
                  class="w-full pl-4 pr-11 py-2.5 sm:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                  placeholder="Enter your password"
                  required
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-700 transition-colors focus:outline-none cursor-pointer"
                  :title="showPassword ? 'Hide password' : 'Show password'"
                  tabindex="-1"
                >
                  <EyeOff v-if="showPassword" class="w-5 h-5" />
                  <Eye v-else class="w-5 h-5" />
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center">
                <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-600">
                <span class="ml-2 text-sm text-gray-600 font-medium">Remember me</span>
              </label>
              <router-link to="/forgot-password" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                Forgot password?
              </router-link>
            </div>

            <button 
              type="submit" 
              :disabled="isLoading"
              class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Logging in...' : 'Log In' }}
            </button>
          </form>

          <div class="mt-5 text-center">
            <p class="text-gray-600 font-normal text-sm">
              Don't have an account? 
              <router-link to="/register" class="text-blue-600 hover:text-blue-700 font-semibold">Create one now</router-link>
            </p>
          </div>
        </div>

        <div class="text-center mt-5">
          <router-link to="/" class="text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center justify-center space-x-1">
            <ArrowLeft :size="16" />
            <span>Back to home</span>
          </router-link>
        </div>

        <!-- Mobile Footer -->
        <div class="lg:hidden text-center mt-6 text-xs text-gray-500 font-normal">
          <p>&copy; 2026 Noras. Developed by Abel T.</p>
        </div>
      </div>
    </div>

    <!-- Suspended Account Wide Modal Popup -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      enter-to-class="opacity-100 translate-y-0 sm:scale-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0 sm:scale-100"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
      <div v-if="showSuspendedModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="showSuspendedModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
          <div 
            class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl lg:max-w-3xl border border-red-100"
            @click.stop
          >
            <!-- Top Gradient Stripe -->
            <div class="h-2 w-full bg-gradient-to-r from-red-500 via-amber-500 to-rose-600"></div>

            <!-- Modal Header -->
            <div class="px-6 py-5 sm:px-8 sm:py-6 border-b border-gray-100 flex items-start justify-between bg-gradient-to-b from-red-50/30 to-transparent">
              <div class="flex items-start gap-3.5 sm:gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center flex-shrink-0 shadow-xs">
                  <ShieldAlert class="w-6 h-6" />
                </div>
                <div>
                  <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    Account Suspended
                  </div>
                  <h3 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                    Your Account is Suspended
                  </h3>
                  <p class="text-xs sm:text-sm text-gray-600 mt-0.5">
                    Access to your student portal features has been temporarily placed on hold.
                  </p>
                </div>
              </div>

              <!-- Close button -->
              <button 
                type="button" 
                @click="showSuspendedModal = false"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                title="Dismiss"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Body (Spacious 2-column layout on desktop) -->
            <div class="p-6 sm:p-8 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                <!-- Left Column: Details & Impact -->
                <div class="md:col-span-7 space-y-4">
                  <!-- Account Information Card -->
                  <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-2.5">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Account Details</h4>
                    <div class="space-y-2 text-xs sm:text-sm">
                      <div class="flex items-center justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Registered Email:</span>
                        <span class="font-mono font-semibold text-gray-900 break-all">{{ suspendedEmail || 'Not specified' }}</span>
                      </div>
                      <div class="flex items-center justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Status:</span>
                        <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold">
                          <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                          Suspended (Inactive)
                        </span>
                      </div>
                      <div class="flex items-center justify-between py-1">
                        <span class="text-gray-500">Institution:</span>
                        <span class="font-medium text-gray-700">Wollo University</span>
                      </div>
                    </div>
                  </div>

                  <!-- Why did this happen & Impact -->
                  <div class="rounded-2xl border border-red-100 bg-red-50/40 p-4 space-y-1.5">
                    <h4 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center gap-1.5">
                      <AlertCircle class="w-4 h-4 text-red-600" />
                      What Does This Mean?
                    </h4>
                    <p class="text-xs text-gray-700 leading-relaxed">
                      While your account is suspended, access to lecture notes, course materials, AI study aids, and quiz features is paused.
                    </p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                      This status may occur due to administrative verification, registrar hold, or policy compliance review.
                    </p>
                  </div>
                </div>

                <!-- Right Column: Resolution Steps & Actions -->
                <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                  <!-- How to Resolve -->
                  <div class="bg-gradient-to-br from-amber-50/80 via-white to-red-50/30 border border-amber-200/80 rounded-2xl p-4 space-y-2.5">
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                      <Info class="w-4 h-4 text-amber-600" />
                      How to Reactivate
                    </h4>
                    <ul class="text-xs text-gray-600 space-y-2">
                      <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">1</span>
                        <span>Contact the administrator or university registrar.</span>
                      </li>
                      <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">2</span>
                        <span>Provide your registered email and student ID.</span>
                      </li>
                      <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">3</span>
                        <span>Once verified, access will be reinstated promptly.</span>
                      </li>
                    </ul>
                  </div>

                  <!-- Action Buttons -->
                  <div class="space-y-2.5 pt-1">
                    <!-- Primary Email Admin Button -->
                    <a
                      :href="adminMailtoLink"
                      class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all cursor-pointer"
                    >
                      <Mail class="w-4 h-4" />
                      <span>Email Administrator</span>
                    </a>

                    <!-- Contact Support via Help Page -->
                    <router-link
                      to="/legal/contact"
                      class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm transition-all cursor-pointer"
                    >
                      <ExternalLink class="w-3.5 h-3.5 text-gray-500" />
                      <span>Contact Support Form</span>
                    </router-link>
                  </div>
                </div>

              </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 sm:px-8 border-t border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row items-center justify-between gap-3">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <Building2 class="w-4 h-4 text-gray-400" />
                <span>Wollo University Support: <strong class="text-gray-700 font-semibold">support@noras.edu.et</strong></span>
              </div>

              <button
                type="button"
                @click="showSuspendedModal = false"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 font-semibold text-xs sm:text-sm transition-colors cursor-pointer shadow-2xs"
              >
                Close &amp; Back to Login
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  BookOpen, 
  Brain, 
  TrendingUp, 
  ArrowLeft,
  ShieldAlert,
  Mail,
  ExternalLink,
  X,
  AlertCircle,
  Info,
  Building2,
  Eye,
  EyeOff
} from 'lucide-vue-next'
import { authService } from '../services/auth'

const router = useRouter()
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const showSuspendedModal = ref(false)
const suspendedEmail = ref('')

const adminMailtoLink = computed(() => {
  const mail = suspendedEmail.value || 'my student account'
  const subject = encodeURIComponent(`Account Suspension Inquiry - ${mail}`)
  const body = encodeURIComponent(
    `Hello Administrator,\n\nMy account (${mail}) has been suspended. Please assist me in resolving this issue and reactivating my account.\n\nThank you.`
  )
  return `mailto:support@noras.edu.et?subject=${subject}&body=${body}`
})

const handleLogin = async () => {
  if (isLoading.value) return

  errorMessage.value = ''
  isLoading.value = true

  try {
    const { user } = await authService.login(email.value, password.value)
    
    // Redirect based on role
    if (user.role === 'admin') {
      router.push('/admin/dashboard')
    } else {
      router.push('/dashboard')
    }
  } catch (error) {
    const status = error.response?.status
    const data = error.response?.data
    const msg = data?.message || error.message || ''

    if (status === 403 && (data?.account_status === 'suspended' || msg.toLowerCase().includes('suspended'))) {
      suspendedEmail.value = email.value
      showSuspendedModal.value = true
      return
    }

    errorMessage.value = msg || 'Login failed. Please check your email and password.'
  } finally {
    isLoading.value = false
  }
}
</script>
