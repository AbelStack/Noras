<template>
  <div id="app">
    <LoadingSplash />
    <router-view />
  </div>
</template>

<script setup>
import LoadingSplash from './components/LoadingSplash.vue'
</script>
