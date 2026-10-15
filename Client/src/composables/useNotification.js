import { ref } from 'vue'

const toasts = ref([])
let toastIdCounter = 0

const confirmModalState = ref({
  isOpen: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'danger', // 'danger' | 'primary' | 'warning'
  resolve: null,
})

export function useNotification() {
  const addToast = (type, message, title = '') => {
    const id = ++toastIdCounter
    const toast = {
      id,
      type,
      title: title || (type === 'success' ? 'Success' : type === 'error' ? 'Error' : type === 'warning' ? 'Notice' : 'Information'),
      message,
    }
    toasts.value.push(toast)

    setTimeout(() => {
      removeToast(id)
    }, 4500)
    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  const success = (message, title = '') => addToast('success', message, title)
  const error = (message, title = '') => addToast('error', message, title)
  const warning = (message, title = '') => addToast('warning', message, title)
  const info = (message, title = '') => addToast('info', message, title)

  const confirm = ({
    title = 'Are you sure?',
    message = 'This action cannot be undone.',
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    type = 'danger',
  } = {}) => {
    return new Promise((resolve) => {
      confirmModalState.value = {
        isOpen: true,
        title,
        message,
        confirmText,
        cancelText,
        type,
        resolve,
      }
    })
  }

  const handleConfirm = () => {
    if (confirmModalState.value.resolve) {
      confirmModalState.value.resolve(true)
    }
    confirmModalState.value.isOpen = false
  }

  const handleCancel = () => {
    if (confirmModalState.value.resolve) {
      confirmModalState.value.resolve(false)
    }
    confirmModalState.value.isOpen = false
  }

  return {
    toasts,
    confirmModalState,
    removeToast,
    success,
    error,
    warning,
    info,
    confirm,
    handleConfirm,
    handleCancel,
  }
}
