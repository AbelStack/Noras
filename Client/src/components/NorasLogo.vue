<template>
  <div class="flex items-center space-x-3">
    <div :class="['rounded-2xl flex items-center justify-center', iconClass]">
      <Sparkles :size="iconSize" :class="sparkleClass" />
    </div>
    <span :class="['font-bold', textClass]">Noras</span>
  </div>
</template>

<script setup>
import { Sparkles } from 'lucide-vue-next'

defineProps({
  iconClass: {
    type: String,
    default: 'w-12 h-12 bg-blue-600'
  },
  iconSize: {
    type: Number,
    default: 24
  },
  sparkleClass: {
    type: String,
    default: 'text-white'
  },
  textClass: {
    type: String,
    default: 'text-4xl text-white'
  }
})
</script>
