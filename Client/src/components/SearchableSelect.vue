<template>
  <div class="relative inline-block text-left" :class="widthClass" ref="dropdownRef">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleDropdown"
      :disabled="disabled"
      :class="[
        'w-full flex items-center justify-between text-left transition-all border outline-none bg-white select-none',
        size === 'sm'
          ? 'px-2.5 py-1.5 text-xs rounded-xl min-h-[34px]'
          : 'px-3.5 py-2.5 text-sm rounded-xl min-h-[42px]',
        disabled
          ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
          : isOpen
            ? 'border-primary-500 ring-2 ring-primary-500/15 text-gray-900 shadow-xs'
            : hasSelection
              ? 'border-gray-200 hover:border-gray-300 text-gray-900 shadow-2xs'
              : 'border-gray-200 hover:border-gray-300 text-gray-400'
      ]"
      :aria-expanded="isOpen"
    >
      <div class="flex items-center gap-1.5 min-w-0 flex-1 mr-1">
        <span 
          class="truncate block" 
          :class="[
            hasSelection ? 'text-gray-900 font-medium' : 'text-gray-400',
            size === 'sm' ? 'text-xs' : 'text-sm'
          ]"
        >
          {{ displayText }}
        </span>
      </div>

      <div class="flex items-center gap-1 flex-shrink-0">
        <!-- Clear Button -->
        <span
          v-if="clearable && hasSelection && !disabled"
          @click.stop="clearSelection"
          class="p-0.5 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
          title="Clear"
        >
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </span>

        <!-- Chevron Down -->
        <svg 
          :class="[
            'text-gray-400 transition-transform duration-200 flex-shrink-0 pointer-events-none',
            size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4',
            isOpen ? 'transform rotate-180 text-primary-600' : ''
          ]" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0 -translate-y-1"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-1"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-1.5 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden text-left',
          align === 'right' ? 'right-0' : 'left-0',
          dropdownWidthClass
        ]"
      >
        <!-- Search Input Bar -->
        <div class="p-2 border-b border-gray-100 bg-gray-50/80 flex items-center gap-2">
          <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            ref="searchInputRef"
            v-model="searchTerm"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full bg-transparent text-xs sm:text-sm text-gray-800 placeholder-gray-400 outline-none"
            @keydown.esc="closeDropdown"
            @keydown.enter.prevent="selectFirstMatch"
          />
          <button
            v-if="searchTerm"
            type="button"
            @click="searchTerm = ''"
            class="text-gray-400 hover:text-gray-600 p-0.5 rounded cursor-pointer"
          >
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Options List -->
        <div class="max-h-56 overflow-y-auto p-1 divide-y-0 text-left">
          <!-- All / Default Option (e.g. "All Campuses", "All Departments") -->
          <div
            v-if="allOptionLabel && matchesAllOption"
            @click="selectOption(allOptionValue)"
            :class="[
              'px-3 py-2 rounded-lg cursor-pointer transition-colors flex items-center justify-between text-xs sm:text-sm',
              isOptionSelected(allOptionValue)
                ? 'bg-primary-50 text-primary-700 font-semibold'
                : 'hover:bg-gray-100/70 text-gray-800'
            ]"
          >
            <span class="font-medium truncate">{{ allOptionLabel }}</span>
            <svg 
              v-if="isOptionSelected(allOptionValue)" 
              class="w-4 h-4 text-primary-600 flex-shrink-0 ml-2" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <!-- Empty search results -->
          <div 
            v-if="filteredOptions.length === 0 && (!allOptionLabel || !matchesAllOption)" 
            class="px-4 py-5 text-center text-xs text-gray-400"
          >
            No results found for "<span class="font-medium text-gray-600">{{ searchTerm }}</span>"
          </div>

          <!-- Filtered Options -->
          <div
            v-for="(opt, index) in filteredOptions"
            :key="getOptionKey(opt, index)"
            @click="selectOption(getOptionValue(opt))"
            :class="[
              'px-3 py-2 rounded-lg cursor-pointer transition-colors flex items-center justify-between text-xs sm:text-sm',
              isOptionSelected(getOptionValue(opt))
                ? 'bg-primary-50 text-primary-700 font-semibold'
                : 'hover:bg-gray-100/70 text-gray-800'
            ]"
          >
            <div class="min-w-0 flex-1 mr-2">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span 
                  class="truncate block font-medium" 
                  :class="isOptionSelected(getOptionValue(opt)) ? 'text-primary-700' : 'text-gray-900'"
                >
                  {{ getOptionLabel(opt) }}
                </span>
                <span 
                  v-if="getOptionCode(opt)" 
                  class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold flex-shrink-0"
                >
                  {{ getOptionCode(opt) }}
                </span>
              </div>
              <p v-if="getOptionSubtitle(opt)" class="text-[11px] text-gray-400 truncate mt-0.5">
                {{ getOptionSubtitle(opt) }}
              </p>
            </div>

            <!-- Selected checkmark -->
            <svg 
              v-if="isOptionSelected(getOptionValue(opt))" 
              class="w-4 h-4 text-primary-600 flex-shrink-0 ml-2" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  valueKey: {
    type: String,
    default: 'id'
  },
  labelKey: {
    type: String,
    default: 'name'
  },
  subtitleKey: {
    type: String,
    default: ''
  },
  codeKey: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  disabledPlaceholder: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md'].includes(v)
  },
  allOptionLabel: {
    type: String,
    default: ''
  },
  allOptionValue: {
    type: [String, Number, null],
    default: ''
  },
  clearable: {
    type: Boolean,
    default: false
  },
  align: {
    type: String,
    default: 'left',
    validator: (v) => ['left', 'right'].includes(v)
  },
  widthClass: {
    type: String,
    default: 'w-full'
  },
  dropdownWidthClass: {
    type: String,
    default: 'w-full min-w-[220px]'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const dropdownRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const searchTerm = ref('')

const getOptionValue = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt !== 'object') return opt
  return opt[props.valueKey] !== undefined ? opt[props.valueKey] : (opt.id !== undefined ? opt.id : opt.value)
}

const getOptionLabel = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt !== 'object') return String(opt)
  return opt[props.labelKey] !== undefined ? opt[props.labelKey] : (opt.name !== undefined ? opt.name : (opt.title || opt.label || ''))
}

const getOptionSubtitle = (opt) => {
  if (!opt || typeof opt !== 'object') return ''
  if (props.subtitleKey && opt[props.subtitleKey]) return opt[props.subtitleKey]
  const parts = []
  if (opt.university?.name) parts.push(opt.university.name)
  if (opt.college) parts.push(opt.college)
  if (opt.total_years) parts.push(`${opt.total_years} Yrs`)
  if (opt.subtitle) parts.push(opt.subtitle)
  return parts.join(' • ')
}

const getOptionCode = (opt) => {
  if (!opt || typeof opt !== 'object') return ''
  if (props.codeKey && opt[props.codeKey]) return opt[props.codeKey]
  return opt.code || ''
}

const getOptionKey = (opt, index) => {
  const val = getOptionValue(opt)
  return val !== undefined && val !== null && val !== '' ? val : index
}

const isOptionSelected = (val) => {
  if (val === null || val === undefined) {
    return props.modelValue === null || props.modelValue === undefined
  }
  return String(props.modelValue) === String(val)
}

const selectedOption = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return null
  return props.options.find(opt => String(getOptionValue(opt)) === String(props.modelValue)) || null
})

const hasSelection = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return false
  if (props.allOptionLabel && String(props.modelValue) === String(props.allOptionValue)) {
    return true
  }
  return String(props.modelValue) !== ''
})

const displayText = computed(() => {
  if (props.disabled && props.disabledPlaceholder) {
    return props.disabledPlaceholder
  }
  if (props.allOptionLabel && String(props.modelValue) === String(props.allOptionValue)) {
    return props.allOptionLabel
  }
  if (selectedOption.value) {
    return getOptionLabel(selectedOption.value)
  }
  return props.placeholder
})

const selectedSubtitle = computed(() => {
  if (!selectedOption.value) return ''
  return getOptionSubtitle(selectedOption.value)
})

const matchesAllOption = computed(() => {
  if (!props.allOptionLabel) return false
  const query = searchTerm.value.trim().toLowerCase()
  if (!query) return true
  return props.allOptionLabel.toLowerCase().includes(query)
})

const filteredOptions = computed(() => {
  const query = searchTerm.value.trim().toLowerCase()
  if (!query) return props.options

  return props.options.filter(opt => {
    const label = String(getOptionLabel(opt)).toLowerCase()
    const subtitle = String(getOptionSubtitle(opt)).toLowerCase()
    const code = String(getOptionCode(opt)).toLowerCase()
    return label.includes(query) || subtitle.includes(query) || code.includes(query)
  })
})

const openDropdown = () => {
  if (props.disabled) return
  isOpen.value = true
  searchTerm.value = ''
  nextTick(() => {
    searchInputRef.value?.focus()
  })
}

const closeDropdown = () => {
  isOpen.value = false
  searchTerm.value = ''
}

const toggleDropdown = () => {
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const selectOption = (val) => {
  emit('update:modelValue', val)
  emit('change', val)
  closeDropdown()
}

const clearSelection = () => {
  const emptyVal = props.allOptionLabel ? props.allOptionValue : ''
  emit('update:modelValue', emptyVal)
  emit('change', emptyVal)
}

const selectFirstMatch = () => {
  if (props.allOptionLabel && matchesAllOption.value) {
    selectOption(props.allOptionValue)
  } else if (filteredOptions.value.length > 0) {
    selectOption(getOptionValue(filteredOptions.value[0]))
  }
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('pointerdown', handleClickOutside)
})
</script>
