<template>
  <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-200/50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center h-20">
        <router-link to="/" class="flex items-center">
          <span class="text-3xl font-bold text-gray-900">Noras</span>
        </router-link>

        <div class="hidden md:flex items-center space-x-2">
          <a 
            href="#home" 
            :class="[
              'px-5 py-2.5 rounded-xl font-medium transition-all',
              activeSection === 'home' 
                ? 'bg-blue-600 text-white shadow-md' 
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
                ? 'bg-blue-600 text-white shadow-md' 
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
                ? 'bg-blue-600 text-white shadow-md' 
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
                ? 'bg-blue-600 text-white shadow-md' 
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

        <button @click="toggleMenu" class="md:hidden text-gray-700">
          <Menu v-if="!mobileMenuOpen" :size="24" />
          <X v-else :size="24" />
        </button>
      </div>
    </div>

    <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200/50 glass-effect">
      <div class="px-6 py-5 space-y-2">
        <a 
          href="#home" 
          @click="toggleMenu" 
          :class="[
            'block px-4 py-3 rounded-xl font-medium transition-all',
            activeSection === 'home' 
              ? 'bg-blue-600 text-white' 
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
              ? 'bg-blue-600 text-white' 
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
              ? 'bg-blue-600 text-white' 
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
              ? 'bg-blue-600 text-white' 
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
