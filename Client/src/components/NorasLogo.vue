<template>
  <div class="flex items-center gap-2">
    <img 
      :src="logoSrc" 
      :alt="altText"
      :class="['object-contain', imageClass]"
      :loading="eager ? 'eager' : 'lazy'"
    />
    <div :class="['h-8 w-px bg-gray-300', dividerClass]"></div>
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
    default: 'Noras'
  },
  imageClass: {
    type: String,
    default: 'h-10'
  },
  textClass: {
    type: String,
    default: 'text-2xl text-gray-900'
  },
  dividerClass: {
    type: String,
    default: 'h-8'
  },
  eager: {
    type: Boolean,
    default: false
  }
})
</script>
