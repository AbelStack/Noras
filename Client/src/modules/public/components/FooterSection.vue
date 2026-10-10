<template>
  <footer class="bg-gray-900 text-gray-400 py-16">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid md:grid-cols-3 gap-12 mb-12">
        <div>
          <div class="mb-5">
            <span class="text-3xl font-bold text-white">Noras</span>
          </div>
          <p class="text-gray-400 leading-relaxed font-normal">
            Your AI-powered academic companion for Wollo University students.
          </p>
        </div>

        <div>
          <h3 class="text-white font-bold mb-5 text-sm uppercase tracking-wider">Quick Links</h3>
          <ul class="space-y-3">
            <li><a href="#home" class="hover:text-yellow-400 transition-colors font-medium">Home</a></li>
            <li><a href="#features" class="hover:text-yellow-400 transition-colors font-medium">Features</a></li>
            <li><a href="#how-it-works" class="hover:text-yellow-400 transition-colors font-medium">How It Works</a></li>
            <li><a href="#about" class="hover:text-yellow-400 transition-colors font-medium">About</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-white font-bold mb-5 text-sm uppercase tracking-wider">Legal</h3>
          <ul class="space-y-3">
            <li><router-link to="/legal/contact" class="hover:text-yellow-400 transition-colors font-medium">Contact</router-link></li>
            <li><router-link to="/legal/privacy-policy" class="hover:text-yellow-400 transition-colors font-medium">Privacy Policy</router-link></li>
            <li><router-link to="/legal/terms-of-use" class="hover:text-yellow-400 transition-colors font-medium">Terms of Use</router-link></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 text-center">
        <p class="font-medium">
          <span>&copy; {{ currentYear }} Noras. Developed by Abel T.</span>
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
