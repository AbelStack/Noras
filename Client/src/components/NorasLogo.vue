<template>
  <div class="flex items-center">
    <img 
      :src="logoSrc" 
      :alt="altText"
      :class="['object-contain', imageClass]"
      loading="lazy"
    />
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
  }
})
</script>
