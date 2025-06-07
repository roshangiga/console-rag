<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold mb-6">
          Welcome to Console
        </h1>
        
        <v-row>
          <v-col
            cols="12"
            md="6"
            lg="3"
          >
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon
                    color="primary"
                    size="40"
                    class="mr-4"
                  >
                    mdi-folder-multiple
                  </v-icon>
                  <div>
                    <div class="text-h6">
                      {{ directoriesStore.directories.length }}
                    </div>
                    <div class="text-subtitle-2">
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
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon
                    color="success"
                    size="40"
                    class="mr-4"
                  >
                    mdi-file-document
                  </v-icon>
                  <div>
                    <div class="text-h6">
                      {{ documentsStore.documents.length }}
                    </div>
                    <div class="text-subtitle-2">
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
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon
                    color="warning"
                    size="40"
                    class="mr-4"
                  >
                    mdi-clock-outline
                  </v-icon>
                  <div>
                    <div class="text-h6">
                      {{ processingCount }}
                    </div>
                    <div class="text-subtitle-2">
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
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon
                    color="error"
                    size="40"
                    class="mr-4"
                  >
                    mdi-alert-circle
                  </v-icon>
                  <div>
                    <div class="text-h6">
                      {{ failedCount }}
                    </div>
                    <div class="text-subtitle-2">
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
            <v-card>
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
            <v-card class="mb-4">
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
            <v-card>
              <v-card-title>Quick Actions</v-card-title>
              <v-card-text>
                <v-btn
                  color="primary"
                  variant="outlined"
                  block
                  class="mb-3"
                  to="/documents"
                >
                  <v-icon left>
                    mdi-file-plus
                  </v-icon>
                  Upload Document
                </v-btn>

                <v-btn
                  color="accent"
                  variant="outlined"
                  block
                  to="/documents"
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