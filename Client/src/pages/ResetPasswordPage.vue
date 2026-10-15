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
        <h2 class="text-3xl font-bold text-gray-900">Set New Password</h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your new password below.
        </p>
      </div>

      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
        <p class="text-sm">{{ successMessage }}</p>
        <p class="text-sm mt-2">
          <router-link to="/login" class="font-medium underline">
            Go to Login
          </router-link>
        </p>
      </div>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
        <p class="text-sm">{{ errorMessage }}</p>
      </div>

      <form v-if="!successMessage" @submit.prevent="handleSubmit" class="mt-8 space-y-6">
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            New Password
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            minlength="8"
            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            placeholder="Enter new password"
            :disabled="loading"
          />
          <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirm New Password
          </label>
          <input
            id="password_confirmation"
            v-model="passwordConfirmation"
            type="password"
            required
            minlength="8"
            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            placeholder="Confirm new password"
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
          {{ loading ? 'Resetting...' : 'Reset Password' }}
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import NorasLogo from '../components/NorasLogo.vue';

const route = useRoute();
const router = useRouter();

const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const token = ref('');
const email = ref('');

onMounted(() => {
  token.value = route.query.token || '';
  email.value = route.query.email || '';

  if (!token.value || !email.value) {
    errorMessage.value = 'Invalid password reset link. Please request a new one.';
  }
});

const handleSubmit = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match.';
    return;
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.';
    return;
  }

  loading.value = true;

  try {
    const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/password/reset`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      successMessage.value = data.message;
      password.value = '';
      passwordConfirmation.value = '';
    } else {
      errorMessage.value = data.message || 'An error occurred. Please try again.';
    }
  } catch (error) {
    console.error('Reset password error:', error);
    errorMessage.value = 'Unable to connect to the server. Please try again later.';
  } finally {
    loading.value = false;
  }
};
</script>
