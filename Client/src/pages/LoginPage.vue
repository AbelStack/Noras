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
              <input 
                type="password" 
                id="password" 
                v-model="password"
                class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Enter your password"
                required
              />
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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { BookOpen, Brain, TrendingUp, ArrowLeft } from 'lucide-vue-next'
import { authService } from '../services/auth'

const router = useRouter()
const email = ref('')
const password = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

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
    errorMessage.value = error.response?.data?.message || 'Login failed. Please check your email and password.'
  } finally {
    isLoading.value = false
  }
}
</script>
