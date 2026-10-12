<template>
  <Transition name="fade">
    <div 
      v-if="show" 
      class="fixed inset-0 z-[100] bg-white flex items-center justify-center"
      @click="skipIntro"
    >
      <div class="max-w-2xl w-full px-6">
        <video
          v-if="!prefersReducedMotion"
          ref="videoRef"
          class="w-full h-auto"
          :src="videoSrc"
          muted
          playsinline
          autoplay
          @ended="onVideoEnded"
          @error="onVideoError"
        ></video>
        
        <!-- Fallback for reduced motion preference -->
        <div v-else class="flex flex-col items-center justify-center py-20">
          <img 
            src="/Noras Logo.jpg" 
            alt="Noras - AI-Powered Academic Platform"
            class="max-w-md w-full h-auto object-contain"
            loading="eager"
          />
        </div>
      </div>
      
      <!-- Skip button (subtle) -->
      <button
        v-if="!prefersReducedMotion"
        @click="skipIntro"
        class="absolute bottom-8 right-8 text-gray-400 hover:text-gray-600 text-sm font-medium transition-colors"
      >
        Skip
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const show = ref(true)
const videoRef = ref(null)
const videoSrc = '/Noras motion logo.mp4'
const prefersReducedMotion = ref(false)

// Check for reduced motion preference
onMounted(() => {
  const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
  prefersReducedMotion.value = mediaQuery.matches
  
  // Auto-hide after short delay if reduced motion
  if (prefersReducedMotion.value) {
    setTimeout(() => {
      show.value = false
    }, 2000)
  }
  
  // Check if splash was already shown in this session
  const splashShown = sessionStorage.getItem('norasSplashShown')
  if (splashShown === 'true') {
    show.value = false
  } else {
    sessionStorage.setItem('norasSplashShown', 'true')
  }
})

const onVideoEnded = () => {
  show.value = false
}

const onVideoError = () => {
  console.warn('Failed to load motion logo, showing static fallback')
  // Show static logo briefly then hide
  setTimeout(() => {
    show.value = false
  }, 2000)
}

const skipIntro = () => {
  show.value = false
}

onUnmounted(() => {
  if (videoRef.value) {
    videoRef.value.pause()
    videoRef.value.src = ''
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
