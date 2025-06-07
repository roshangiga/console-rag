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
            <!-- Recent Documents - Now in Sidebar -->
            <v-card
              class="sidebar-card mb-4"
              elevation="0"
            >
              <v-card-title class="d-flex align-center justify-space-between">
                <span>Recent Documents</span>
                <div
                  v-if="!documentsStore.loading"
                  class="text-caption text-medium-emphasis"
                >
                  {{ recentDocuments.length }} of {{ sortedDocuments.length }}
                </div>
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
                <v-list v-else-if="recentDocuments.length > 0" density="compact">
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
                        {{ getDocumentIcon(document.type) }}
                      </v-icon>
                    </template>

                    <v-list-item-title class="text-body-2">{{ document.name }}</v-list-item-title>
                    <v-list-item-subtitle class="text-caption">
                      {{ getFormattedPath(document) }}
                      <v-chip
                        size="x-small"
                        variant="outlined"
                        color="primary"
                        class="ml-1"
                      >
                        V{{ document.version }}
                      </v-chip>
                    </v-list-item-subtitle>

                    <template #append>
                      <v-chip
                        :color="getStatusColor(document.status)"
                        size="x-small"
                        variant="dot"
                      />
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

            <!-- Quick Actions -->
            <v-card
              class="sidebar-card"
              elevation="0"
            >
              <v-card-title>Quick Actions</v-card-title>
              <v-card-text>
                <v-btn
                  color="primary"
                  variant="flat"
                  block
                  class="mb-3 action-btn"
                  to="/documents"
                  elevation="0"
                >
                  <v-icon left>
                    mdi-file-plus
                  </v-icon>
                  Upload Document
                </v-btn>

                <v-btn
                  color="secondary"
                  variant="tonal"
                  block
                  class="action-btn"
                  to="/documents"
                  elevation="0"
                >
                  <v-icon left>
                    mdi-magnify
                  </v-icon>
                  Search Documents
                </v-btn>
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
const itemsPerPage = 3

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

const goToPage = (page) => {
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

const getDocumentIcon = (type) => {
  switch (type) {
    case 'QA': return 'mdi-help-circle'
    case 'Image': return 'mdi-image'
    case 'Process_Chart': return 'mdi-chart-box'
    case 'Presentation': return 'mdi-presentation'
    case 'General_Doc': return 'mdi-file-document'
    default: return 'mdi-file'
  }
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
</style>