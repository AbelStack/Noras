<template>
  <div class="min-h-screen bg-white">
    <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-200/50">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-20">
          <router-link to="/" class="flex items-center">
            <span class="text-3xl font-bold text-gray-900">Noras</span>
          </router-link>
        </div>
      </div>
    </nav>

    <div class="pt-32 pb-20">
      <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Privacy Policy</h1>
        <p class="text-gray-600 mb-8">Last updated: {{ lastUpdated }}</p>
        
        <div class="prose prose-lg max-w-none space-y-8">
          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Introduction</h2>
            <p class="text-gray-700 leading-relaxed">
              At Noras, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our academic platform.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Information We Collect</h2>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Personal Information</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              We collect information that you provide directly to us, including:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Name and email address</li>
              <li>Student ID and department information</li>
              <li>Academic progress and quiz results</li>
              <li>Study materials you upload or access</li>
            </ul>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">How We Use Your Information</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We use the information we collect to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Provide, maintain, and improve our services</li>
              <li>Generate personalized study notes and quizzes</li>
              <li>Track your academic progress</li>
              <li>Communicate with you about updates and features</li>
              <li>Ensure platform security and prevent fraud</li>
            </ul>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Data Security</h2>
            <p class="text-gray-700 leading-relaxed">
              We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Your Rights</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              You have the right to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Access your personal data</li>
              <li>Correct inaccurate data</li>
              <li>Request deletion of your data</li>
              <li>Object to data processing</li>
              <li>Export your data</li>
            </ul>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Contact Us</h2>
            <p class="text-gray-700 leading-relaxed">
              If you have questions about this Privacy Policy, please contact us at: <a href="mailto:privacy@noras.edu.et" class="text-blue-600 hover:text-yellow-600 font-semibold">privacy@noras.edu.et</a>
            </p>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const lastUpdated = computed(() => {
  return new Date().toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
})
</script>
