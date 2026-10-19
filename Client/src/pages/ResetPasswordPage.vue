<template>
  <div class="min-h-screen bg-gradient-to-br from-beige-50 via-white to-blue-50/40 flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6 bg-white/90 backdrop-blur-md p-8 rounded-3xl shadow-xl border border-gray-200/60">
      <div class="text-center">
        <router-link to="/" class="inline-flex items-center justify-center mb-6" title="Noras Home">
          <NorasLogo imageClass="h-10 w-auto" textClass="text-2xl font-bold text-gray-900" dividerClass="h-8" />
        </router-link>
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Set New Password</h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your new password below to reset your account credentials.
        </p>
      </div>

      <div v-if="successMessage" class="bg-blue-50 border border-blue-200 text-primary-700 px-4 py-3 rounded-xl" role="alert">
        <div class="flex items-start gap-2.5">
          <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-sm font-medium">{{ successMessage }}</p>
            <p class="text-xs mt-2">
              <router-link to="/login" class="font-bold underline text-primary-800 hover:text-primary-900">
                Proceed to Login &rarr;
              </router-link>
            </p>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl" role="alert">
        <div class="flex items-start gap-2.5">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm font-medium">{{ errorMessage }}</p>
        </div>
      </div>

      <form v-if="!successMessage" @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
            New Password
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition text-sm"
            placeholder="Create a strong new password"
            :disabled="loading"
          />
          <PasswordRequirements :password="password" />
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
            Confirm New Password
          </label>
          <input
            id="password_confirmation"
            v-model="passwordConfirmation"
            type="password"
            required
            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition text-sm"
            placeholder="Confirm new password"
            :disabled="loading"
          />
          <p v-if="passwordConfirmation && password !== passwordConfirmation" class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Passwords do not match
          </p>
          <p v-else-if="passwordConfirmation && password === passwordConfirmation" class="mt-1.5 text-xs text-emerald-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Passwords match
          </p>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-yellow-400 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
          <span v-if="loading" class="inline-flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Resetting...
          </span>
          <span v-else>Reset Password</span>
        </button>

        <div class="text-center pt-2">
          <router-link to="/login" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition">
            &larr; Back to Login
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import NorasLogo from '../components/NorasLogo.vue'
import PasswordRequirements from '../components/PasswordRequirements.vue'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const token = ref('')
const email = ref('')

onMounted(() => {
  token.value = route.query.token || ''
  email.value = route.query.email || ''

  if (!token.value || !email.value) {
    errorMessage.value = 'Invalid or missing password reset link. Please request a new one.'
  }
})

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters long.'
    return
  }

  if (!/[a-zA-Z]/.test(password.value)) {
    errorMessage.value = 'Password must contain at least one letter.'
    return
  }

  if (!/[0-9]/.test(password.value)) {
    errorMessage.value = 'Password must contain at least one number.'
    return
  }

  if (!/[^a-zA-Z0-9]/.test(password.value)) {
    errorMessage.value = 'Password must contain at least one symbol (e.g. !@#$%^&*).'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/password/reset', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    successMessage.value = response.data?.message || 'Your password has been reset successfully. You can now log in with your new password.'
    password.value = ''
    passwordConfirmation.value = ''
  } catch (error) {
    console.error('Reset password error:', error)
    errorMessage.value = error.response?.data?.message || 'An error occurred. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
