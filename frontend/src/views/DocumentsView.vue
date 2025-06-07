<template>
  <v-container fluid>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4 font-weight-bold">
        Documents
      </h1>
      <v-btn
        color="primary"
        @click="showUploadDialog = true"
      >
        <v-icon left>
          mdi-upload
        </v-icon>
        Upload Document
      </v-btn>
    </div>

    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-row>
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model="searchQuery"
                  label="Search documents..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  dense
                  @input="handleSearch"
                />
              </v-col>
              <v-col
                cols="12"
                md="3"
              >
                <v-select
                  v-model="typeFilter"
                  :items="documentTypes"
                  label="Type"
                  variant="outlined"
                  dense
                  clearable
                  @update:model-value="handleFilter"
                />
              </v-col>
              <v-col
                cols="12"
                md="3"
              >
                <v-select
                  v-model="statusFilter"
                  :items="statusOptions"
                  label="Status"
                  variant="outlined"
                  dense
                  clearable
                  @update:model-value="handleFilter"
                />
              </v-col>
              <v-col
                cols="12"
                md="2"
              >
                <v-btn
                  variant="outlined"
                  block
                  @click="clearFilters"
                >
                  Clear Filters
                </v-btn>
              </v-col>
            </v-row>
          </v-card-title>

          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="documentsStore.documents"
              :loading="documentsStore.loading"
              class="elevation-1"
              item-value="id"
              show-expand
              :expanded="expanded"
            >
              <!-- Name column with icons and path -->
              <template #item.name="{ item }">
                <div class="d-flex align-center">
                  <v-icon
                    :color="getTypeColor(item.type)"
                    class="mr-3"
                  >
                    {{ getDocumentIcon(item.type) }}
                  </v-icon>
                  <div>
                    <div class="font-weight-medium">
                      {{ item.name }}
                    </div>
                    <div class="d-flex align-center mt-1">
                      <span class="text-caption text-medium-emphasis">{{ getFormattedPath(item) }}</span>
                      <v-chip
                        size="x-small"
                        variant="outlined"
                        color="primary"
                        class="ml-2"
                      >
                        V {{ item.version }}
                      </v-chip>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Type column -->
              <template #item.type="{ item }">
                <v-chip
                  :color="getTypeColor(item.type)"
                  size="small"
                  variant="outlined"
                >
                  {{ formatType(item.type) }}
                </v-chip>
              </template>

              <!-- Size column -->
              <template #item.size="{ item }">
                <span>{{ formatFileSize(item.file_size) }}</span>
              </template>

              <!-- Owner column -->
              <template #item.owner="{ item }">
                <div class="d-flex align-center">
                  <v-avatar
                    size="24"
                    class="mr-2"
                  >
                    <v-icon size="16">
                      mdi-account
                    </v-icon>
                  </v-avatar>
                  <span>{{ item.creator?.name || 'Unknown' }}</span>
                </div>
              </template>

              <!-- Date modified -->
              <template #item.modified="{ item }">
                <div>
                  <div>{{ formatDate(item.updated_at || item.created_at) }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ formatTime(item.updated_at || item.created_at) }}
                  </div>
                </div>
              </template>

              <!-- Status column -->
              <template #item.status="{ item }">
                <v-chip
                  :color="getStatusColor(item.status)"
                  size="small"
                  variant="outlined"
                >
                  {{ item.status }}
                </v-chip>
              </template>

              <!-- Version column -->
              <template #item.version="{ item }">
                <span class="font-weight-medium">{{ item.version }}</span>
              </template>

              <!-- Actions column -->
              <template #item.actions="{ item }">
                <v-menu>
                  <template #activator="{ props }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      v-bind="props"
                    />
                  </template>
                  <v-list>
                    <v-list-item @click="downloadDocument(item)">
                      <v-list-item-title>
                        <v-icon left>
                          mdi-download
                        </v-icon>
                        Download
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="processDocument(item)">
                      <v-list-item-title>
                        <v-icon left>
                          mdi-cog
                        </v-icon>
                        Process
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="editDocument(item)">
                      <v-list-item-title>
                        <v-icon left>
                          mdi-pencil
                        </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      class="text-error"
                      @click="deleteDocument(item)"
                    >
                      <v-list-item-title>
                        <v-icon left>
                          mdi-delete
                        </v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>

              <!-- Expanded details -->
              <template #expanded-row="{ item }">
                <tr>
                  <td colspan="8">
                    <v-card
                      flat
                      class="ma-2"
                    >
                      <v-card-text>
                        <v-row>
                          <v-col cols="6">
                            <strong>Created:</strong> {{ formatDateTime(item.created_at) }}
                          </v-col>
                          <v-col cols="6">
                            <strong>Last Modified:</strong> {{ formatDateTime(item.updated_at) }}
                          </v-col>
                          <v-col
                            v-if="item.purpose"
                            cols="6"
                          >
                            <strong>Purpose:</strong> {{ item.purpose }}
                          </v-col>
                          <v-col
                            v-if="item.directory"
                            cols="6"
                          >
                            <strong>Directory:</strong> {{ item.directory.name }}
                          </v-col>
                          <v-col
                            v-if="item.tags && item.tags.length"
                            cols="12"
                          >
                            <strong>Tags:</strong>
                            <v-chip
                              v-for="tag in item.tags"
                              :key="tag.id"
                              size="small"
                              class="ml-2"
                              variant="outlined"
                            >
                              {{ tag.tag_name }}
                            </v-chip>
                          </v-col>
                          <v-col
                            v-if="item.file_path"
                            cols="12"
                          >
                            <strong>Path:</strong> {{ getFormattedPath(item) }}
                          </v-col>
                          <v-col
                            v-if="item.metadata"
                            cols="12"
                          >
                            <strong>Metadata:</strong>
                            <pre class="text-caption">{{ JSON.stringify(item.metadata, null, 2) }}</pre>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </td>
                </tr>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Upload Dialog Placeholder -->
    <v-dialog
      v-model="showUploadDialog"
      max-width="600"
    >
      <v-card>
        <v-card-title>Upload Document</v-card-title>
        <v-card-text>
          <p>Document upload functionality will be implemented in the next phase.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showUploadDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useDocumentsStore } from '@/stores/documents'

const documentsStore = useDocumentsStore()
const showSnackbar = inject('showSnackbar')

const showUploadDialog = ref(false)
const searchQuery = ref('')
const typeFilter = ref(null)
const statusFilter = ref(null)

const expanded = ref([])

const headers = [
  { title: 'Name', key: 'name', sortable: true, width: '25%' },
  { title: 'Type', key: 'type', sortable: true, width: '12%' },
  { title: 'Size', key: 'size', sortable: false, width: '10%' },
  { title: 'Owner', key: 'owner', sortable: true, width: '15%' },
  { title: 'Modified', key: 'modified', sortable: true, width: '15%' },
  { title: 'Status', key: 'status', sortable: true, width: '10%' },
  { title: 'Version', key: 'version', sortable: true, width: '8%' },
  { title: '', key: 'actions', sortable: false, width: '5%' }
]

const documentTypes = [
  { title: 'QA Documents', value: 'QA' },
  { title: 'Images', value: 'Image' },
  { title: 'Process Charts', value: 'Process_Chart' },
  { title: 'Presentations', value: 'Presentation' },
  { title: 'General Documents', value: 'General_Doc' }
]

const statusOptions = [
  { title: 'Success', value: 'SUCCESS' },
  { title: 'Failed', value: 'FAILED' },
  { title: 'Processing', value: 'UPDATING' }
]

const getStatusColor = (status) => {
  switch (status) {
    case 'SUCCESS': return 'success'
    case 'FAILED': return 'error'
    case 'UPDATING': return 'warning'
    default: return 'grey'
  }
}

const getTypeColor = (type) => {
  switch (type) {
    case 'QA': return 'blue'
    case 'Image': return 'green'
    case 'Process_Chart': return 'orange'
    case 'Presentation': return 'purple'
    case 'General_Doc': return 'grey'
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

const formatType = (type) => {
  return type.replace(/_/g, ' ')
}

// Utility functions matching FileBrowser
const formatFileSize = (bytes) => {
  if (!bytes) return '-'
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleTimeString()
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString()
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

// New event handler
const downloadDocument = (document) => {
  showSnackbar(`Downloading ${document.name}...`, 'info')
  // In a real app, you would initiate the actual download here
}

const handleSearch = () => {
  // Implement search functionality
  fetchDocuments()
}

const handleFilter = () => {
  fetchDocuments()
}

const clearFilters = () => {
  searchQuery.value = ''
  typeFilter.value = null
  statusFilter.value = null
  fetchDocuments()
}

const fetchDocuments = async () => {
  const filters = {}
  if (searchQuery.value) filters.search = searchQuery.value
  if (typeFilter.value) filters.type = typeFilter.value
  if (statusFilter.value) filters.status = statusFilter.value

  try {
    await documentsStore.fetchDocuments(filters)
  } catch (_error) {
    showSnackbar('Failed to fetch documents', 'error')
  }
}

const processDocument = async (document) => {
  try {
    await documentsStore.processDocument(document.id)
    showSnackbar(`Processing initiated for ${document.name}`, 'info')
  } catch (_error) {
    showSnackbar('Failed to process document', 'error')
  }
}

const editDocument = (_document) => {
  showSnackbar('Edit functionality coming soon', 'info')
}

const deleteDocument = async (document) => {
  if (confirm(`Are you sure you want to delete "${document.name}"?`)) {
    try {
      await documentsStore.deleteDocument(document.id)
      showSnackbar('Document deleted successfully', 'success')
    } catch (_error) {
      showSnackbar('Failed to delete document', 'error')
    }
  }
}

onMounted(() => {
  fetchDocuments()
})
</script>

<style scoped>
.v-data-table {
  background: transparent;
}

.v-list-item-title .v-icon {
  margin-right: 8px;
}

pre {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 8px;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
}
</style>