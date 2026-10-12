<template>
  <div class="min-h-screen bg-white">
    <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-200/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex justify-between items-center h-16 sm:h-18 md:h-20">
          <router-link to="/" class="flex items-center gap-2 flex-shrink-0">
            <img 
              src="/Noras Logo.jpg" 
              alt="Noras"
              class="h-8 sm:h-9 md:h-10 w-auto object-contain"
              loading="eager"
            />
            <div class="h-6 sm:h-7 md:h-8 w-px bg-gray-300"></div>
            <span class="text-xl sm:text-2xl md:text-2xl font-bold text-gray-900 tracking-tight">Noras</span>
          </router-link>
        </div>
      </div>
    </nav>

    <div class="pt-24 sm:pt-28 md:pt-32 pb-20">
      <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Terms of Use</h1>
        <p class="text-gray-600 mb-8">Last updated: {{ lastUpdated }}</p>
        
        <div class="prose prose-lg max-w-none space-y-8">
          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Agreement to Terms</h2>
            <p class="text-gray-700 leading-relaxed">
              By accessing or using Noras, you agree to be bound by these Terms of Use. If you disagree with any part of these terms, you may not access the service.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Eligibility</h2>
            <p class="text-gray-700 leading-relaxed">
              Noras is exclusively available to verified students of Wollo University. You must provide accurate information during registration and maintain the confidentiality of your account credentials.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Acceptable Use</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              You agree to use Noras only for lawful purposes and in accordance with these Terms. You agree NOT to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Share your account credentials with others</li>
              <li>Upload malicious or inappropriate content</li>
              <li>Attempt to access other users' data</li>
              <li>Use the platform for commercial purposes</li>
              <li>Interfere with the platform's operation</li>
            </ul>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Content Ownership</h2>
            <p class="text-gray-700 leading-relaxed">
              You retain ownership of any content you upload to Noras. By uploading content, you grant us a license to use, store, and process that content to provide our services.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">AI-Generated Content</h2>
            <p class="text-gray-700 leading-relaxed">
              Noras uses AI to generate study notes and quizzes. While we strive for accuracy, AI-generated content should be used as a study aid and not as a sole source of information.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Termination</h2>
            <p class="text-gray-700 leading-relaxed">
              We reserve the right to terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users or the platform.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Changes to Terms</h2>
            <p class="text-gray-700 leading-relaxed">
              We reserve the right to modify these Terms at any time. We will notify users of any material changes. Continued use of the platform after changes constitutes acceptance of the new Terms.
            </p>
          </section>

          <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Contact</h2>
            <p class="text-gray-700 leading-relaxed">
              Questions about the Terms of Use should be sent to: <a href="mailto:legal@noras.edu.et" class="text-blue-600 hover:text-yellow-600 font-semibold">legal@noras.edu.et</a>
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
