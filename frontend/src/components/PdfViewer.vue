<template>
  <v-dialog
    v-model="dialog"
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
  >
    <v-card class="pdf-viewer-card">
      <!-- Header -->
      <v-card-title class="pdf-viewer-header">
        <div class="d-flex align-center justify-space-between w-100">
          <div class="d-flex align-center">
            <v-icon
              size="24"
              class="mr-3"
              color="error"
            >
              mdi-file-pdf-box
            </v-icon>
            <div>
              <h3 class="text-h6 mb-0">
                {{ document?.name || 'PDF Viewer' }}
              </h3>
              <p class="text-caption text-medium-emphasis mb-0">
                {{ document?.file_path }}
              </p>
            </div>
          </div>
          
          <!-- Toolbar -->
          <div class="d-flex align-center">
            <!-- Action Buttons -->
            <v-btn
              icon="mdi-download"
              variant="text"
              size="small"
              class="mr-2"
              @click="downloadPdf"
            />
            <v-btn
              icon="mdi-open-in-new"
              variant="text"
              size="small"
              class="mr-2"
              @click="openInNewTab"
            />
            <v-btn
              icon="mdi-close"
              variant="text"
              size="small"
              @click="closePdfViewer"
            />
          </div>
        </div>
      </v-card-title>
      
      <!-- Loading State -->
      <div
        v-if="loading"
        class="pdf-loading"
      >
        <v-progress-circular
          indeterminate
          color="primary"
          size="48"
        />
        <p class="mt-4 text-body-1">
          Loading PDF...
        </p>
      </div>
      
      <!-- Error State -->
      <div
        v-else-if="error"
        class="pdf-error"
      >
        <v-icon
          size="64"
          color="error"
          class="mb-4"
        >
          mdi-alert-circle
        </v-icon>
        <h3 class="text-h6 mb-2">
          Failed to Load PDF
        </h3>
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ error }}
        </p>
        <v-btn
          color="primary"
          variant="outlined"
          @click="retryLoad"
        >
          Retry
        </v-btn>
      </div>
      
      <!-- PDF iframe Container -->
      <div
        v-else
        class="pdf-container"
      >
        <iframe
          ref="pdfFrame"
          :src="pdfUrl"
          class="pdf-iframe"
          @load="handleIframeLoad"
          @error="handleIframeError"
        />
      </div>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

console.log('Simple PDF Viewer loaded at:', new Date().toISOString())

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  document: {
    type: Object,
    default: null
  },
  pdfUrl: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'download'])

// Reactive state
const loading = ref(false)
const error = ref('')
const pdfFrame = ref(null)

// Computed
const dialog = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

const pdfUrl = computed(() => {
  if (props.pdfUrl) {
    return props.pdfUrl
  } else if (props.document?.id) {
    return `/api/documents/${props.document.id}/download`
  }
  return ''
})

// Watch for dialog changes
watch(() => props.modelValue, (newVal) => {
  if (newVal && pdfUrl.value) {
    loading.value = true
    error.value = ''
    console.log('Opening PDF:', pdfUrl.value)
    
    // Test the URL directly first
    testPdfUrl()
    
    // Set a timeout in case iframe never loads
    setTimeout(() => {
      if (loading.value) {
        console.warn('PDF loading timeout after 10 seconds')
        loading.value = false
        error.value = 'PDF loading timeout. Try opening in new tab.'
      }
    }, 10000)
  } else {
    cleanup()
  }
})

// Methods
const testPdfUrl = async () => {
  try {
    console.log('Testing PDF URL:', pdfUrl.value)
    const response = await fetch(pdfUrl.value, {
      method: 'HEAD' // Just check if URL is accessible
    })
    console.log('PDF URL test response:', response.status, response.statusText)
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
  } catch (error) {
    console.error('PDF URL test failed:', error)
    loading.value = false
    error.value = `Failed to access PDF: ${error.message}`
  }
}

const handleIframeLoad = () => {
  console.log('PDF iframe loaded successfully')
  loading.value = false
  error.value = ''
}

const handleIframeError = (event) => {
  console.error('Failed to load PDF in iframe:', event)
  loading.value = false
  error.value = 'Failed to load PDF in viewer'
}

const openInNewTab = () => {
  if (pdfUrl.value) {
    window.open(pdfUrl.value, '_blank')
  }
}

const downloadPdf = () => {
  emit('download', props.document)
}

const closePdfViewer = () => {
  dialog.value = false
}

const retryLoad = () => {
  if (pdfFrame.value) {
    pdfFrame.value.src = pdfUrl.value
    loading.value = true
    error.value = ''
  }
}

const cleanup = () => {
  error.value = ''
  loading.value = false
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  if (!dialog.value) return
  
  switch (event.key) {
    case 'Escape':
      event.preventDefault()
      closePdfViewer()
      break
  }
}

// Lifecycle
import { onMounted, onUnmounted } from 'vue'

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  cleanup()
})
</script>

<style scoped>
.pdf-viewer-card {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.pdf-viewer-header {
  background: rgba(0, 0, 0, 0.02);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
  padding: 16px 24px;
}

.v-theme--dark .pdf-viewer-header {
  background: rgba(255, 255, 255, 0.02);
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

.pdf-container {
  flex: 1;
  overflow: auto;
  background: #f5f5f5;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.v-theme--dark .pdf-container {
  background: #1a1a1a;
}

.pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
  border-radius: 4px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.pdf-loading,
.pdf-error {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 40px;
}

.zoom-display {
  min-width: 50px;
  text-align: center;
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
}

/* Toolbar styling */
.pdf-viewer-header .v-btn {
  transition: all 0.2s ease;
}

.pdf-viewer-header .v-btn:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.v-theme--dark .pdf-viewer-header .v-btn:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .pdf-viewer-header {
    padding: 12px 16px;
  }
  
  .pdf-container {
    padding: 10px;
  }
  
  .zoom-display {
    min-width: 45px;
    font-size: 0.875rem;
  }
}

/* Scrollbar styling */
.pdf-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.pdf-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}

.pdf-container::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.pdf-container::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.5);
}
</style>