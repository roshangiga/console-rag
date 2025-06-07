<template>
  <v-container fluid>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold mb-1">
          Documents
        </h1>
      </div>
      <v-btn
        color="primary"
        variant="outlined"
        size="default"
        class="upload-docs-btn"
        @click="showUploadDialog = true"
      >
        <v-icon start>
          mdi-upload
        </v-icon>
        Upload Document
      </v-btn>
    </div>

    <v-row>
      <v-col cols="12">
        <v-card class="documents-card">
          <!-- Filters Section -->
          <v-card-title class="filters-section">
            <div class="d-flex justify-end mb-4">
              <v-btn
                v-if="hasActiveFilters"
                variant="text"
                size="small"
                color="primary"
                @click="clearFilters"
              >
                <v-icon
                  start
                  size="small"
                >
                  mdi-filter-off
                </v-icon>
                Clear All
              </v-btn>
            </div>
            
            <v-row>
              <v-col
                cols="12"
                md="5"
              >
                <v-text-field
                  v-model="searchQuery"
                  label="Search documents..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
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
                  label="Document Type"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  @update:model-value="handleFilter"
                >
                  <template #prepend-inner>
                    <v-icon size="small">
                      mdi-file-document
                    </v-icon>
                  </template>
                </v-select>
              </v-col>
              <v-col
                cols="12"
                md="4"
              >
                <v-select
                  v-model="statusFilter"
                  :items="statusOptions"
                  label="Status"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  @update:model-value="handleFilter"
                >
                  <template #prepend-inner>
                    <v-icon size="small">
                      mdi-information
                    </v-icon>
                  </template>
                </v-select>
              </v-col>
            </v-row>
          </v-card-title>

          <v-card-text>
            <!-- Debug Info -->
            <div
              v-if="false"
              class="mb-4 pa-4 bg-grey-lighten-4"
            >
              <p>Loading: {{ documentsStore.loading }}</p>
              <p>Documents length: {{ documentsStore.documents.length }}</p>
              <p>Error: {{ documentsStore.error }}</p>
            </div>
            
            <!-- Loading State -->
            <div
              v-if="documentsStore.loading"
              class="text-center py-8"
            >
              <v-progress-circular
                indeterminate
                color="primary"
                size="40"
                class="mb-4"
              />
              <p>Loading documents...</p>
            </div>
            
            <!-- Data Table -->
            <v-data-table
              v-else-if="documentsStore.documents.length > 0"
              :headers="headers"
              :items="documentsStore.documents"
              :loading="documentsStore.loading"
              class="elevation-1 documents-table enhanced-documents-table"
              item-value="id"
              show-expand
              :expanded="expanded"
              :row-props="getRowProps"
              fixed-header
              :height="600"
            >
              <!-- Name column with icons and path -->
              <template #item.name="{ item }">
                <div class="d-flex align-center">
                  <v-icon
                    :color="getTypeColor(item.type)"
                    class="mr-3"
                    size="24"
                  >
                    {{ getDocumentIcon(item.type, getActualFileName(item)) }}
                  </v-icon>
                  <div>
                    <div class="font-weight-medium">
                      {{ getActualFileName(item) }}
                    </div>
                    <div class="d-flex align-center mt-1">
                      <span class="text-caption text-medium-emphasis">{{ getFormattedPath(item) }}</span>
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
                <tr class="expanded-row-container">
                  <td 
                    colspan="8" 
                    class="pa-0"
                  >
                    <v-expand-transition>
                      <div class="expanded-content">
                        <v-card
                          flat
                          class="expanded-card"
                        >
                          <!-- Header with main info -->
                          <v-card-title class="expanded-header pb-2">
                            <div class="d-flex align-center justify-space-between w-100">
                              <div class="d-flex align-center">
                                <v-icon
                                  :color="getTypeColor(item.type)"
                                  size="32"
                                  class="mr-3"
                                >
                                  {{ getDocumentIcon(item.type, getActualFileName(item)) }}
                                </v-icon>
                                <div>
                                  <h3 class="text-h6 mb-1">{{ getActualFileName(item) }}</h3>
                                  <div class="d-flex align-center gap-2">
                                    <v-chip
                                      :color="getTypeColor(item.type)"
                                      size="small"
                                      variant="outlined"
                                    >
                                      {{ formatType(item.type) }}
                                    </v-chip>
                                    <v-chip
                                      :color="getStatusColor(item.status)"
                                      size="small"
                                      variant="outlined"
                                    >
                                      {{ item.status }}
                                    </v-chip>
                                    <v-chip
                                      v-if="item.version"
                                      color="primary"
                                      size="small"
                                      variant="outlined"
                                    >
                                      Version {{ item.version }}
                                    </v-chip>
                                  </div>
                                </div>
                              </div>
                              
                              <!-- Quick actions -->
                              <div class="d-flex align-center gap-2">
                                <v-tooltip text="Quick Preview" location="top">
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-eye"
                                      variant="text"
                                      size="small"
                                      color="primary"
                                      @click="previewDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip text="Download" location="top">
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-download"
                                      variant="text"
                                      size="small"
                                      color="primary"
                                      @click="downloadDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip text="Process" location="top">
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-cog"
                                      variant="text"
                                      size="small"
                                      color="warning"
                                      @click="processDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip text="Edit" location="top">
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-pencil"
                                      variant="text"
                                      size="small"
                                      color="secondary"
                                      @click="editDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip text="Share" location="top">
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-share-variant"
                                      variant="text"
                                      size="small"
                                      color="info"
                                      @click="shareDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                              </div>
                            </div>
                          </v-card-title>
                          
                          <v-divider class="mx-4" />
                          
                          <!-- Content area -->
                          <v-card-text class="expanded-body">
                            <v-row class="align-start">
                              <!-- Left column - Metadata -->
                              <v-col cols="12" md="8">
                                <div class="metadata-grid">
                                  <!-- Document specific info -->
                                  <div class="info-section">
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon size="18" class="mr-1">mdi-file-document</v-icon>
                                      Document Information
                                    </h4>
                                    <div class="info-grid">
                                      <div class="info-item">
                                        <span class="info-label">File Size:</span>
                                        <span class="info-value">{{ formatFileSize(item.file_size) }}</span>
                                      </div>
                                      <div v-if="item.purpose" class="info-item">
                                        <span class="info-label">Purpose:</span>
                                        <span class="info-value">{{ item.purpose }}</span>
                                      </div>
                                      <div v-if="item.directory" class="info-item">
                                        <span class="info-label">Directory:</span>
                                        <span class="info-value">{{ item.directory.name }}</span>
                                      </div>
                                      <div class="info-item">
                                        <span class="info-label">File Extension:</span>
                                        <span class="info-value">{{ getFileExtension(getActualFileName(item)).toUpperCase() || 'N/A' }}</span>
                                      </div>
                                    </div>
                                  </div>
                                  
                                  <!-- Timestamps -->
                                  <div class="info-section">
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon size="18" class="mr-1">mdi-clock</v-icon>
                                      Timeline
                                    </h4>
                                    <div class="info-grid">
                                      <div class="info-item">
                                        <span class="info-label">Created:</span>
                                        <span class="info-value">{{ formatDateTime(item.created_at) }}</span>
                                      </div>
                                      <div class="info-item">
                                        <span class="info-label">Last Modified:</span>
                                        <span class="info-value">{{ formatDateTime(item.updated_at) }}</span>
                                      </div>
                                      <div class="info-item">
                                        <span class="info-label">Owner:</span>
                                        <span class="info-value d-flex align-center">
                                          <v-avatar size="20" class="mr-2">
                                            <v-icon size="12">mdi-account</v-icon>
                                          </v-avatar>
                                          {{ item.creator?.name || 'Unknown' }}
                                        </span>
                                      </div>
                                    </div>
                                  </div>
                                  
                                  <!-- Path and Location -->
                                  <div v-if="item.file_path" class="info-section">
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon size="18" class="mr-1">mdi-map-marker-path</v-icon>
                                      Location
                                    </h4>
                                    <div class="path-display">
                                      <v-chip
                                        variant="outlined"
                                        color="primary"
                                        class="font-mono path-chip"
                                      >
                                        {{ getFormattedPath(item) }}
                                      </v-chip>
                                    </div>
                                  </div>
                                  
                                  <!-- Metadata section -->
                                  <div v-if="item.metadata" class="info-section">
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon size="18" class="mr-1">mdi-code-json</v-icon>
                                      Technical Metadata
                                    </h4>
                                    <div class="metadata-display">
                                      <v-expansion-panels variant="accordion">
                                        <v-expansion-panel>
                                          <v-expansion-panel-title>
                                            <v-icon size="16" class="mr-2">mdi-information-outline</v-icon>
                                            View Technical Details
                                          </v-expansion-panel-title>
                                          <v-expansion-panel-text>
                                            <pre class="metadata-pre">{{ JSON.stringify(item.metadata, null, 2) }}</pre>
                                          </v-expansion-panel-text>
                                        </v-expansion-panel>
                                      </v-expansion-panels>
                                    </div>
                                  </div>
                                </div>
                              </v-col>
                              
                              <!-- Right column - Tags and Actions -->
                              <v-col cols="12" md="4">
                                <!-- Tags section -->
                                <div v-if="item.tags && item.tags.length" class="info-section">
                                  <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                    <v-icon size="18" class="mr-1">mdi-tag-multiple</v-icon>
                                    Tags
                                  </h4>
                                  <div class="tags-container">
                                    <v-chip
                                      v-for="tag in item.tags"
                                      :key="tag.id"
                                      size="small"
                                      class="ma-1"
                                      variant="outlined"
                                      color="primary"
                                    >
                                      <v-icon size="14" class="mr-1">mdi-tag</v-icon>
                                      {{ tag.tag_name }}
                                    </v-chip>
                                  </div>
                                </div>
                                
                                <!-- Action buttons -->
                                <div class="info-section">
                                  <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                    <v-icon size="18" class="mr-1">mdi-cog</v-icon>
                                    Actions
                                  </h4>
                                  <div class="action-buttons">
                                    <v-btn
                                      variant="outlined"
                                      color="primary"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="downloadDocument(item)"
                                    >
                                      <v-icon size="16" class="mr-2">mdi-download</v-icon>
                                      Download
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="warning"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="processDocument(item)"
                                    >
                                      <v-icon size="16" class="mr-2">mdi-cog</v-icon>
                                      Process Document
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="secondary"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="editDocument(item)"
                                    >
                                      <v-icon size="16" class="mr-2">mdi-pencil</v-icon>
                                      Edit Properties
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="info"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="shareDocument(item)"
                                    >
                                      <v-icon size="16" class="mr-2">mdi-share-variant</v-icon>
                                      Share
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="error"
                                      size="small"
                                      class="w-100"
                                      @click="deleteDocument(item)"
                                    >
                                      <v-icon size="16" class="mr-2">mdi-delete</v-icon>
                                      Delete
                                    </v-btn>
                                  </div>
                                </div>
                              </v-col>
                            </v-row>
                          </v-card-text>
                        </v-card>
                      </div>
                    </v-expand-transition>
                  </td>
                </tr>
              </template>
            </v-data-table>
            
            <!-- Empty State -->
            <EmptyState
              v-else
              icon="mdi-file-document-multiple-outline"
              :icon-size="80"
              title="No documents found"
              :description="searchQuery || typeFilter || statusFilter ? 'Try adjusting your filters' : 'Upload your first document to get started'"
              :action-text="searchQuery || typeFilter || statusFilter ? 'Clear Filters' : 'Upload Document'"
              action-icon="mdi-upload"
              @action="searchQuery || typeFilter || statusFilter ? clearFilters() : showUploadDialog = true"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Upload Dialog -->
    <v-dialog
      v-model="showUploadDialog"
      max-width="1200"
    >
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <span>Upload Documents</span>
          <v-btn
            icon="mdi-close"
            variant="text"
            @click="showUploadDialog = false"
          />
        </v-card-title>
        <v-card-text>
          <v-row>
            <!-- Left Column - Drag & Drop -->
            <v-col
              cols="12"
              md="6"
            >
              <DragDropUpload
                ref="uploadComponent"
                @files-selected="handleFilesSelected"
                @error="handleUploadError"
              />
            </v-col>
            
            <!-- Right Column - Document Details -->
            <v-col
              cols="12"
              md="6"
            >
              <v-expand-transition>
                <div v-if="uploadFiles.length > 0">
                  <h4 class="text-subtitle-1 mb-3">
                    Document Details
                  </h4>
                  
                  <DirectoryTreeSelector
                    v-model="uploadForm.directory_id"
                    label="Select Directory"
                    density="compact"
                    class="mb-3 directory-selector-field"
                  />
                  
                  <v-select
                    v-model="uploadForm.type"
                    :items="documentTypes"
                    label="Document Type"
                    variant="outlined"
                    density="compact"
                    class="mb-3"
                  />
                  
                  <v-textarea
                    v-model="uploadForm.purpose"
                    label="Purpose (Optional)"
                    variant="outlined"
                    rows="4"
                    density="compact"
                    class="mb-3"
                  />
                  
                  <v-select
                    v-model="uploadForm.tags"
                    :items="['Internal', 'Enterprise', 'Public']"
                    label="Tags"
                    variant="outlined"
                    multiple
                    chips
                    density="compact"
                  />
                </div>
                <div
                  v-else
                  class="text-center text-medium-emphasis mt-8"
                >
                  <v-icon
                    size="48"
                    color="grey-lighten-1"
                    class="mb-2"
                  >
                    mdi-form-select
                  </v-icon>
                  <p class="text-body-2">
                    Select files to configure document details
                  </p>
                </div>
              </v-expand-transition>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="closeUploadDialog"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :disabled="uploadFiles.length === 0"
            @click="uploadDocuments"
          >
            Upload {{ uploadFiles.length }} File{{ uploadFiles.length !== 1 ? 's' : '' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useDocumentsStore } from '@/stores/documents'
import { useDirectoriesStore } from '@/stores/directories'
import DragDropUpload from '@/components/DragDropUpload.vue'
import EmptyState from '@/components/EmptyState.vue'
import DirectoryTreeSelector from '@/components/DirectoryTreeSelector.vue'

const router = useRouter()
const documentsStore = useDocumentsStore()
const directoriesStore = useDirectoriesStore()
const showSnackbar = inject('showSnackbar')

const showUploadDialog = ref(false)
const searchQuery = ref('')
const typeFilter = ref(null)
const statusFilter = ref(null)
const uploadComponent = ref(null)
const uploadFiles = ref([])
const uploadForm = ref({
  directory_id: null,
  type: 'General_Doc',
  purpose: '',
  tags: []
})

const expanded = ref([])
const selectedItemId = ref(null)
const hoveredItemId = ref(null)
const keyboardSelectedIndex = ref(0)

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return searchQuery.value || typeFilter.value || statusFilter.value
})

const headers = [
  { title: 'Name', key: 'name', sortable: true, width: '25%' },
  { title: 'Version', key: 'version', sortable: true, width: '8%' },
  { title: 'Type', key: 'type', sortable: true, width: '12%' },
  { title: 'Size', key: 'size', sortable: false, width: '10%' },
  { title: 'Owner', key: 'owner', sortable: true, width: '15%' },
  { title: 'Modified', key: 'modified', sortable: true, width: '15%' },
  { title: 'Status', key: 'status', sortable: true, width: '10%' },
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

// New event handler
const downloadDocument = (document) => {
  showSnackbar(`Downloading ${document.name}...`, 'info')
  // In a real app, you would initiate the actual download here
}

const previewDocument = (document) => {
  showSnackbar(`Opening preview for ${document.name}...`, 'info')
  // Implement preview functionality
}

const shareDocument = (document) => {
  showSnackbar(`Sharing ${document.name}...`, 'info')
  // Implement sharing functionality
}

// Keyboard navigation
const handleKeydown = (event) => {
  if (documentsStore.documents.length === 0) return
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      keyboardSelectedIndex.value = Math.min(keyboardSelectedIndex.value + 1, documentsStore.documents.length - 1)
      selectedItemId.value = documentsStore.documents[keyboardSelectedIndex.value].id
      break
    case 'ArrowUp':
      event.preventDefault()
      keyboardSelectedIndex.value = Math.max(keyboardSelectedIndex.value - 1, 0)
      selectedItemId.value = documentsStore.documents[keyboardSelectedIndex.value].id
      break
    case 'Enter':
      event.preventDefault()
      if (documentsStore.documents[keyboardSelectedIndex.value]) {
        downloadDocument(documentsStore.documents[keyboardSelectedIndex.value])
      }
      break
  }
}

// Row styling for table
const getRowProps = ({ item, index }) => {
  return {
    class: [
      'documents-table-row',
      {
        'row-selected': selectedItemId.value === item.id,
        'row-hovered': hoveredItemId.value === item.id,
        'row-keyboard-focus': keyboardSelectedIndex.value === index
      }
    ],
    onMouseenter: () => hoveredItemId.value = item.id,
    onMouseleave: () => hoveredItemId.value = null,
    onClick: () => {
      selectedItemId.value = item.id
      keyboardSelectedIndex.value = index
    }
  }
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

// Upload handlers
const handleFilesSelected = (files) => {
  uploadFiles.value = files
}

const handleUploadError = (error) => {
  showSnackbar(error, 'error')
}

const closeUploadDialog = () => {
  showUploadDialog.value = false
  uploadFiles.value = []
  uploadForm.value = {
    directory_id: null,
    type: 'General_Doc',
    purpose: '',
    tags: []
  }
  if (uploadComponent.value) {
    uploadComponent.value.clearFiles()
  }
}

const uploadDocuments = async () => {
  try {
    // Simulate upload for each file
    for (const file of uploadFiles.value) {
      showSnackbar(`Uploading ${file.name}...`, 'info')
      // In a real app, you would upload the file here
    }
    
    showSnackbar(`Successfully uploaded ${uploadFiles.value.length} files`, 'success')
    closeUploadDialog()
    
    // Refresh documents list
    await fetchDocuments()
  } catch (_error) {
    showSnackbar('Failed to upload documents', 'error')
  }
}

onMounted(async () => {
  await Promise.all([
    fetchDocuments(),
    directoriesStore.fetchDirectories()
  ])
  
  // Add keyboard event listener
  document.addEventListener('keydown', handleKeydown)
  
  // Check if upload action is requested via URL parameter
  const route = router.currentRoute.value
  if (route.query.action === 'upload') {
    showUploadDialog.value = true
    // Clear the query parameter
    router.replace({ query: {} })
  }
})

onUnmounted(() => {
  // Remove keyboard event listener
  document.removeEventListener('keydown', handleKeydown)
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

/* Upload button styling */
.upload-docs-btn {
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0.25px;
  transition: all 0.2s ease;
}

.upload-docs-btn:hover {
  background-color: rgba(25, 118, 210, 0.04);
}

/* Documents card styling */
.documents-card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.v-theme--dark .documents-card {
  border-color: rgba(255, 255, 255, 0.08);
}

/* Filters section styling */
.filters-section {
  background-color: rgba(0, 0, 0, 0.02);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding: 24px !important;
}

.v-theme--dark .filters-section {
  background-color: rgba(255, 255, 255, 0.02);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

/* Input field improvements */
.v-text-field, .v-select {
  transition: all 0.2s ease;
}

.v-field--focused {
  transform: none !important;
}

/* Filter button animations */
.v-btn {
  transition: all 0.2s ease;
}

.v-btn:hover {
  transform: translateY(-1px);
}

/* Data table enhancements */
.documents-table {
  border-radius: 8px;
  overflow: hidden;
}

.documents-table .v-data-table__wrapper {
  border-radius: 8px;
}

.documents-table .v-data-table-header {
  background-color: rgba(0, 0, 0, 0.02);
  backdrop-filter: blur(10px);
  border-bottom: 2px solid rgba(0, 0, 0, 0.08);
}

.v-theme--dark .documents-table .v-data-table-header {
  background-color: rgba(255, 255, 255, 0.02);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.documents-table .v-data-table-header th {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.75rem;
}

.documents-table tbody tr {
  transition: background-color 0.2s ease;
}

.documents-table tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.v-theme--dark .documents-table tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.02);
}

/* Directory selector field styling */
.directory-selector-field {
  cursor: pointer !important;
}

.directory-selector-field * {
  cursor: pointer !important;
}

/* File extension styling */
.file-extension {
  font-size: 8px !important;
  line-height: 1.2 !important;
  margin-top: 4px !important;
  font-weight: 500 !important;
  text-transform: uppercase !important;
  color: rgba(0, 0, 0, 0.6) !important;
  background-color: rgba(0, 0, 0, 0.08) !important;
  padding: 1px 3px !important;
  border-radius: 3px !important;
  min-width: 20px !important;
  text-align: center !important;
  display: block !important;
  border: none !important;
}

.v-theme--dark .file-extension {
  color: rgba(255, 255, 255, 0.7) !important;
  background-color: rgba(255, 255, 255, 0.12) !important;
}

/* Enhanced row highlighting */
.enhanced-documents-table :deep(.documents-table-row) {
  transition: background-color 0.2s ease, border-left 0.2s ease;
  cursor: pointer;
}

.enhanced-documents-table :deep(.documents-table-row:hover),
.enhanced-documents-table :deep(.row-hovered) {
  background-color: rgba(0, 0, 0, 0.04) !important;
}

.v-theme--dark .enhanced-documents-table :deep(.documents-table-row:hover),
.v-theme--dark .enhanced-documents-table :deep(.row-hovered) {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.enhanced-documents-table :deep(.row-selected) {
  background-color: rgba(25, 118, 210, 0.08) !important;
  border-left: 4px solid rgb(25, 118, 210) !important;
}

.v-theme--dark .enhanced-documents-table :deep(.row-selected) {
  background-color: rgba(144, 202, 249, 0.12) !important;
  border-left: 4px solid rgb(144, 202, 249) !important;
}

.enhanced-documents-table :deep(.row-selected:hover) {
  background-color: rgba(25, 118, 210, 0.12) !important;
}

.v-theme--dark .enhanced-documents-table :deep(.row-selected:hover) {
  background-color: rgba(144, 202, 249, 0.16) !important;
}

.enhanced-documents-table :deep(.row-keyboard-focus) {
  outline: 2px solid rgb(25, 118, 210) !important;
  outline-offset: -2px;
}

.v-theme--dark .enhanced-documents-table :deep(.row-keyboard-focus) {
  outline: 2px solid rgb(144, 202, 249) !important;
}

/* Expanded row styling - same as FileBrowser */
.expanded-row-container {
  background: none !important;
}

.expanded-content {
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.04) 0%, rgba(25, 118, 210, 0.08) 100%);
  border-left: 4px solid rgb(25, 118, 210);
  margin: 8px 0;
  animation: expandIn 0.3s ease-out;
  box-shadow: 0 4px 20px rgba(25, 118, 210, 0.15);
}

.v-theme--dark .expanded-content {
  background: linear-gradient(135deg, rgba(144, 202, 249, 0.08) 0%, rgba(144, 202, 249, 0.15) 100%);
  border-left: 4px solid rgb(144, 202, 249);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.expanded-card {
  border-radius: 12px !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.95) !important;
  border: 1px solid rgba(25, 118, 210, 0.1) !important;
}

.v-theme--dark .expanded-card {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important;
  background: rgba(30, 30, 30, 0.95) !important;
}

.expanded-header {
  padding: 16px 24px 8px 24px;
  background: rgba(25, 118, 210, 0.06);
  border-radius: 12px 12px 0 0;
  border-bottom: 1px solid rgba(25, 118, 210, 0.2);
}

.v-theme--dark .expanded-header {
  background: rgba(144, 202, 249, 0.12);
  border-bottom: 1px solid rgba(144, 202, 249, 0.3);
}

.expanded-body {
  padding: 16px 24px 24px 24px;
}

/* Info sections styling */
.info-section {
  margin-bottom: 24px;
}

.info-section:last-child {
  margin-bottom: 0;
}

.info-section h4 {
  display: flex;
  align-items: center;
  font-weight: 600;
  border-bottom: 1px solid rgba(25, 118, 210, 0.3);
  padding-bottom: 8px;
  color: rgba(25, 118, 210, 0.9);
}

.v-theme--dark .info-section h4 {
  border-bottom: 1px solid rgba(144, 202, 249, 0.4);
  color: rgba(220, 235, 250, 0.9);
}

.info-grid {
  display: grid;
  gap: 12px;
  margin-top: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: rgba(25, 118, 210, 0.04);
  border-radius: 8px;
  transition: all 0.2s ease;
  border: 1px solid rgba(25, 118, 210, 0.1);
}

.v-theme--dark .info-item {
  background: rgba(144, 202, 249, 0.08);
  border: 1px solid rgba(144, 202, 249, 0.2);
}

.info-item:hover {
  background: rgba(25, 118, 210, 0.08);
  border-color: rgba(25, 118, 210, 0.25);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(25, 118, 210, 0.15);
}

.v-theme--dark .info-item:hover {
  background: rgba(144, 202, 249, 0.15);
  border-color: rgba(144, 202, 249, 0.4);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.info-label {
  font-weight: 600;
  color: rgba(25, 118, 210, 0.8);
  font-size: 0.875rem;
}

.v-theme--dark .info-label {
  color: rgba(200, 225, 245, 0.85);
  font-weight: 600;
}

.info-value {
  font-family: 'Roboto Mono', monospace;
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.95);
  text-align: right;
  font-weight: 500;
}

.v-theme--dark .info-value {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 500;
}

/* Path display */
.path-display {
  margin-top: 12px;
}

.font-mono {
  font-family: 'Roboto Mono', monospace !important;
  font-size: 0.875rem;
}

.path-chip {
  background-color: rgba(25, 118, 210, 0.12) !important;
  border-color: rgba(25, 118, 210, 0.6) !important;
  font-weight: 600 !important;
  color: rgba(25, 118, 210, 0.9) !important;
}

.v-theme--dark .path-chip {
  background-color: rgba(144, 202, 249, 0.15) !important;
  border-color: rgba(144, 202, 249, 0.8) !important;
  color: rgb(220, 235, 250) !important;
  font-weight: 500 !important;
}

/* Tags container */
.tags-container {
  margin-top: 12px;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

/* Action buttons */
.action-buttons {
  margin-top: 12px;
}

.action-buttons .v-btn {
  justify-content: flex-start;
}

/* Metadata display */
.metadata-display {
  margin-top: 12px;
}

.metadata-pre {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 12px;
  border-radius: 8px;
  max-height: 300px;
  overflow-y: auto;
  font-size: 0.75rem;
  line-height: 1.4;
}

.v-theme--dark .metadata-pre {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Expand animation */
@keyframes expandIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover effects for quick actions */
.expanded-header .v-btn {
  transition: all 0.2s ease;
}

.expanded-header .v-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.v-theme--dark .expanded-header .v-btn:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .expanded-content {
    margin: 4px 0;
  }
  
  .expanded-header {
    padding: 12px 16px 8px 16px;
  }
  
  .expanded-body {
    padding: 12px 16px 16px 16px;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>