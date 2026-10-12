<template>
  <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <div class="flex justify-between items-center h-16 sm:h-18 md:h-20">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
          <img 
            src="/Noras Logo.jpg" 
            alt="Noras"
            class="h-8 sm:h-9 md:h-10 w-auto object-contain"
            loading="eager"
          />
          <span class="text-xl sm:text-2xl md:text-2xl font-bold text-gray-900 tracking-tight">Noras</span>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-2">
          <a 
            href="#home" 
            :class="[
              'px-5 py-2.5 rounded-xl font-medium transition-all',
              activeSection === 'home' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-yellow-50'
            ]"
          >
            Home
          </a>
          <a 
            href="#features" 
            :class="[
              'px-5 py-2.5 rounded-xl font-medium transition-all',
              activeSection === 'features' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-yellow-50'
            ]"
          >
            Features
          </a>
          <a 
            href="#how-it-works" 
            :class="[
              'px-5 py-2.5 rounded-xl font-medium transition-all',
              activeSection === 'how-it-works' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-yellow-50'
            ]"
          >
            How It Works
          </a>
          <a 
            href="#about" 
            :class="[
              'px-5 py-2.5 rounded-xl font-medium transition-all',
              activeSection === 'about' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-yellow-50'
            ]"
          >
            About
          </a>
          <router-link 
            to="/login" 
            class="px-5 py-2.5 rounded-xl font-medium transition-all text-gray-600 hover:text-gray-900 hover:bg-yellow-50"
          >
            Login
          </router-link>
          <router-link 
            to="/register" 
            class="ml-2 bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-medium shadow-md hover:shadow-lg"
          >
            Get Started
          </router-link>
        </div>

        <!-- Mobile Menu Button -->
        <button 
          @click="toggleMenu" 
          class="md:hidden text-gray-700 p-2 -mr-2 hover:bg-gray-100/50 rounded-lg transition-colors"
          :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
          :aria-expanded="mobileMenuOpen"
        >
          <Menu v-if="!mobileMenuOpen" :size="24" />
          <X v-else :size="24" />
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200/50 glass-effect">
      <div class="px-4 py-4 space-y-1">
        <a 
          href="#home" 
          @click="toggleMenu" 
          :class="[
            'block px-4 py-3 rounded-xl font-medium transition-all',
            activeSection === 'home' 
              ? 'bg-blue-100 text-blue-700' 
              : 'text-gray-700 hover:text-gray-900 hover:bg-yellow-50'
          ]"
        >
          Home
        </a>
        <a 
          href="#features" 
          @click="toggleMenu" 
          :class="[
            'block px-4 py-3 rounded-xl font-medium transition-all',
            activeSection === 'features' 
              ? 'bg-blue-100 text-blue-700' 
              : 'text-gray-700 hover:text-gray-900 hover:bg-yellow-50'
          ]"
        >
          Features
        </a>
        <a 
          href="#how-it-works" 
          @click="toggleMenu" 
          :class="[
            'block px-4 py-3 rounded-xl font-medium transition-all',
            activeSection === 'how-it-works' 
              ? 'bg-blue-100 text-blue-700' 
              : 'text-gray-700 hover:text-gray-900 hover:bg-yellow-50'
          ]"
        >
          How It Works
        </a>
        <a 
          href="#about" 
          @click="toggleMenu" 
          :class="[
            'block px-4 py-3 rounded-xl font-medium transition-all',
            activeSection === 'about' 
              ? 'bg-blue-100 text-blue-700' 
              : 'text-gray-700 hover:text-gray-900 hover:bg-yellow-50'
          ]"
        >
          About
        </a>
        <router-link 
          to="/login" 
          @click="toggleMenu" 
          class="block px-4 py-3 rounded-xl font-medium transition-all text-gray-700 hover:text-gray-900 hover:bg-yellow-50"
        >
          Login
        </router-link>
        <router-link 
          to="/register" 
          @click="toggleMenu" 
          class="block bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-colors font-medium text-center mt-2"
        >
          Get Started
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Menu, X } from 'lucide-vue-next'

const mobileMenuOpen = ref(false)
const activeSection = ref('home')

const toggleMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const handleScroll = () => {
  const sections = ['home', 'features', 'how-it-works', 'about']
  const scrollPosition = window.scrollY + 150

  for (const sectionId of sections) {
    const section = document.getElementById(sectionId)
    if (section) {
      const offsetTop = section.offsetTop
      const offsetHeight = section.offsetHeight

      if (scrollPosition >= offsetTop && scrollPosition < offsetTop + offsetHeight) {
        activeSection.value = sectionId
        break
      }
    }
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>
