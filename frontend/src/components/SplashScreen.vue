<template>
  <v-overlay
    v-model="show"
    persistent
    class="splash-overlay"
  >
    <div class="splash-container">
      <!-- Animated logo -->
      <div class="logo-wrapper mb-6">
        <v-icon
          size="80"
          class="splash-icon"
          color="white"
        >
          mdi-console
        </v-icon>
      </div>
      
      <!-- Title with gradient -->
      <h1 class="text-h3 font-weight-bold mb-2 splash-title">
        Console RAG
      </h1>
      
      <p class="text-h6 text-medium-emphasis mb-6 splash-subtitle">
        Retrieval-Augmented Generation System
      </p>
      
      <!-- Loading indicator -->
      <v-progress-linear
        indeterminate
        color="white"
        class="mb-4 splash-progress"
        height="3"
        rounded
      />
      
      <p class="text-body-2 text-medium-emphasis loading-text">
        {{ loadingText }}
      </p>
    </div>
  </v-overlay>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const _emit = defineEmits(['update:modelValue'])

const show = ref(props.modelValue)
const loadingText = ref('Initializing...')

const loadingMessages = [
  'Initializing...',
  'Loading resources...',
  'Preparing workspace...',
  'Almost ready...'
]

let messageIndex = 0
let messageInterval = null

watch(() => props.modelValue, (newValue) => {
  show.value = newValue
  if (newValue) {
    startLoadingAnimation()
  } else {
    stopLoadingAnimation()
  }
})

const startLoadingAnimation = () => {
  messageIndex = 0
  loadingText.value = loadingMessages[0]
  
  messageInterval = setInterval(() => {
    messageIndex = (messageIndex + 1) % loadingMessages.length
    loadingText.value = loadingMessages[messageIndex]
  }, 800)
}

const stopLoadingAnimation = () => {
  if (messageInterval) {
    clearInterval(messageInterval)
    messageInterval = null
  }
}

onMounted(() => {
  if (show.value) {
    startLoadingAnimation()
  }
})
</script>

<style scoped>
.splash-overlay {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.splash-overlay :deep(.v-overlay__content) {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.v-theme--dark .splash-overlay {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.splash-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
  min-height: 100vh;
  width: 100%;
}

/* Logo animation */
.logo-wrapper {
  position: relative;
  display: inline-block;
}

.splash-icon {
  animation: pulse-scale 2s ease-in-out infinite;
}

@keyframes pulse-scale {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Title styling */
.splash-title {
  color: white !important;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.splash-subtitle {
  color: rgba(255, 255, 255, 0.9) !important;
}

/* Loading text animation */
.loading-text {
  color: rgba(255, 255, 255, 0.8) !important;
  animation: fade-in-out 0.8s ease-in-out;
}

@keyframes fade-in-out {
  0% {
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 1;
  }
}

/* Progress bar styling */
.splash-progress {
  max-width: 300px;
  width: 100%;
}

.splash-progress :deep(.v-progress-linear__background) {
  background-color: rgba(255, 255, 255, 0.2) !important;
}
</style>