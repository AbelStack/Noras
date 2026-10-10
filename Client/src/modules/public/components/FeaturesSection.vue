<template>
  <section id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="inline-block bg-gradient-to-r from-blue-100 to-blue-50 border border-blue-200/50 rounded-full px-5 py-2 mb-6">
          <span class="text-blue-600 font-semibold text-sm">Features</span>
        </div>
        <h2 class="text-5xl font-bold text-gray-900 mb-5 tracking-tight">
          Everything You Need to Excel
        </h2>
        <p class="text-xl text-gray-600 font-normal leading-relaxed">
          Noras provides powerful tools designed for academic success
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 border border-gray-200/50 rounded-3xl p-8 hover:shadow-xl transition-all card-hover-shadow">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-6 border border-gray-200">
            <FolderOpen :size="32" class="text-gray-700" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Organized Materials</h3>
          <p class="text-gray-700 leading-relaxed font-normal">
            PDFs and resources organized by department, year, and course.
          </p>
        </div>

        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 border border-gray-200/50 rounded-3xl p-8 hover:shadow-xl transition-all card-hover-shadow">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-6 border border-gray-200">
            <Lightbulb :size="32" class="text-gray-700" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">AI Study Notes</h3>
          <p class="text-gray-700 leading-relaxed font-normal">
            Concise notes generated from PDFs to study efficiently.
          </p>
        </div>

        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 border border-gray-200/50 rounded-3xl p-8 hover:shadow-xl transition-all card-hover-shadow">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-6 border border-gray-200">
            <FileCheck :size="32" class="text-gray-700" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Practice Quizzes</h3>
          <p class="text-gray-700 leading-relaxed font-normal">
            Test your knowledge with explanations for every answer.
          </p>
        </div>

        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 border border-gray-200/50 rounded-3xl p-8 hover:shadow-xl transition-all card-hover-shadow">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-6 border border-gray-200">
            <TrendingUp :size="32" class="text-gray-700" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Track Progress</h3>
          <p class="text-gray-700 leading-relaxed font-normal">
            Monitor your learning journey and exam readiness.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { FolderOpen, Lightbulb, FileCheck, TrendingUp } from 'lucide-vue-next'
</script>
