<template>
  <div class="relative">
    <!-- Trigger Field -->
    <button
      type="button"
      @click="openPicker"
      :disabled="disabled || loading"
      :class="[
        'w-full px-4 py-3 rounded-xl border text-left transition-all flex items-center justify-between gap-3',
        disabled 
          ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
          : isOpen
            ? 'bg-white border-blue-600 ring-2 ring-blue-600/20 text-gray-900 shadow-sm'
            : selectedItem
              ? 'bg-white border-gray-300 hover:border-blue-500 text-gray-900 shadow-2xs'
              : 'bg-white border-gray-300 hover:border-blue-500 text-gray-400'
      ]"
    >
      <div class="flex items-center gap-3 min-w-0 flex-1">
        <!-- Icon -->
        <div 
          :class="[
            'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors',
            disabled 
              ? 'bg-gray-200 text-gray-400'
              : selectedItem
                ? 'bg-blue-50 text-blue-700'
                : 'bg-gray-100 text-gray-500'
          ]"
        >
          <!-- Campus Icon -->
          <svg v-if="pickerType === 'campus'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <!-- Department Icon -->
          <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
        </div>

        <!-- Text Display -->
        <div class="min-w-0 flex-1">
          <template v-if="loading">
            <span class="text-sm text-gray-500 flex items-center gap-2">
              <svg class="animate-spin h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loadingPlaceholder || 'Loading options...' }}
            </span>
          </template>
          <template v-else-if="disabled">
            <span class="text-sm text-gray-400 truncate block">{{ disabledPlaceholder || placeholder }}</span>
          </template>
          <template v-else-if="selectedItem">
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-900 truncate">{{ selectedItem.name }}</span>
              <span v-if="selectedItem.code" class="text-[11px] font-mono font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 flex-shrink-0">
                {{ selectedItem.code }}
              </span>
            </div>
            <p v-if="selectedItem.college" class="text-[11px] text-gray-500 truncate">{{ selectedItem.college }}</p>
          </template>
          <template v-else>
            <span class="text-sm text-gray-400 truncate block">{{ placeholder }}</span>
          </template>
        </div>
      </div>

      <!-- Right Chevron -->
      <div class="flex items-center gap-1.5 flex-shrink-0">
        <span v-if="selectedItem" class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <svg 
          :class="['w-4 h-4 text-gray-400 transition-transform duration-200', isOpen ? 'rotate-180 text-blue-600' : '']" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Modal / Bottom Sheet Popup (Teleported to body for zero clipping on mobile & desktop) -->
    <Teleport to="body">
      <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
          <!-- Backdrop -->
          <div 
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            @click="closePicker"
          ></div>

          <!-- Dialog Position: Mobile bottom-sheet / Desktop centered modal -->
          <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div 
              class="relative transform overflow-hidden rounded-t-3xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full sm:max-w-xl max-h-[90vh] flex flex-col border border-gray-100"
              @click.stop
            >
              <!-- Mobile Drag Handle Bar -->
              <div class="sm:hidden flex justify-center pt-3 pb-1 bg-gradient-to-r from-blue-700 via-primary-600 to-indigo-700">
                <div class="w-12 h-1.5 bg-white/40 rounded-full"></div>
              </div>

              <!-- Header Banner with Brand Blue and Yellow Highlights -->
              <div class="relative bg-gradient-to-r from-blue-700 via-primary-600 to-indigo-700 p-5 sm:p-6 text-white overflow-hidden flex-shrink-0">
                <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-yellow-400/15 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="flex items-center justify-between relative z-10">
                  <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-md ring-2 ring-white/30 flex items-center justify-center text-white flex-shrink-0">
                      <!-- Campus Icon -->
                      <svg v-if="pickerType === 'campus'" class="w-6 h-6 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      <!-- Department Icon -->
                      <svg v-else class="w-6 h-6 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                        {{ pickerType === 'campus' ? 'Select Campus' : 'Select Department' }}
                      </h3>
                      <p class="text-xs sm:text-sm text-blue-100">
                        {{ subtitleText }}
                      </p>
                    </div>
                  </div>

                  <button 
                    type="button"
                    @click="closePicker" 
                    class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors cursor-pointer"
                    title="Close"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Search Bar & College Filter Pills (for instant filtering, essential on mobile) -->
              <div class="p-3.5 sm:p-4 bg-gray-50/90 border-b border-gray-100 flex-shrink-0 space-y-2.5">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <input
                    ref="searchInputRef"
                    type="text"
                    v-model="searchQuery"
                    :placeholder="pickerType === 'campus' ? 'Search campus name or code...' : 'Search department name, code, or college...'"
                    class="w-full pl-10 pr-9 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition text-gray-900 placeholder-gray-400"
                  />
                  <button 
                    v-if="searchQuery"
                    type="button"
                    @click="searchQuery = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <!-- College Filter Bar (with Left/Right Arrow Navigation, Mouse Wheel Scroll, and Quick Select Dropdown) -->
                <div v-if="pickerType === 'department' && availableColleges.length > 1" class="space-y-1.5 pt-1">
                  <!-- Header with Quick Dropdown -->
                  <div class="flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span class="flex items-center gap-1.5">
                      <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                      </svg>
                      Filter by College:
                    </span>
                    
                    <!-- Quick Dropdown for 1-Click Jump to Any College -->
                    <select
                      v-model="selectedCollegeFilter"
                      class="text-xs font-semibold bg-white border border-gray-200 rounded-lg px-2 py-0.5 text-gray-700 hover:border-blue-400 focus:ring-2 focus:ring-blue-600 outline-none cursor-pointer max-w-[200px] truncate"
                    >
                      <option value="all">All Colleges ({{ items.length }})</option>
                      <option v-for="c in availableColleges" :key="c" :value="c">{{ c }}</option>
                    </select>
                  </div>

                  <!-- Horizontal Scrollable Chips Row with Prev/Next Buttons -->
                  <div class="relative flex items-center gap-1.5">
                    <!-- Left Arrow Button (for Desktop & Mobile) -->
                    <button
                      type="button"
                      @click="scrollChips('left')"
                      :disabled="!canScrollLeft"
                      :class="[
                        'w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 transition-all border shadow-2xs',
                        canScrollLeft
                          ? 'bg-white hover:bg-blue-50 text-gray-700 hover:text-blue-700 border-gray-200 hover:border-blue-300 cursor-pointer'
                          : 'bg-gray-100 text-gray-300 border-gray-100 cursor-not-allowed opacity-40'
                      ]"
                      aria-label="Scroll colleges left"
                      title="Scroll colleges left"
                    >
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>

                    <!-- Scrollable Container with Mouse Wheel Support on PC -->
                    <div 
                      ref="chipsContainerRef"
                      @wheel="handleChipsWheel"
                      @scroll="updateScrollState"
                      class="flex items-center gap-1.5 overflow-x-auto py-1 text-xs chips-scroll flex-1 scroll-smooth"
                    >
                      <button
                        type="button"
                        @click="selectedCollegeFilter = 'all'"
                        :class="[
                          'px-3 py-1 rounded-full whitespace-nowrap font-medium transition-all cursor-pointer flex-shrink-0',
                          selectedCollegeFilter === 'all'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'
                        ]"
                      >
                        All Colleges ({{ items.length }})
                      </button>
                      <button
                        v-for="college in availableColleges"
                        :key="college"
                        type="button"
                        @click="selectedCollegeFilter = college"
                        :class="[
                          'px-3 py-1 rounded-full whitespace-nowrap font-medium transition-all cursor-pointer flex-shrink-0',
                          selectedCollegeFilter === college
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'
                        ]"
                      >
                        {{ college }}
                      </button>
                    </div>

                    <!-- Right Arrow Button (for Desktop & Mobile) -->
                    <button
                      type="button"
                      @click="scrollChips('right')"
                      :disabled="!canScrollRight"
                      :class="[
                        'w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 transition-all border shadow-2xs',
                        canScrollRight
                          ? 'bg-white hover:bg-blue-50 text-gray-700 hover:text-blue-700 border-gray-200 hover:border-blue-300 cursor-pointer'
                          : 'bg-gray-100 text-gray-300 border-gray-100 cursor-not-allowed opacity-40'
                      ]"
                      aria-label="Scroll colleges right"
                      title="Scroll colleges right"
                    >
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Options List -->
              <div class="overflow-y-auto p-3 sm:p-4 space-y-2 flex-1 max-h-[50vh] sm:max-h-[400px]">
                <!-- Empty State -->
                <div v-if="filteredItems.length === 0" class="py-12 text-center">
                  <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <p class="text-sm font-semibold text-gray-900">No results found</p>
                  <p class="text-xs text-gray-500 mt-1">No matching {{ pickerType }} found for "{{ searchQuery }}"</p>
                  <button 
                    v-if="searchQuery || selectedCollegeFilter !== 'all'"
                    type="button"
                    @click="resetFilters"
                    class="mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                  >
                    Clear Search Filters
                  </button>
                </div>

                <!-- Campus Option Cards -->
                <template v-if="pickerType === 'campus'">
                  <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    @click="selectItem(item)"
                    :class="[
                      'p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3',
                      modelValue === item.id
                        ? 'border-blue-600 bg-blue-50/90 ring-2 ring-blue-600/20 shadow-xs'
                        : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 active:bg-blue-50/50'
                    ]"
                  >
                    <div class="flex items-center gap-3.5 min-w-0">
                      <div 
                        :class="[
                          'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 font-bold text-xs',
                          modelValue === item.id 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-blue-100 text-blue-700'
                        ]"
                      >
                        {{ item.code || 'WU' }}
                      </div>
                      <div class="min-w-0">
                        <div class="flex items-center gap-2">
                          <h4 class="text-sm font-bold text-gray-900 truncate">{{ item.name }}</h4>
                          <span 
                            v-if="item.code" 
                            class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600"
                          >
                            {{ item.code }}
                          </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                          {{ getCampusDescription(item.code) }}
                        </p>
                      </div>
                    </div>

                    <!-- Radio / Checkmark -->
                    <div class="flex-shrink-0">
                      <div 
                        :class="[
                          'w-6 h-6 rounded-full flex items-center justify-center transition-colors',
                          modelValue === item.id 
                            ? 'bg-blue-600 text-white ring-2 ring-yellow-400' 
                            : 'border-2 border-gray-300'
                        ]"
                      >
                        <svg v-if="modelValue === item.id" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Department Option Cards (Grouped by College) -->
                <template v-else>
                  <div 
                    v-for="(group, collegeName) in groupedFilteredDepartments" 
                    :key="collegeName"
                    class="space-y-2 mb-4"
                  >
                    <!-- Sticky College Section Header -->
                    <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-md py-1.5 px-3 rounded-xl border border-blue-100 flex items-center justify-between shadow-2xs">
                      <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 ring-2 ring-yellow-400"></span>
                        <h5 class="text-xs font-bold uppercase tracking-wider text-blue-900 truncate">{{ collegeName }}</h5>
                      </div>
                      <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200 flex-shrink-0">
                        {{ group.length }} {{ group.length === 1 ? 'Dept' : 'Depts' }}
                      </span>
                    </div>

                    <!-- Department Cards in this College -->
                    <div
                      v-for="item in group"
                      :key="item.id"
                      @click="selectItem(item)"
                      :class="[
                        'p-3.5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 min-h-[52px]',
                        modelValue === item.id
                          ? 'border-blue-600 bg-blue-50/90 ring-2 ring-blue-600/20 shadow-xs'
                          : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 active:bg-blue-50/50'
                      ]"
                    >
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                          <span class="text-sm font-bold text-gray-900 truncate">{{ item.name }}</span>
                          <span 
                            v-if="item.code" 
                            class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 flex-shrink-0"
                          >
                            {{ item.code }}
                          </span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                          <span class="truncate">{{ item.college || 'General Studies' }}</span>
                          <span v-if="item.total_years" class="text-gray-400">&bull; {{ item.total_years }} Yrs</span>
                        </div>
                      </div>

                      <!-- Selected Indicator -->
                      <div class="flex-shrink-0">
                        <div 
                          :class="[
                            'w-6 h-6 rounded-full flex items-center justify-center transition-colors',
                            modelValue === item.id 
                              ? 'bg-blue-600 text-white ring-2 ring-yellow-400' 
                              : 'border-2 border-gray-300'
                          ]"
                        >
                          <svg v-if="modelValue === item.id" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <!-- Footer Toolbar -->
              <div class="p-3 sm:p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3 flex-shrink-0">
                <span class="text-xs text-gray-500">
                  {{ filteredItems.length }} {{ pickerType === 'campus' ? 'campuses' : 'departments' }} available
                </span>
                <button
                  type="button"
                  @click="closePicker"
                  class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs font-semibold rounded-xl transition cursor-pointer"
                >
                  Done
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    default: null,
  },
  items: {
    type: Array,
    default: () => [],
  },
  pickerType: {
    type: String,
    default: 'campus', // 'campus' | 'department'
  },
  placeholder: {
    type: String,
    default: 'Select an option...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  disabledPlaceholder: {
    type: String,
    default: '',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingPlaceholder: {
    type: String,
    default: '',
  },
  campusName: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const selectedCollegeFilter = ref('all')
const searchInputRef = ref(null)
const chipsContainerRef = ref(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(false)

const selectedItem = computed(() => {
  if (!props.modelValue) return null
  return props.items.find(item => item.id === props.modelValue) || null
})

const subtitleText = computed(() => {
  if (props.pickerType === 'campus') {
    return 'Wollo University • 3 Official Campuses'
  }
  const prefix = props.campusName ? `${props.campusName} • ` : ''
  return `${prefix}${props.items.length} Registered Departments`
})

const availableColleges = computed(() => {
  if (props.pickerType !== 'department') return []
  const set = new Set()
  props.items.forEach(d => {
    if (d.college) set.add(d.college)
  })
  return Array.from(set).sort()
})

const updateScrollState = () => {
  if (!chipsContainerRef.value) {
    canScrollLeft.value = false
    canScrollRight.value = false
    return
  }
  const { scrollLeft, scrollWidth, clientWidth } = chipsContainerRef.value
  canScrollLeft.value = scrollLeft > 2
  canScrollRight.value = scrollLeft + clientWidth < scrollWidth - 2
}

const scrollChips = (direction) => {
  if (!chipsContainerRef.value) return
  const offset = direction === 'left' ? -200 : 200
  chipsContainerRef.value.scrollBy({ left: offset, behavior: 'smooth' })
  setTimeout(updateScrollState, 350)
}

const handleChipsWheel = (e) => {
  if (!chipsContainerRef.value) return
  if (e.deltaY !== 0) {
    e.preventDefault()
    chipsContainerRef.value.scrollLeft += e.deltaY
    updateScrollState()
  }
}

watch([availableColleges, selectedCollegeFilter], () => {
  nextTick(() => {
    updateScrollState()
  })
})

const filteredItems = computed(() => {
  let list = props.items || []

  // College filter
  if (props.pickerType === 'department' && selectedCollegeFilter.value !== 'all') {
    list = list.filter(item => item.college === selectedCollegeFilter.value)
  }

  // Search filter
  const q = (searchQuery.value || '').trim().toLowerCase()
  if (!q) return list

  return list.filter(item => {
    const nameMatch = item.name?.toLowerCase().includes(q)
    const codeMatch = item.code?.toLowerCase().includes(q)
    const collegeMatch = item.college?.toLowerCase().includes(q)
    return nameMatch || codeMatch || collegeMatch
  })
})

const groupedFilteredDepartments = computed(() => {
  const groups = {}
  filteredItems.value.forEach(item => {
    const college = item.college || 'Other Programs'
    if (!groups[college]) {
      groups[college] = []
    }
    groups[college].push(item)
  })
  return groups
})

const getCampusDescription = (code) => {
  switch (code) {
    case 'DESSIE':
      return 'Main Campus • Agriculture, Business & Economics, Natural & Social Sciences, Law, Veterinary'
    case 'KOCHA':
      return 'Kombolcha Institute of Technology (KIoT) • Informatics, Engineering & Textile'
    case 'TITA':
      return 'Tertiary Care Health Sciences • Medicine, Nursing, Pharmacy & Public Health'
    default:
      return 'Official Wollo University Campus'
  }
}

const openPicker = () => {
  if (props.disabled || props.loading) return
  isOpen.value = true
  searchQuery.value = ''
  selectedCollegeFilter.value = 'all'
  nextTick(() => {
    searchInputRef.value?.focus()
    updateScrollState()
  })
}

const closePicker = () => {
  isOpen.value = false
}

const selectItem = (item) => {
  emit('update:modelValue', item.id)
  emit('change', item)
  closePicker()
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCollegeFilter.value = 'all'
  nextTick(() => {
    updateScrollState()
  })
}
</script>

<style scoped>
.chips-scroll {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
.chips-scroll::-webkit-scrollbar {
  height: 4px;
}
.chips-scroll::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 9999px;
}
.chips-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.chips-scroll::-webkit-scrollbar-thumb:hover {
  background: #2563eb;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
