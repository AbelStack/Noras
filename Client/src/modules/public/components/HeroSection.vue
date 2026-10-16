<template>
  <section id="home" class="pt-24 sm:pt-28 md:pt-32 pb-20 bg-gradient-to-br from-beige-50 via-white to-blue-50 min-h-screen flex items-center relative overflow-hidden">
    <div class="absolute top-20 right-10 w-72 h-72 bg-blue-200/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-10 w-96 h-96 bg-beige-200/30 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-6 w-full relative z-10">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <!-- Left: Content -->
        <div class="space-y-8">
          <div class="inline-block">
            <div class="bg-gradient-to-r from-blue-100 to-blue-50 border border-blue-200/50 rounded-full px-6 py-2">
              <span class="text-blue-600 font-semibold text-sm flex items-center space-x-2">
                <Sparkles :size="16" />
                <span>Your Academic Learning Companion</span>
              </span>
            </div>
          </div>

          <h1 class="text-6xl lg:text-7xl font-bold text-gray-900 leading-tight tracking-tight">
            Meet <span class="text-blue-600">Noras</span>, <br />
            Your Study Buddy
          </h1>
          
          <p class="text-xl text-gray-600 leading-relaxed font-normal">
            AI-powered study notes, practice quizzes, and organized course materials — all in one beautiful place designed for Wollo University students.
          </p>

          <div class="flex flex-col sm:flex-row gap-4">
            <router-link to="/register" class="bg-blue-600 text-white px-10 py-4 rounded-2xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center space-x-2">
              <span>Get Started Free</span>
              <ArrowRight :size="20" />
            </router-link>
            <router-link to="/login" class="bg-white text-gray-900 px-10 py-4 rounded-2xl hover:bg-yellow-50 transition-all font-semibold text-center border-2 border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
              Sign In
            </router-link>
          </div>

          <div class="flex flex-wrap items-center gap-6 pt-4">
            <!-- Skeleton state -->
            <template v-if="loadingStats">
              <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-blue-200 animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 animate-pulse rounded-md"></div>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-blue-200 animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 animate-pulse rounded-md"></div>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-blue-200 animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 animate-pulse rounded-md"></div>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-blue-200 animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 animate-pulse rounded-md"></div>
              </div>
            </template>
            <!-- Real data loaded from database -->
            <template v-else>
              <div class="flex items-center space-x-2 text-gray-700">
                <BookOpen :size="20" class="text-blue-600 flex-shrink-0" />
                <span class="font-semibold text-sm">{{ stats.materials || 0 }}+ Materials</span>
              </div>
              <div class="flex items-center space-x-2 text-gray-700">
                <Users :size="20" class="text-blue-600 flex-shrink-0" />
                <span class="font-semibold text-sm">{{ stats.students || 0 }}+ Students</span>
              </div>
              <div class="flex items-center space-x-2 text-gray-700">
                <GraduationCap :size="20" class="text-blue-600 flex-shrink-0" />
                <span class="font-semibold text-sm">{{ stats.courses || 0 }}+ Courses</span>
              </div>
              <div class="flex items-center space-x-2 text-gray-700">
                <Building2 :size="20" class="text-blue-600 flex-shrink-0" />
                <span class="font-semibold text-sm">{{ stats.campuses || 3 }} Campuses</span>
              </div>
            </template>
          </div>
        </div>

        <!-- Right: 3D Model -->
        <div class="relative">
          <div class="relative w-full h-[600px] bg-gradient-to-br from-blue-50/50 to-beige-100/50 rounded-[40px] overflow-hidden border border-gray-200/50 shadow-2xl">
            <canvas ref="canvasRef" class="w-full h-full"></canvas>
            
            <!-- Floating elements around model -->
            <div class="absolute top-10 left-10 bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg animate-float">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                  <Brain :size="20" class="text-blue-600" />
                </div>
                <div>
                  <p class="text-xs text-gray-500 font-medium">AI Notes</p>
                  <p class="text-sm font-bold text-gray-900">Smart Learning</p>
                </div>
              </div>
            </div>

            <!-- Floating card removed - will show user's actual progress after login -->
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { BookOpen, Users, GraduationCap, Building2, Sparkles, ArrowRight, Brain, Trophy } from 'lucide-vue-next'
import * as THREE from 'three'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader'
import { publicService } from '../../../services/public'

const canvasRef = ref(null)
let scene, camera, renderer, model

const loadingStats = ref(true)
const stats = ref({
  materials: 0,
  students: 0,
  courses: 0,
  campuses: 0,
  departments: 0,
})

const fetchRealStats = async () => {
  try {
    loadingStats.value = true
    const data = await publicService.getStats()
    stats.value = data
  } catch (error) {
    console.error('Failed to load landing stats:', error)
  } finally {
    loadingStats.value = false
  }
}

onMounted(() => {
  fetchRealStats()
  if (!canvasRef.value) return

  // Scene setup
  scene = new THREE.Scene()
  scene.background = null

  // Camera
  camera = new THREE.PerspectiveCamera(50, canvasRef.value.clientWidth / canvasRef.value.clientHeight, 0.1, 1000)
  camera.position.set(0, 0, 4)

  // Renderer
  renderer = new THREE.WebGLRenderer({ 
    canvas: canvasRef.value,
    alpha: true,
    antialias: true
  })
  renderer.setSize(canvasRef.value.clientWidth, canvasRef.value.clientHeight)
  renderer.setPixelRatio(window.devicePixelRatio)

  // Lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.8)
  scene.add(ambientLight)

  const directionalLight = new THREE.DirectionalLight(0xffffff, 1)
  directionalLight.position.set(5, 5, 5)
  scene.add(directionalLight)

  const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.5)
  directionalLight2.position.set(-5, 5, -5)
  scene.add(directionalLight2)

  // Load 3D Model
  const loader = new GLTFLoader()
  loader.load(
    '/3d model.glb',
    (gltf) => {
      model = gltf.scene
      
      // Calculate bounding box to center and scale properly
      const box = new THREE.Box3().setFromObject(model)
      const center = box.getCenter(new THREE.Vector3())
      const size = box.getSize(new THREE.Vector3())
      
      // Center the model
      model.position.x = -center.x
      model.position.y = -center.y
      model.position.z = -center.z
      
      // Scale to fit the canvas (adjust 2.5 to make bigger or smaller)
      const maxDim = Math.max(size.x, size.y, size.z)
      const scale = 2.5 / maxDim
      model.scale.set(scale, scale, scale)
      
      scene.add(model)
      renderer.render(scene, camera)
    },
    undefined,
    (error) => {
      console.error('Error loading 3D model:', error)
    }
  )

  // Render once (static model)
  const render = () => {
    renderer.render(scene, camera)
  }
  render()

  // Handle resize
  const handleResize = () => {
    if (!canvasRef.value) return
    camera.aspect = canvasRef.value.clientWidth / canvasRef.value.clientHeight
    camera.updateProjectionMatrix()
    renderer.setSize(canvasRef.value.clientWidth, canvasRef.value.clientHeight)
    renderer.render(scene, camera)
  }
  window.addEventListener('resize', handleResize)

  // Cleanup
  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    if (renderer) renderer.dispose()
  })
})
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

@keyframes float-delayed {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 3s ease-in-out infinite 1.5s;
}
</style>
