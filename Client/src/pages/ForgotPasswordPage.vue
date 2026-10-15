<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-8 bg-white/80 backdrop-blur-lg p-8 rounded-2xl shadow-xl">
      <div class="text-center">
        <router-link to="/" class="inline-flex items-center gap-2 mb-6">
          <NorasLogo :size="40" />
          <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
            Noras
          </span>
        </router-link>
        <h2 class="text-3xl font-bold text-gray-900">Reset Password</h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
        <p class="text-sm">{{ successMessage }}</p>
      </div>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
        <p class="text-sm">{{ errorMessage }}</p>
      </div>

      <form v-if="!successMessage" @submit.prevent="handleSubmit" class="mt-8 space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email Address
          </label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            placeholder="your@email.com"
            :disabled="loading"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          {{ loading ? 'Sending...' : 'Send Reset Link' }}
        </button>

        <div class="text-center">
          <router-link to="/login" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition">
            Back to Login
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import NorasLogo from '../components/NorasLogo.vue';

const router = useRouter();
const email = ref('');
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const handleSubmit = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  loading.value = true;

  try {
    const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/password/forgot`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      successMessage.value = data.message;
      email.value = '';
    } else {
      errorMessage.value = data.message || 'An error occurred. Please try again.';
    }
  } catch (error) {
    console.error('Forgot password error:', error);
    errorMessage.value = 'Unable to connect to the server. Please try again later.';
  } finally {
    loading.value = false;
  }
};
</script>
