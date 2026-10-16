<template>
  <div class="w-full animate-pulse">
    <!-- 1. Stats Cards Skeleton -->
    <div v-if="type === 'stats'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="n in (count || 4)" 
        :key="n"
        class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-3"
      >
        <div class="h-3.5 bg-gray-200 rounded-md w-2/3"></div>
        <div class="h-8 bg-gray-200 rounded-lg w-1/3"></div>
        <div class="h-2.5 bg-gray-100 rounded-md w-1/2"></div>
      </div>
    </div>

    <!-- 2. Table Skeleton -->
    <div v-else-if="type === 'table'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <!-- Table Header Skeleton -->
      <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
        <div class="h-4 bg-gray-200 rounded w-24"></div>
        <div class="h-4 bg-gray-200 rounded w-36 hidden sm:block"></div>
        <div class="h-4 bg-gray-200 rounded w-28 hidden md:block"></div>
        <div class="h-4 bg-gray-200 rounded w-20"></div>
        <div class="h-4 bg-gray-200 rounded w-16"></div>
      </div>
      <!-- Table Rows -->
      <div class="divide-y divide-gray-100">
        <div 
          v-for="n in (rows || 5)" 
          :key="n"
          class="px-6 py-4.5 flex items-center justify-between gap-4"
        >
          <div class="flex items-center gap-3 w-1/4">
            <div class="h-6 w-16 bg-gray-200 rounded-lg"></div>
          </div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded-md w-3/4"></div>
            <div class="h-3 bg-gray-100 rounded-md w-1/2"></div>
          </div>
          <div class="hidden md:block w-32">
            <div class="h-6 bg-gray-100 rounded-full w-24"></div>
          </div>
          <div class="hidden sm:block w-24">
            <div class="h-5 bg-gray-200 rounded-full w-16"></div>
          </div>
          <div class="flex items-center gap-2 justify-end w-20">
            <div class="h-7 w-12 bg-gray-100 rounded-md"></div>
            <div class="h-7 w-12 bg-gray-100 rounded-md"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Cards Grid Skeleton -->
    <div 
      v-else-if="type === 'cards'" 
      :class="[
        'grid gap-6',
        columnsClass || 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
      ]"
    >
      <div 
        v-for="n in (count || 6)" 
        :key="n"
        class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between space-y-4"
      >
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-5 bg-gray-200 rounded-lg w-20"></div>
            <div class="h-4 bg-gray-100 rounded-md w-16"></div>
          </div>
          <div class="h-5 bg-gray-200 rounded-md w-4/5"></div>
          <div class="space-y-1.5 pt-1">
            <div class="h-3 bg-gray-100 rounded-md w-full"></div>
            <div class="h-3 bg-gray-100 rounded-md w-3/4"></div>
          </div>
        </div>
        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
          <div class="h-4 bg-gray-100 rounded-md w-24"></div>
          <div class="h-4 bg-gray-200 rounded-md w-20"></div>
        </div>
      </div>
    </div>

    <!-- 4. Dashboard Skeleton (Header + Stats + Charts) -->
    <div v-else-if="type === 'dashboard'" class="space-y-8">
      <div class="space-y-2">
        <div class="h-8 bg-gray-200 rounded-lg w-64"></div>
        <div class="h-4 bg-gray-100 rounded-md w-80"></div>
      </div>
      <!-- 4 Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-3">
          <div class="h-3.5 bg-gray-200 rounded-md w-2/3"></div>
          <div class="h-9 bg-gray-200 rounded-lg w-1/3"></div>
          <div class="h-3 bg-gray-100 rounded-md w-1/2"></div>
        </div>
      </div>
      <!-- 2 Chart Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
          <div class="h-5 bg-gray-200 rounded-md w-48"></div>
          <div class="h-64 bg-gray-100/70 rounded-xl flex items-center justify-center">
            <div class="w-36 h-36 rounded-full border-8 border-gray-200"></div>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
          <div class="h-5 bg-gray-200 rounded-md w-48"></div>
          <div class="h-64 bg-gray-100/70 rounded-xl p-6 flex items-end justify-between gap-3">
            <div v-for="bar in [40, 65, 30, 85, 50, 75, 60]" :key="bar" class="flex-1 bg-gray-200 rounded-t-lg" :style="{ height: `${bar}%` }"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Detail / Document Skeleton -->
    <div v-else-if="type === 'detail'" class="space-y-6">
      <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm space-y-4">
        <div class="h-4 bg-gray-100 rounded-md w-32"></div>
        <div class="h-7 bg-gray-200 rounded-lg w-3/4"></div>
        <div class="flex items-center gap-3 pt-2">
          <div class="h-5 bg-gray-100 rounded-md w-28"></div>
          <div class="h-5 bg-gray-100 rounded-md w-20"></div>
          <div class="h-5 bg-gray-100 rounded-md w-24"></div>
        </div>
      </div>
      <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm space-y-4">
        <div class="h-5 bg-gray-200 rounded-md w-40"></div>
        <div class="space-y-2 pt-2">
          <div class="h-3.5 bg-gray-100 rounded-md w-full"></div>
          <div class="h-3.5 bg-gray-100 rounded-md w-11/12"></div>
          <div class="h-3.5 bg-gray-100 rounded-md w-4/5"></div>
          <div class="h-3.5 bg-gray-100 rounded-md w-9/12"></div>
        </div>
        <div class="space-y-2 pt-4">
          <div class="h-3.5 bg-gray-100 rounded-md w-full"></div>
          <div class="h-3.5 bg-gray-100 rounded-md w-5/6"></div>
          <div class="h-3.5 bg-gray-100 rounded-md w-3/4"></div>
        </div>
      </div>
    </div>

    <!-- 6. Quiz Taking Skeleton -->
    <div v-else-if="type === 'quiz'" class="max-w-3xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div class="h-4 bg-gray-200 rounded-md w-24"></div>
        <div class="h-4 bg-gray-200 rounded-md w-32"></div>
      </div>
      <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm space-y-6">
        <div class="h-6 bg-gray-200 rounded-lg w-4/5"></div>
        <div class="space-y-3 pt-4">
          <div v-for="i in 4" :key="i" class="h-14 bg-gray-50 border border-gray-200 rounded-xl w-full"></div>
        </div>
      </div>
    </div>

    <!-- 7. Study Tasks / List Skeleton -->
    <div v-else-if="type === 'tasks'" class="space-y-4">
      <div 
        v-for="n in (count || 5)" 
        :key="n"
        class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs flex items-center justify-between gap-4"
      >
        <div class="flex items-center gap-4 flex-1">
          <div class="w-6 h-6 rounded-full bg-gray-200 flex-shrink-0"></div>
          <div class="space-y-2 flex-1">
            <div class="h-4 bg-gray-200 rounded-md w-2/3"></div>
            <div class="flex items-center gap-3">
              <div class="h-3 bg-gray-100 rounded w-24"></div>
              <div class="h-3 bg-gray-100 rounded w-16"></div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-7 w-16 bg-gray-100 rounded-lg"></div>
          <div class="h-7 w-8 bg-gray-100 rounded-lg"></div>
        </div>
      </div>
    </div>

    <!-- Default: Generic shimmer block -->
    <div v-else class="space-y-3">
      <div class="h-4 bg-gray-200 rounded-md w-3/4"></div>
      <div class="h-4 bg-gray-100 rounded-md w-full"></div>
      <div class="h-4 bg-gray-100 rounded-md w-2/3"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'cards', // 'stats', 'table', 'cards', 'dashboard', 'detail', 'quiz', 'tasks'
  },
  count: {
    type: Number,
    default: null,
  },
  rows: {
    type: Number,
    default: null,
  },
  columnsClass: {
    type: String,
    default: '',
  },
})
</script>
