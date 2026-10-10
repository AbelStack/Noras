<template>
  <section class="py-20 bg-gradient-to-br from-blue-600 via-blue-500 to-blue-600 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-yellow-400/10 rounded-full blur-3xl"></div>

    <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
      <h2 class="text-5xl font-bold text-white mb-6 tracking-tight">
        Ready to Study Smarter?
      </h2>
      <p class="text-xl text-blue-50 mb-10 font-normal leading-relaxed">
        Join Wollo University students using Noras to ace their exams
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link to="/register" class="bg-white text-blue-600 px-12 py-5 rounded-2xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center space-x-2">
          <span>Create Your Account</span>
          <ArrowRight :size="20" />
        </router-link>
        <router-link to="/login" class="bg-transparent text-white px-12 py-5 rounded-2xl hover:bg-white/10 transition-all font-semibold border-2 border-white shadow-lg hover:shadow-xl transform hover:-translate-y-1">
          Sign In
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ArrowRight } from 'lucide-vue-next'
</script>
