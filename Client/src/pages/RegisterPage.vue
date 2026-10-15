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
            Start Your Academic Success Story
          </h2>
          <p class="text-xl text-blue-700 font-normal leading-relaxed">
            Join hundreds of Wollo University students who are studying smarter with AI-powered tools.
          </p>
        </div>

        <div class="space-y-6">
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <Zap :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">Quick Setup</h3>
              <p class="text-blue-600 font-normal">Get started in less than 2 minutes</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <Award :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">Verified Students Only</h3>
              <p class="text-blue-600 font-normal">Secure platform for university students</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center flex-shrink-0">
              <BarChart3 :size="24" class="text-blue-700" />
            </div>
            <div>
              <h3 class="text-blue-700 font-bold text-lg mb-1">Track Your Growth</h3>
              <p class="text-blue-600 font-normal">See your progress in real-time</p>
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

        <div class="mb-8 text-center">
          <h1 class="text-4xl font-bold text-gray-900 mb-3 tracking-tight">Create Your Account</h1>
          <p class="text-gray-600 text-lg font-normal">Start studying smarter today</p>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-gray-200/50 p-8">
          <!-- Success Message -->
          <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl">
            <p class="text-sm text-green-600 font-medium">{{ successMessage }}</p>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm text-red-600 font-medium">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label for="fullName" class="block text-sm font-semibold text-gray-900 mb-2">Full Name</label>
              <input 
                type="text" 
                id="fullName" 
                v-model="fullName"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Enter your full name"
                required
              />
            </div>

            <div>
              <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
              <input 
                type="email" 
                id="email" 
                v-model="email"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="your.email@example.com"
                required
              />
            </div>

            <div>
              <label for="studentId" class="block text-sm font-semibold text-gray-900 mb-2">Student ID</label>
              <input 
                type="text" 
                id="studentId" 
                v-model="studentId"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Enter your student ID"
                required
              />
            </div>

            <!-- Campus Selection -->
            <div>
              <label for="campus" class="block text-sm font-semibold text-gray-900 mb-2">
                Campus <span class="text-red-500">*</span>
              </label>
              <select 
                id="campus" 
                v-model="selectedCampusId"
                @change="onCampusChange"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white text-gray-900 text-sm"
              >
                <option :value="null" disabled>Select your campus...</option>
                <option v-for="campus in campuses" :key="campus.id" :value="campus.id">
                  {{ campus.name }}
                </option>
              </select>
            </div>

            <!-- Department Selection -->
            <div>
              <label for="department" class="block text-sm font-semibold text-gray-900 mb-2">
                Department <span class="text-red-500">*</span>
              </label>
              <select 
                id="department" 
                v-model="selectedDepartmentId"
                :disabled="!selectedCampusId || isDepartmentsLoading"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white text-gray-900 text-sm disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
              >
                <option :value="null" disabled>
                  {{ !selectedCampusId ? 'Please select a campus first...' : (isDepartmentsLoading ? 'Loading departments...' : 'Select your department...') }}
                </option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }} ({{ dept.code }})
                </option>
              </select>
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
              <input 
                type="password" 
                id="password" 
                v-model="password"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Create a strong password"
                required
              />
            </div>

            <div>
              <label for="confirmPassword" class="block text-sm font-semibold text-gray-900 mb-2">Confirm Password</label>
              <input 
                type="password" 
                id="confirmPassword" 
                v-model="confirmPassword"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition bg-white"
                placeholder="Confirm your password"
                required
              />
            </div>

            <div class="pt-2">
              <label class="flex items-start">
                <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-600 mt-1" required>
                <span class="ml-2 text-sm text-gray-600 font-normal">
                  I agree to the <router-link to="/legal/terms-of-use" class="text-blue-600 hover:text-blue-700 font-semibold">Terms of Use</router-link> and <router-link to="/legal/privacy-policy" class="text-blue-600 hover:text-blue-700 font-semibold">Privacy Policy</router-link>
                </span>
              </label>
            </div>

            <button 
              type="submit" 
              :disabled="isLoading"
              class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Creating Account...' : 'Create Account' }}
            </button>
          </form>

          <div class="mt-6 text-center">
            <p class="text-gray-600 font-normal text-sm">
              Already have an account? 
              <router-link to="/login" class="text-blue-600 hover:text-blue-700 font-semibold">Log in</router-link>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Zap, Award, BarChart3, ArrowLeft } from 'lucide-vue-next'
import { authService } from '../services/auth'

const router = useRouter()
const fullName = ref('')
const email = ref('')
const studentId = ref('')
const selectedCampusId = ref(null)
const selectedDepartmentId = ref(null)
const campuses = ref([])
const departments = ref([])
const isCampusesLoading = ref(false)
const isDepartmentsLoading = ref(false)
const password = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

onMounted(async () => {
  isCampusesLoading.value = true
  try {
    campuses.value = await authService.getCampuses()
  } catch (err) {
    console.error('Failed to load campuses:', err)
  } finally {
    isCampusesLoading.value = false
  }
})

const onCampusChange = async () => {
  selectedDepartmentId.value = null
  departments.value = []
  if (!selectedCampusId.value) return

  isDepartmentsLoading.value = true
  try {
    departments.value = await authService.getDepartments(selectedCampusId.value)
  } catch (err) {
    console.error('Failed to load departments:', err)
  } finally {
    isDepartmentsLoading.value = false
  }
}

const handleRegister = async () => {
  if (isLoading.value) return

  errorMessage.value = ''
  successMessage.value = ''

  if (!selectedCampusId.value) {
    errorMessage.value = 'Please select your campus.'
    return
  }

  if (!selectedDepartmentId.value) {
    errorMessage.value = 'Please select your department.'
    return
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match!'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters long.'
    return
  }

  isLoading.value = true

  try {
    await authService.register({
      name: fullName.value,
      email: email.value,
      student_id: studentId.value,
      university_id: selectedCampusId.value,
      department_id: selectedDepartmentId.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    })

    successMessage.value = 'Registration successful! Your account is pending approval. You will be notified once approved.'
    
    // Clear form
    fullName.value = ''
    email.value = ''
    studentId.value = ''
    selectedCampusId.value = null
    selectedDepartmentId.value = null
    departments.value = []
    password.value = ''
    confirmPassword.value = ''

    // Redirect to login after 3 seconds
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (error) {
    const errors = error.response?.data?.errors
    if (errors) {
      errorMessage.value = Object.values(errors).flat().join(' ')
    } else {
      errorMessage.value = error.response?.data?.message || error.message || 'Registration failed. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
