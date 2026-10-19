<template>
  <div class="mt-2 space-y-2">
    <!-- Strength Meter Bar -->
    <div v-if="password.length > 0" class="space-y-1">
      <div class="flex items-center justify-between text-xs font-medium">
        <span class="text-gray-500">Password strength:</span>
        <span :class="strengthTextColor" class="font-bold">{{ strengthLabel }}</span>
      </div>
      <div class="grid grid-cols-4 gap-1.5 h-1.5">
        <div 
          v-for="index in 4" 
          :key="index"
          class="rounded-full transition-colors duration-200"
          :class="getSegmentClass(index)"
        ></div>
      </div>
    </div>

    <!-- Requirements Checklist Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs">
      <!-- 8+ Characters -->
      <div 
        class="flex items-center gap-1.5 transition-colors duration-150"
        :class="hasMinLength ? 'text-emerald-700 font-medium' : (password.length > 0 ? 'text-gray-500' : 'text-gray-400')"
      >
        <span 
          class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 transition-colors"
          :class="hasMinLength ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-gray-100 text-gray-400'"
        >
          <svg v-if="hasMinLength" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>&bull;</span>
        </span>
        <span>At least 8 characters</span>
      </div>

      <!-- Letter -->
      <div 
        class="flex items-center gap-1.5 transition-colors duration-150"
        :class="hasLetter ? 'text-emerald-700 font-medium' : (password.length > 0 ? 'text-gray-500' : 'text-gray-400')"
      >
        <span 
          class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 transition-colors"
          :class="hasLetter ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-gray-100 text-gray-400'"
        >
          <svg v-if="hasLetter" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>&bull;</span>
        </span>
        <span>At least 1 letter (a-z, A-Z)</span>
      </div>

      <!-- Number -->
      <div 
        class="flex items-center gap-1.5 transition-colors duration-150"
        :class="hasNumber ? 'text-emerald-700 font-medium' : (password.length > 0 ? 'text-gray-500' : 'text-gray-400')"
      >
        <span 
          class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 transition-colors"
          :class="hasNumber ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-gray-100 text-gray-400'"
        >
          <svg v-if="hasNumber" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>&bull;</span>
        </span>
        <span>At least 1 number (0-9)</span>
      </div>

      <!-- Symbol -->
      <div 
        class="flex items-center gap-1.5 transition-colors duration-150"
        :class="hasSymbol ? 'text-emerald-700 font-medium' : (password.length > 0 ? 'text-gray-500' : 'text-gray-400')"
      >
        <span 
          class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 transition-colors"
          :class="hasSymbol ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-gray-100 text-gray-400'"
        >
          <svg v-if="hasSymbol" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>&bull;</span>
        </span>
        <span>At least 1 symbol (!@#$%^&*)</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: {
    type: String,
    default: '',
  },
})

const hasMinLength = computed(() => (props.password || '').length >= 8)
const hasLetter = computed(() => /[a-zA-Z]/.test(props.password || ''))
const hasNumber = computed(() => /[0-9]/.test(props.password || ''))
const hasSymbol = computed(() => /[^a-zA-Z0-9]/.test(props.password || ''))

const score = computed(() => {
  let count = 0
  if (hasMinLength.value) count++
  if (hasLetter.value) count++
  if (hasNumber.value) count++
  if (hasSymbol.value) count++
  return count
})

const isAllValid = computed(() => score.value === 4)

const strengthLabel = computed(() => {
  if (score.value === 0) return 'Very Weak'
  if (score.value === 1) return 'Weak'
  if (score.value === 2) return 'Fair'
  if (score.value === 3) return 'Good'
  return 'Strong'
})

const strengthTextColor = computed(() => {
  if (score.value <= 1) return 'text-rose-600'
  if (score.value === 2) return 'text-amber-600'
  if (score.value === 3) return 'text-blue-600'
  return 'text-emerald-600'
})

const getSegmentClass = (segmentIndex) => {
  if (segmentIndex > score.value) return 'bg-gray-200'
  if (score.value <= 1) return 'bg-rose-500'
  if (score.value === 2) return 'bg-amber-500'
  if (score.value === 3) return 'bg-blue-600'
  return 'bg-emerald-500'
}

defineExpose({
  hasMinLength,
  hasLetter,
  hasNumber,
  hasSymbol,
  isAllValid,
  score,
})
</script>
