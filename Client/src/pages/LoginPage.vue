<template>
  <div class="min-h-screen bg-white flex">
    <!-- Left Sidebar -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-100 via-blue-50 to-blue-100 relative overflow-hidden p-12 flex-col justify-between">
      <div class="absolute top-0 left-0 w-full h-full opacity-20">
        <div class="absolute top-20 right-20 w-96 h-96 bg-blue-200 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-yellow-200 rounded-full blur-3xl"></div>
      </div>

      <div class="relative z-10">
        <router-link to="/" class="inline-flex items-center gap-2 mb-16">
          <img 
            src="/Noras logo-md.png" 
            alt="Noras"
            class="h-12 w-auto object-contain"
            loading="eager"
          />
          <div class="h-10 w-px bg-blue-300"></div>
          <span class="text-3xl font-bold text-blue-700 tracking-tight">Noras</span>
        </router-link>

        <div class="text-center max-w-md mx-auto mb-12">
          <h2 class="text-5xl font-bold text-blue-700 mb-6 leading-tight">
            Welcome Back to Your Learning Journey
          </h2>
          <p class="text-xl text-blue-700 font-normal leading-relaxed">
            Access your personalized study materials, AI-generated notes, and track your academic progress.
          </p>
        </div>

        <div class="space-y-6">
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <BookOpen :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">Organized Materials</h3>
              <p class="text-blue-600 font-normal">All your course materials in one place</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <Brain :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">AI Study Notes</h3>
              <p class="text-blue-600 font-normal">Smart summaries for efficient learning</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <TrendingUp :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">Track Progress</h3>
              <p class="text-blue-600 font-normal">Monitor your learning achievements</p>
            </div>
          </div>
        </div>
      </div>

      <div class="relative z-10 text-blue-600 font-normal">
        <p>&copy; 2026 Noras. Developed by Abel T.</p>
      </div>
    </div>

    <!-- Right Content -->
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-gradient-to-br from-beige-50 via-white to-blue-50/30">
      <div class="max-w-md w-full">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-10">
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

        <div class="mb-10 text-center">
          <h1 class="text-4xl font-bold text-gray-900 mb-3 tracking-tight">Welcome Back</h1>
          <p class="text-gray-600 text-lg font-normal">Log in to continue your studies</p>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-gray-200/50 p-8">
          <form @submit.prevent="handleLogin" class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">Email Address</label>
              <input 
                type="email" 
                id="email" 
                v-model="email"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="your.email@university.edu"
                required
              />
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
              <input 
                type="password" 
                id="password" 
                v-model="password"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Enter your password"
                required
              />
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center">
                <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-600">
                <span class="ml-2 text-sm text-gray-600 font-medium">Remember me</span>
              </label>
              <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">Forgot password?</a>
            </div>

            <button 
              type="submit" 
              class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold shadow-md hover:shadow-lg"
            >
              Log In
            </button>
          </form>

          <div class="mt-6 text-center">
            <p class="text-gray-600 font-normal text-sm">
              Don't have an account? 
              <router-link to="/register" class="text-blue-600 hover:text-blue-700 font-semibold">Create one now</router-link>
            </p>
          </div>
        </div>

        <div class="text-center mt-6">
          <router-link to="/" class="text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center justify-center space-x-1">
            <ArrowLeft :size="16" />
            <span>Back to home</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { BookOpen, Brain, TrendingUp, ArrowLeft } from 'lucide-vue-next'

const router = useRouter()
const email = ref('')
const password = ref('')

const handleLogin = () => {
  // TODO: Replace with actual API call to authenticate user
  // const response = await fetch('/api/auth/login', {
  //   method: 'POST',
  //   headers: { 'Content-Type': 'application/json' },
  //   body: JSON.stringify({
  //     email: email.value,
  //     password: password.value
  //   })
  // })
  // if (response.ok) {
  //   const data = await response.json()
  //   // Store token and redirect to dashboard
  //   localStorage.setItem('token', data.token)
  //   router.push('/dashboard')
  // }
  alert('Login functionality will be implemented with backend integration.')
}
</script>
