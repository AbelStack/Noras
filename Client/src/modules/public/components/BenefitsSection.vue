<template>
  <section id="about" class="py-20 bg-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <div class="space-y-8">
          <div>
            <div class="inline-block bg-gradient-to-r from-blue-100 to-blue-50 border border-blue-200/50 rounded-full px-5 py-2 mb-6">
              <span class="text-blue-600 font-semibold text-sm flex items-center space-x-2">
                <Award :size="16" />
                <span>Why Noras?</span>
              </span>
            </div>
            <h2 class="text-5xl font-bold text-gray-900 tracking-tight mb-6">
              Your Study Companion
            </h2>
            <p class="text-xl text-gray-600 font-normal leading-relaxed">
              Designed specifically for Wollo University students
            </p>
          </div>

          <div class="space-y-6">
            <div class="flex items-start space-x-4 bg-gray-50/50 rounded-2xl p-5 border border-gray-200/50">
              <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0 border border-gray-200">
                <FolderOpen :size="24" class="text-gray-700" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Stay Organized</h3>
                <p class="text-gray-600 leading-relaxed font-normal">All materials in one place, organized by department.</p>
              </div>
            </div>

            <div class="flex items-start space-x-4 bg-gray-50/50 rounded-2xl p-5 border border-gray-200/50">
              <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0 border border-gray-200">
                <Zap :size="24" class="text-gray-700" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Study Smarter</h3>
                <p class="text-gray-600 leading-relaxed font-normal">AI-generated notes save time during revision.</p>
              </div>
            </div>

            <div class="flex items-start space-x-4 bg-gray-50/50 rounded-2xl p-5 border border-gray-200/50">
              <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0 border border-gray-200">
                <BarChart3 :size="24" class="text-gray-700" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Track Progress</h3>
                <p class="text-gray-600 leading-relaxed font-normal">Practice quizzes reveal knowledge gaps.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-[40px] p-12 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
            
            <div class="relative z-10 text-center text-white">
              <GraduationCap :size="64" class="mx-auto mb-6 text-white/90" />
              
              <h3 class="text-3xl font-bold mb-4">Built for Wollo University</h3>
              <p class="text-blue-50 text-sm mb-6 font-normal">
                Serving students across {{ stats?.campuses || 3 }} Campuses:
                <span class="font-semibold text-white">{{ stats?.campuses_list?.map(c => c.name).join(', ') || 'Kombolcha, Dessie, and Tita' }}</span>
              </p>
              <div class="space-y-4">
                <div class="flex items-center space-x-4 text-white">
                  <div class="w-8 flex-shrink-0">
                    <CheckCircle :size="24" />
                  </div>
                  <span class="font-medium text-left">Verified students across {{ stats?.departments || 7 }} academic departments</span>
                </div>
                <div class="flex items-center space-x-4 text-white">
                  <div class="w-8 flex-shrink-0">
                    <CheckCircle :size="24" />
                  </div>
                  <span class="font-medium text-left">Organized by semester curriculum & study years</span>
                </div>
                <div class="flex items-center space-x-4 text-white">
                  <div class="w-8 flex-shrink-0">
                    <CheckCircle :size="24" />
                  </div>
                  <span class="font-medium text-left">100% Free AI study summaries & practice quizzes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { FolderOpen, Zap, BarChart3, GraduationCap, CheckCircle, Award } from 'lucide-vue-next'
import { publicService } from '../../../services/public'

const stats = ref(null)

onMounted(async () => {
  try {
    stats.value = await publicService.getStats()
  } catch (e) {
    // graceful fallback
  }
})
</script>
