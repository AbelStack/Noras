<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 sm:px-6 py-3.5 bg-white border-t border-gray-100 text-xs text-gray-500 select-none">
    <!-- Left: summary count and per-page selector -->
    <div class="flex flex-wrap items-center gap-3">
      <span>
        Showing <span class="font-semibold text-gray-800">{{ fromItem }}</span> to 
        <span class="font-semibold text-gray-800">{{ toItem }}</span> of 
        <span class="font-semibold text-gray-800">{{ totalItems }}</span> {{ itemLabel }}
      </span>

      <!-- Per Page Selector -->
      <div v-if="showPerPage && perPageOptions?.length > 1" class="flex items-center gap-1.5 pl-3 border-l border-gray-200">
        <span class="text-gray-400">Per page:</span>
        <select
          :value="perPage"
          @change="onPerPageChange(Number($event.target.value))"
          class="px-2 py-1 border border-gray-200 rounded-lg text-xs bg-white text-gray-700 font-medium focus:ring-1 focus:ring-primary-500 outline-none cursor-pointer hover:border-gray-300"
        >
          <option v-for="opt in perPageOptions" :key="opt" :value="opt">
            {{ opt }}
          </option>
        </select>
      </div>
    </div>

    <!-- Right: Page buttons -->
    <div v-if="totalPages > 1" class="flex items-center gap-1 self-center sm:self-auto">
      <!-- First Page -->
      <button
        type="button"
        @click="goToPage(1)"
        :disabled="currentPage <= 1"
        class="p-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors cursor-pointer"
        title="First Page"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>

      <!-- Previous Page -->
      <button
        type="button"
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage <= 1"
        class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent font-medium transition-colors flex items-center gap-1 cursor-pointer"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="hidden sm:inline">Prev</span>
      </button>

      <!-- Page Numbers with Sliding Window -->
      <template v-for="(item, idx) in visiblePages" :key="idx">
        <span v-if="item === '...'" class="px-2 py-1 text-gray-400 font-bold select-none">
          &hellip;
        </span>
        <button
          v-else
          type="button"
          @click="goToPage(item)"
          :class="[
            'min-w-[32px] h-8 px-2 rounded-lg text-xs font-semibold transition-all cursor-pointer',
            currentPage === item
              ? 'bg-primary-600 text-white shadow-xs'
              : 'border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-gray-900'
          ]"
        >
          {{ item }}
        </button>
      </template>

      <!-- Next Page -->
      <button
        type="button"
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage >= totalPages"
        class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent font-medium transition-colors flex items-center gap-1 cursor-pointer"
      >
        <span class="hidden sm:inline">Next</span>
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Last Page -->
      <button
        type="button"
        @click="goToPage(totalPages)"
        :disabled="currentPage >= totalPages"
        class="p-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors cursor-pointer"
        title="Last Page"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
    default: 1
  },
  totalPages: {
    type: Number,
    required: true,
    default: 1
  },
  totalItems: {
    type: Number,
    required: true,
    default: 0
  },
  perPage: {
    type: Number,
    default: 10
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 20, 50, 100]
  },
  showPerPage: {
    type: Boolean,
    default: true
  },
  itemLabel: {
    type: String,
    default: 'items'
  }
})

const emit = defineEmits(['update:currentPage', 'update:perPage', 'change'])

const fromItem = computed(() => {
  if (props.totalItems === 0) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const toItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems)
})

const visiblePages = computed(() => {
  const total = props.totalPages
  const current = props.currentPage
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const pages = []
  pages.push(1)

  if (current > 3) {
    pages.push('...')
  }

  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  if (current < total - 2) {
    pages.push('...')
  }

  pages.push(total)
  return pages
})

const goToPage = (page) => {
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('update:currentPage', page)
  emit('change', page)
}

const onPerPageChange = (val) => {
  emit('update:perPage', val)
  emit('update:currentPage', 1)
  emit('change', 1)
}
</script>
