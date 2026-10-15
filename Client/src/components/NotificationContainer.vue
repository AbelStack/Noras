<template>
  <div>
    <!-- Toast Notifications (Top Right) -->
    <div class="fixed top-5 right-5 z-[100] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0">
      <transition-group
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto w-full bg-white rounded-xl shadow-xl border overflow-hidden p-4 flex items-start gap-3 transition-all"
          :class="getToastBorderClass(toast.type)"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mt-0.5">
            <!-- Success Icon -->
            <div v-if="toast.type === 'success'" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <!-- Error Icon -->
            <div v-else-if="toast.type === 'error'" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <!-- Warning Icon -->
            <div v-else-if="toast.type === 'warning'" class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <!-- Info Icon -->
            <div v-else class="w-8 h-8 rounded-full bg-blue-50 text-primary-600 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-semibold text-gray-900 leading-tight">{{ toast.title }}</h4>
            <p class="text-xs sm:text-sm text-gray-600 mt-1 leading-snug break-words">{{ toast.message }}</p>
          </div>

          <!-- Close Button -->
          <button
            @click="removeToast(toast.id)"
            class="flex-shrink-0 text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Styled Confirmation Modal -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="confirmModalState.isOpen"
        class="fixed inset-0 z-[120] overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="handleCancel"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100"
            @click.stop
          >
            <div class="p-6">
              <div class="flex items-start gap-4">
                <!-- Danger Icon -->
                <div
                  v-if="confirmModalState.type === 'danger'"
                  class="flex-shrink-0 w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center"
                >
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <!-- Primary / Info Icon -->
                <div
                  v-else
                  class="flex-shrink-0 w-12 h-12 rounded-2xl bg-blue-50 text-primary-600 flex items-center justify-center"
                >
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>

                <!-- Text content -->
                <div class="flex-1 mt-0.5">
                  <h3 class="text-lg font-bold text-gray-900" id="modal-title">
                    {{ confirmModalState.title }}
                  </h3>
                  <div class="mt-2 text-sm text-gray-600 leading-relaxed">
                    {{ confirmModalState.message }}
                  </div>
                </div>
              </div>

              <!-- Buttons -->
              <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button
                  type="button"
                  @click="handleCancel"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
                >
                  {{ confirmModalState.cancelText }}
                </button>
                <button
                  type="button"
                  @click="handleConfirm"
                  class="px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                  :class="confirmModalState.type === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500'"
                >
                  {{ confirmModalState.confirmText }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { useNotification } from '../composables/useNotification'

const { toasts, confirmModalState, removeToast, handleConfirm, handleCancel } = useNotification()

const getToastBorderClass = (type) => {
  switch (type) {
    case 'success':
      return 'border-emerald-200'
    case 'error':
      return 'border-rose-200'
    case 'warning':
      return 'border-amber-200'
    default:
      return 'border-blue-200'
  }
}
</script>
