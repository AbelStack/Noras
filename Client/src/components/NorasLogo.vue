<template>
  <div class="flex items-center gap-3">
    <img 
      :src="logoSrc" 
      :alt="altText"
      :class="['object-contain', imageClass]"
      :loading="eager ? 'eager' : 'lazy'"
    />
    <span :class="['font-bold tracking-tight', textClass]">Noras</span>
  </div>
</template>

<script setup>
defineProps({
  logoSrc: {
    type: String,
    default: '/Noras Logo.jpg'
  },
  altText: {
    type: String,
    default: 'Noras - AI-Powered Academic Platform'
  },
  imageClass: {
    type: String,
    default: 'h-10'
  },
  textClass: {
    type: String,
    default: 'text-2xl text-gray-900'
  },
  eager: {
    type: Boolean,
    default: false
  }
})
</script>
