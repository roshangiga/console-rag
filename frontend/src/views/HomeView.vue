<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <v-row>
          <v-col
            cols="12"
            md="6"
            lg="3"
          >
            <v-card
              class="stat-card"
              elevation="0"
            >
              <v-card-text class="pa-5">
                <div class="d-flex align-center">
                  <div class="stat-icon-wrapper primary">
                    <v-icon
                      color="white"
                      size="28"
                    >
                      mdi-folder-multiple
                    </v-icon>
                  </div>
                  <div class="ml-4">
                    <div class="text-h4 font-weight-bold">
                      {{ directoriesStore.directories.length }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      Directories
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col
            cols="12"
            md="6"
            lg="3"
          >
            <v-card
              class="stat-card"
              elevation="0"
            >
              <v-card-text class="pa-5">
                <div class="d-flex align-center">
                  <div class="stat-icon-wrapper success">
                    <v-icon
                      color="white"
                      size="28"
                    >
                      mdi-file-document
                    </v-icon>
                  </div>
                  <div class="ml-4">
                    <div class="text-h4 font-weight-bold">
                      {{ documentsStore.documents.length }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      Documents
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col
            cols="12"
            md="6"
            lg="3"
          >
            <v-card
              class="stat-card"
              elevation="0"
            >
              <v-card-text class="pa-5">
                <div class="d-flex align-center">
                  <div class="stat-icon-wrapper warning">
                    <v-icon
                      color="white"
                      size="28"
                    >
                      mdi-clock-outline
                    </v-icon>
                  </div>
                  <div class="ml-4">
                    <div class="text-h4 font-weight-bold">
                      {{ processingCount }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      Processing
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col
            cols="12"
            md="6"
            lg="3"
          >
            <v-card
              class="stat-card"
              elevation="0"
            >
              <v-card-text class="pa-5">
                <div class="d-flex align-center">
                  <div class="stat-icon-wrapper error">
                    <v-icon
                      color="white"
                      size="28"
                    >
                      mdi-alert-circle
                    </v-icon>
                  </div>
                  <div class="ml-4">
                    <div class="text-h4 font-weight-bold">
                      {{ failedCount }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      Failed
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row class="mt-6">
          <v-col
            cols="12"
            lg="8"
          >
            <!-- File Browser Section - Now Main Content -->
            <v-card
              class="content-card"
              elevation="0"
            >
              <v-card-title class="d-flex align-center justify-space-between">
                <span>File Browser</span>
                <v-btn
                  color="primary"
                  size="small"
                  to="/browse"
                  variant="outlined"
                >
                  <v-icon left>
                    mdi-open-in-new
                  </v-icon>
                  Open Full Browser
                </v-btn>
              </v-card-title>
              <v-card-text class="pa-0">
                <FileBrowser
                  @upload="handleUpload"
                  @edit="handleEdit"
                  @delete="handleDelete"
                  @download="handleDownload"
                  @create-folder="handleCreateFolder"
                />
              </v-card-text>
            </v-card>
          </v-col>

          <v-col
            cols="12"
            lg="4"
          >
            <!-- Upload Document Button -->
            <v-btn
              color="primary"
              variant="outlined"
              block
              size="default"
              class="mb-4 upload-btn"
              @click="handleUploadClick"
            >
              <v-icon start>
                mdi-upload
              </v-icon>
              Upload Document
            </v-btn>

            <!-- Recent Documents - Now in Sidebar -->
            <v-card
              class="sidebar-card mb-4"
              elevation="0"
            >
              <v-card-title class="d-flex align-center justify-space-between">
                <div class="d-flex flex-column">
                  <span>Recent Documents</span>
                  <div
                    v-if="!documentsStore.loading"
                    class="text-caption text-medium-emphasis"
                  >
                    {{ recentDocuments.length }} of {{ sortedDocuments.length }}
                  </div>
                </div>
                <v-btn
                  color="primary"
                  size="small"
                  variant="outlined"
                  to="/documents"
                >
                  <v-icon start>
                    mdi-folder-open
                  </v-icon>
                  Manage Files
                </v-btn>
              </v-card-title>
              <v-card-text>
                <!-- Loading state -->
                <div
                  v-if="documentsStore.loading"
                  class="text-center py-4"
                >
                  <v-progress-circular
                    indeterminate
                    color="primary"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis">
                    Loading...
                  </div>
                </div>
                
                <!-- Documents list -->
                <v-list
                  v-else-if="recentDocuments.length > 0"
                  density="compact"
                >
                  <v-list-item
                    v-for="document in recentDocuments"
                    :key="document.id"
                    :to="`/documents?id=${document.id}`"
                    class="px-0"
                  >
                    <template #prepend>
                      <v-icon 
                        :color="getStatusColor(document.status)"
                        size="small"
                      >
                        {{ getDocumentIcon(document.type, getActualFileName(document)) }}
                      </v-icon>
                    </template>

                    <v-list-item-title class="text-body-2">
                      {{ getActualFileName(document) }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-caption">
                      {{ getFormattedPath(document) }}
                    </v-list-item-subtitle>

                    <template #append>
                      <div class="d-flex align-center">
                        <span class="text-caption text-medium-emphasis mr-2">
                          {{ getTimeAgo(document.updated_at || document.created_at) }}
                        </span>
                        <v-chip
                          :color="getStatusColor(document.status)"
                          size="x-small"
                          variant="dot"
                        />
                      </div>
                    </template>
                  </v-list-item>
                </v-list>
                
                <!-- Empty state -->
                <div
                  v-else
                  class="text-center py-4"
                >
                  <v-icon
                    size="32"
                    color="grey-lighten-1"
                    class="mb-1"
                  >
                    mdi-file-document-outline
                  </v-icon>
                  <div class="text-body-2 text-medium-emphasis">
                    No documents
                  </div>
                </div>

                <!-- Pagination for sidebar -->
                <div
                  v-if="!documentsStore.loading && totalPages > 1"
                  class="d-flex justify-center align-center mt-2"
                >
                  <v-btn
                    icon="mdi-chevron-left"
                    variant="text"
                    size="x-small"
                    :disabled="currentPage === 1"
                    @click="prevPage"
                  />
                  
                  <span class="text-caption mx-2">
                    {{ currentPage }}/{{ totalPages }}
                  </span>
                  
                  <v-btn
                    icon="mdi-chevron-right"
                    variant="text"
                    size="x-small"
                    :disabled="currentPage === totalPages"
                    @click="nextPage"
                  />
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useDirectoriesStore } from '@/stores/directories'
import { useDocumentsStore } from '@/stores/documents'
import FileBrowser from '@/components/FileBrowser.vue'

const router = useRouter()
const directoriesStore = useDirectoriesStore()
const documentsStore = useDocumentsStore()
const showSnackbar = inject('showSnackbar')

// Pagination for recent documents
const currentPage = ref(1)
const itemsPerPage = 5

const sortedDocuments = computed(() => {
  return documentsStore.documents
    .slice()
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const totalPages = computed(() => {
  return Math.ceil(sortedDocuments.value.length / itemsPerPage)
})

const recentDocuments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return sortedDocuments.value.slice(start, end)
})

const _goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const processingCount = computed(() => {
  return documentsStore.documents.filter(doc => doc.status === 'UPDATING').length
})

const failedCount = computed(() => {
  return documentsStore.documents.filter(doc => doc.status === 'FAILED').length
})

const getStatusColor = (status) => {
  switch (status) {
    case 'SUCCESS': return 'success'
    case 'FAILED': return 'error'
    case 'UPDATING': return 'warning'
    default: return 'grey'
  }
}

const getDocumentIcon = (type, filename = '') => {
  // First check file extension for more specific icons
  if (filename) {
    const extension = getFileExtension(filename).toLowerCase()
    switch (extension) {
      case 'pdf': return 'mdi-file-document'
      case 'doc':
      case 'docx': return 'mdi-file-word-box'
      case 'xls':
      case 'xlsx': return 'mdi-file-excel-box'
      case 'ppt':
      case 'pptx': return 'mdi-file-powerpoint-box'
      case 'txt': return 'mdi-file-document-outline'
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
      case 'bmp':
      case 'svg': return 'mdi-file-image'
      case 'zip':
      case 'rar':
      case '7z': return 'mdi-zip-box'
      case 'mp4':
      case 'avi':
      case 'mov': return 'mdi-file-video'
      case 'mp3':
      case 'wav':
      case 'flac': return 'mdi-file-music'
    }
  }
  
  // Fall back to document type
  switch (type) {
    case 'QA': return 'mdi-help-circle'
    case 'Image': return 'mdi-image'
    case 'Process_Chart': return 'mdi-chart-box'
    case 'Presentation': return 'mdi-presentation'
    case 'General_Doc': return 'mdi-file-document'
    default: return 'mdi-file'
  }
}

const getFileExtension = (filename) => {
  if (!filename) return ''
  const parts = filename.split('.')
  if (parts.length < 2) return ''
  return parts.pop().toLowerCase()
}

const _getExtensionFromType = (type) => {
  switch (type) {
    case 'QA': return 'pdf'
    case 'Image': return 'png'
    case 'Process_Chart': return 'pdf'
    case 'Presentation': return 'pptx'
    case 'General_Doc': return 'docx'
    default: return 'file'
  }
}

const getActualFileName = (item) => {
  // Try to get filename from file_path first
  if (item.file_path) {
    const pathParts = item.file_path.split('/')
    const filename = pathParts[pathParts.length - 1]
    if (filename && filename !== '') {
      return filename
    }
  }
  // Fallback to name field
  return item.name || 'Unknown File'
}

const getDocumentPath = (document) => {
  return document.directory_path || 'Root'
}

const getFormattedPath = (document) => {
  const path = getDocumentPath(document)
  if (path === 'Root') {
    return 'Root'
  }
  // Replace forward slashes with arrow icons
  return path.replace(/\//g, ' ➤ ').replace(/^ ➤ /, '')
}

const getTimeAgo = (dateString) => {
  if (!dateString) return ''
  
  const now = new Date()
  const date = new Date(dateString)
  const diffInMs = now - date
  const diffInMinutes = Math.floor(diffInMs / (1000 * 60))
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60))
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))
  const diffInWeeks = Math.floor(diffInDays / 7)
  const diffInMonths = Math.floor(diffInDays / 30)
  const diffInYears = Math.floor(diffInDays / 365)
  
  if (diffInMinutes < 1) return 'just now'
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  if (diffInHours < 24) return `${diffInHours}h ago`
  if (diffInDays < 7) return `${diffInDays}d ago`
  if (diffInWeeks < 4) return `${diffInWeeks}w ago`
  if (diffInMonths < 12) return `${diffInMonths}mo ago`
  return `${diffInYears}y ago`
}

// FileBrowser event handlers
const handleUpload = () => {
  showSnackbar('Upload functionality - use the full browser for complete features', 'info')
}

const handleEdit = (item) => {
  showSnackbar(`Edit ${item.name} - use the full browser for complete features`, 'info')
}

const handleDelete = (item) => {
  showSnackbar(`Delete ${item.name} - use the full browser for complete features`, 'info')
}

const handleDownload = (item) => {
  if (item.type === 'directory') return
  showSnackbar(`Downloading ${item.name}...`, 'info')
}

const handleCreateFolder = () => {
  // Redirect to full browser with create folder intent
  router.push('/browse?action=create-folder')
}

const handleUploadClick = () => {
  // Navigate to documents page with upload action
  router.push('/documents?action=upload')
}

onMounted(async () => {
  try {
    await Promise.all([
      directoriesStore.fetchDirectories(),
      documentsStore.fetchDocuments()
    ])
  } catch (_error) {
    showSnackbar('Failed to load data', 'error')
  }
})
</script>

<style scoped>
/* Text gradient for header */
.text-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Stat cards styling */
.stat-card {
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  background: rgba(255, 255, 255, 0.95);
}

.v-theme--dark .stat-card {
  background: rgba(30, 30, 30, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.15);
}

.v-theme--dark .stat-card:hover {
  box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.5);
}

/* Icon wrappers for stats */
.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.stat-icon-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 100%);
}

.stat-icon-wrapper.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon-wrapper.success {
  background: linear-gradient(135deg, #48c78e 0%, #36a269 100%);
}

.stat-icon-wrapper.warning {
  background: linear-gradient(135deg, #ffb236 0%, #ff8800 100%);
}

.stat-icon-wrapper.error {
  background: linear-gradient(135deg, #f14668 0%, #cc0f35 100%);
}

/* Content and sidebar cards */
.content-card {
  border: 1px solid rgba(0, 0, 0, 0.05);
  background: rgba(255, 255, 255, 0.98);
  transition: all 0.3s ease;
}

.v-theme--dark .content-card {
  background: rgba(30, 30, 30, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-card {
  border: 1px solid rgba(0, 0, 0, 0.05);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.v-theme--dark .sidebar-card {
  background: rgba(30, 30, 30, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-card:hover {
  box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.1);
}

.v-theme--dark .sidebar-card:hover {
  box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.3);
}

/* Action buttons */
.action-btn {
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px -6px rgba(0, 0, 0, 0.2);
}

/* Recent documents list improvements */
.v-list-item {
  transition: background-color 0.2s ease;
  border-radius: 8px;
  margin-bottom: 2px;
}

.v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.v-theme--dark .v-list-item:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

/* Smooth transitions for all interactive elements */
.v-btn, .v-card, .v-chip {
  transition: all 0.2s ease;
}

/* Enhanced chip styling */
.v-chip {
  font-weight: 500;
}

/* Upload button styling */
.upload-btn {
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0.25px;
  transition: all 0.2s ease;
}

.upload-btn:hover {
  background-color: rgba(25, 118, 210, 0.04);
}

/* Loading state improvements */
.v-progress-circular {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* File extension styling for recent documents */
.file-extension-small {
  font-size: 7px !important;
  line-height: 1.2 !important;
  margin-top: 2px !important;
  font-weight: 500 !important;
  text-transform: uppercase !important;
  color: rgba(0, 0, 0, 0.6) !important;
  background-color: rgba(0, 0, 0, 0.08) !important;
  padding: 1px 2px !important;
  border-radius: 2px !important;
  min-width: 18px !important;
  text-align: center !important;
  display: block !important;
  border: none !important;
}

.v-theme--dark .file-extension-small {
  color: rgba(255, 255, 255, 0.7) !important;
  background-color: rgba(255, 255, 255, 0.12) !important;
}
</style>