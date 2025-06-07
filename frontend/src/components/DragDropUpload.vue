<template>
  <div
    :class="['drag-drop-zone', { 'drag-active': isDragging }]"
    @drop="handleDrop"
    @dragover.prevent
    @dragenter.prevent="handleDragEnter"
    @dragleave.prevent="handleDragLeave"
  >
    <!-- Upload Icon -->
    <div class="upload-icon mb-3">
      <v-icon
        :size="64"
        :color="isDragging ? 'primary' : 'grey-lighten-1'"
        class="upload-icon-animated"
      >
        mdi-cloud-upload-outline
      </v-icon>
    </div>

    <!-- Instructions -->
    <h3 class="text-h6 font-weight-medium mb-2">
      {{ isDragging ? 'Drop files here' : 'Drag & Drop files here' }}
    </h3>
    <p class="text-body-2 text-medium-emphasis mb-4">
      or
    </p>

    <!-- Browse Button -->
    <v-btn
      color="primary"
      variant="outlined"
      @click="$refs.fileInput.click()"
    >
      <v-icon start>
        mdi-folder-open
      </v-icon>
      Browse Files
    </v-btn>

    <!-- Hidden File Input -->
    <input
      ref="fileInput"
      type="file"
      :multiple="multiple"
      :accept="accept"
      style="display: none"
      @change="handleFileSelect"
    >

    <!-- File Type Info -->
    <p class="text-caption text-medium-emphasis mt-4">
      {{ acceptText || 'Supported formats: PDF, DOCX, XLSX, PPTX, Images, TXT' }}
    </p>

    <!-- Selected Files Preview -->
    <v-expand-transition>
      <div
        v-if="selectedFiles.length > 0"
        class="file-preview-container mt-6"
      >
        <v-divider class="mb-4" />
        <h4 class="text-subtitle-2 mb-3">
          Selected Files ({{ selectedFiles.length }})
        </h4>
        <v-list
          density="compact"
          class="file-list"
        >
          <v-list-item
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="file-item"
          >
            <template #prepend>
              <v-icon
                :color="getFileIconColor(file)"
                size="small"
              >
                {{ getFileIcon(file) }}
              </v-icon>
            </template>

            <v-list-item-title class="text-body-2">
              {{ file.name }}
            </v-list-item-title>
            <v-list-item-subtitle class="text-caption">
              {{ formatFileSize(file.size) }}
            </v-list-item-subtitle>

            <template #append>
              <v-btn
                icon="mdi-close"
                size="x-small"
                variant="text"
                @click="removeFile(index)"
              />
            </template>
          </v-list-item>
        </v-list>
      </div>
    </v-expand-transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  multiple: {
    type: Boolean,
    default: true
  },
  accept: {
    type: String,
    default: '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt'
  },
  acceptText: {
    type: String,
    default: ''
  },
  maxSize: {
    type: Number,
    default: 10 * 1024 * 1024 // 10MB
  }
})

const emit = defineEmits(['files-selected', 'error'])

const isDragging = ref(false)
const selectedFiles = ref([])
const dragCounter = ref(0)

const handleDragEnter = () => {
  dragCounter.value++
  isDragging.value = true
}

const handleDragLeave = () => {
  dragCounter.value--
  if (dragCounter.value === 0) {
    isDragging.value = false
  }
}

const handleDrop = (e) => {
  e.preventDefault()
  dragCounter.value = 0
  isDragging.value = false
  
  const files = Array.from(e.dataTransfer.files)
  processFiles(files)
}

const handleFileSelect = (e) => {
  const files = Array.from(e.target.files)
  processFiles(files)
}

const processFiles = (files) => {
  const validFiles = files.filter(file => {
    // Check file size
    if (file.size > props.maxSize) {
      emit('error', `File "${file.name}" exceeds maximum size of ${formatFileSize(props.maxSize)}`)
      return false
    }
    
    // Check file type if accept is specified
    if (props.accept) {
      const extension = '.' + file.name.split('.').pop().toLowerCase()
      const acceptedTypes = props.accept.split(',').map(type => type.trim().toLowerCase())
      if (!acceptedTypes.includes(extension)) {
        emit('error', `File type "${extension}" is not supported`)
        return false
      }
    }
    
    return true
  })
  
  if (props.multiple) {
    selectedFiles.value = [...selectedFiles.value, ...validFiles]
  } else {
    selectedFiles.value = validFiles.slice(0, 1)
  }
  
  emit('files-selected', selectedFiles.value)
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
  emit('files-selected', selectedFiles.value)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (file) => {
  const extension = file.name.split('.').pop().toLowerCase()
  switch (extension) {
    case 'pdf': return 'mdi-file-pdf-box'
    case 'doc':
    case 'docx': return 'mdi-file-word'
    case 'xls':
    case 'xlsx': return 'mdi-file-excel'
    case 'ppt':
    case 'pptx': return 'mdi-file-powerpoint'
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif': return 'mdi-file-image'
    case 'txt': return 'mdi-file-document-outline'
    default: return 'mdi-file'
  }
}

const getFileIconColor = (file) => {
  const extension = file.name.split('.').pop().toLowerCase()
  switch (extension) {
    case 'pdf': return 'red'
    case 'doc':
    case 'docx': return 'blue'
    case 'xls':
    case 'xlsx': return 'green'
    case 'ppt':
    case 'pptx': return 'orange'
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif': return 'purple'
    case 'txt': return 'teal'
    default: return 'grey'
  }
}

// Expose methods for parent component
defineExpose({
  clearFiles: () => {
    selectedFiles.value = []
  }
})
</script>

<style scoped>
.drag-drop-zone {
  border: 2px dashed rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  padding: 32px 24px;
  text-align: center;
  transition: all 0.3s ease;
  background-color: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}

.v-theme--dark .drag-drop-zone {
  border-color: rgba(255, 255, 255, 0.2);
  background-color: rgba(255, 255, 255, 0.02);
}

.drag-drop-zone:hover {
  border-color: rgba(33, 150, 243, 0.5);
  background-color: rgba(33, 150, 243, 0.05);
}

.drag-drop-zone.drag-active {
  border-color: rgb(33, 150, 243);
  background-color: rgba(33, 150, 243, 0.1);
  transform: scale(1.02);
}

.upload-icon {
  display: inline-block;
}

.upload-icon-animated {
  animation: bounce 2s ease-in-out infinite;
}

.drag-active .upload-icon-animated {
  animation: pulse 1s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.file-preview-container {
  text-align: left;
}

.file-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
}

.v-theme--dark .file-list {
  border-color: rgba(255, 255, 255, 0.12);
}

.file-item {
  transition: background-color 0.2s ease;
}

.file-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.v-theme--dark .file-item:hover {
  background-color: rgba(255, 255, 255, 0.04);
}
</style>