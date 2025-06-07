<template>
  <v-container fluid>
    <!-- Header with stats and view controls -->
    <v-row class="mb-4">
      <v-col
        cols="12"
        md="8"
      >
        <div class="d-flex align-center">
          <v-btn
            icon="mdi-arrow-up"
            variant="text"
            size="small"
            :disabled="!canNavigateUp"
            class="mr-2"
            @click="navigateUp"
          />
          <div>
            <h2 class="text-h5 font-weight-bold">
              {{ currentDirectory?.name || 'Root Directory' }}
            </h2>
            <div class="text-body-2 text-medium-emphasis">
              {{ stats.total_count }} items 
              ({{ stats.directory_count }} folders, {{ stats.document_count }} files)
            </div>
          </div>
        </div>
      </v-col>
      <v-col
        cols="12"
        md="4"
        class="text-right"
      >
        <v-btn-toggle
          v-model="viewMode"
          mandatory
          class="mr-2"
        >
          <v-btn
            value="list"
            icon="mdi-view-list"
            size="small"
          />
          <v-btn
            value="grid"
            icon="mdi-view-grid"
            size="small"
          />
        </v-btn-toggle>
        <v-tooltip
          text="Create New Folder"
          location="bottom"
        >
          <template #activator="{ props: activatorProps }">
            <v-btn
              v-bind="activatorProps"
              icon="mdi-folder-plus"
              variant="text"
              size="small"
              color="secondary"
              class="mr-2"
              @click="$emit('createFolder')"
            />
          </template>
        </v-tooltip>
        
        <v-tooltip
          text="Upload Files"
          location="bottom"
        >
          <template #activator="{ props: activatorProps }">
            <v-btn
              v-bind="activatorProps"
              icon="mdi-upload"
              variant="text"
              size="small"
              color="primary"
              @click="$emit('upload')"
            />
          </template>
        </v-tooltip>
      </v-col>
    </v-row>

    <!-- Breadcrumbs -->
    <v-row class="mb-4">
      <v-col cols="12">
        <v-breadcrumbs
          :items="breadcrumbItems"
          class="pa-0"
        >
          <template #item="{ item }">
            <v-breadcrumbs-item
              :disabled="item.disabled"
              :class="{ 'breadcrumb-clickable': !item.disabled }"
              @click="navigateToDirectory(item.value)"
            >
              <v-icon
                v-if="item.value === 0"
                size="small"
                class="mr-1"
              >
                mdi-home
              </v-icon>
              {{ item.title }}
            </v-breadcrumbs-item>
          </template>
          <template #divider>
            <v-icon
              size="small"
              color="grey-lighten-1"
            >
              mdi-chevron-right
            </v-icon>
          </template>
        </v-breadcrumbs>
      </v-col>
    </v-row>

    <!-- Content Area -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <!-- List View -->
          <div v-if="viewMode === 'list'">
            <v-data-table
              :headers="headers"
              :items="allItems"
              :loading="loading"
              class="elevation-1 file-browser-table"
              item-value="id"
              show-expand
              :expanded="expanded"
              :row-props="getRowProps"
            >
              <!-- Name column with icons and version -->
              <template #item.name="{ item }">
                <div class="d-flex align-center">
                  <v-icon
                    :color="getItemColor(item)"
                    class="mr-3"
                  >
                    {{ getItemIcon(item) }}
                  </v-icon>
                  <div>
                    <div 
                      class="font-weight-medium cursor-pointer item-title"
                      @click="handleItemClick(item)"
                    >
                      {{ item.name }}
                    </div>
                    <div
                      v-if="item.type === 'directory'"
                      class="text-caption text-medium-emphasis"
                    >
                      {{ item.total_count }} items
                    </div>
                    <div
                      v-if="item.type !== 'directory'"
                      class="d-flex align-center mt-1"
                    >
                      <span
                        v-if="getFileName(item)"
                        class="text-caption text-medium-emphasis mr-2"
                      >
                        {{ getFileName(item) }}
                      </span>
                      <v-chip
                        v-if="item.version"
                        size="x-small"
                        variant="outlined"
                        color="primary"
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

              <!-- Size/Count column -->
              <template #item.size="{ item }">
                <span v-if="item.type === 'directory'">
                  {{ item.subdirectory_count }} folders, {{ item.document_count }} files
                </span>
                <span v-else>
                  {{ formatFileSize(item.file_size) }}
                </span>
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

              <!-- Status for documents -->
              <template #item.status="{ item }">
                <v-chip
                  v-if="item.type !== 'directory'"
                  :color="getStatusColor(item.status)"
                  size="small"
                  variant="outlined"
                >
                  {{ item.status || 'Unknown' }}
                </v-chip>
                <span v-else>-</span>
              </template>

              <!-- Actions -->
              <template #item.actions="{ item }">
                <v-menu>
                  <template #activator="{ props: activatorProps }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      v-bind="activatorProps"
                    />
                  </template>
                  <v-list>
                    <v-list-item
                      v-if="item.type === 'directory'"
                      @click="navigateToDirectory(item.id)"
                    >
                      <v-list-item-title>
                        <v-icon left>
                          mdi-folder-open
                        </v-icon>
                        Open
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-else
                      @click="downloadDocument(item)"
                    >
                      <v-list-item-title>
                        <v-icon left>
                          mdi-download
                        </v-icon>
                        Download
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="item.type !== 'directory'"
                      @click="moveItem(item)"
                    >
                      <v-list-item-title>
                        <v-icon left>
                          mdi-folder-move
                        </v-icon>
                        Move To
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="editItem(item)">
                      <v-list-item-title>
                        <v-icon left>
                          mdi-pencil
                        </v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      class="text-error"
                      @click="deleteItem(item)"
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
                    colspan="7" 
                    class="pa-0"
                  >
                    <v-expand-transition>
                      <div class="expanded-content">
                        <v-card
                          flat
                          class="expanded-card"
                          :class="{ 'expanded-directory': item.type === 'directory' }"
                        >
                          <!-- Header with main info -->
                          <v-card-title class="expanded-header pb-2">
                            <div class="d-flex align-center justify-space-between w-100">
                              <div class="d-flex align-center">
                                <v-icon
                                  :color="getItemColor(item)"
                                  size="32"
                                  class="mr-3"
                                >
                                  {{ getItemIcon(item) }}
                                </v-icon>
                                <div>
                                  <h3 class="text-h6 mb-1">
                                    {{ item.name }}
                                  </h3>
                                  <div class="d-flex align-center gap-2">
                                    <v-chip
                                      :color="getTypeColor(item.type)"
                                      size="small"
                                      variant="outlined"
                                    >
                                      {{ formatType(item.type) }}
                                    </v-chip>
                                    <v-chip
                                      v-if="item.status && item.type !== 'directory'"
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
                                <v-tooltip
                                  text="Quick Preview"
                                  location="top"
                                >
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-eye"
                                      variant="text"
                                      size="small"
                                      color="primary"
                                      @click="previewItem(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip 
                                  :text="item.type === 'directory' ? 'Open Folder' : 'Download'"
                                  location="top"
                                >
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      :icon="item.type === 'directory' ? 'mdi-folder-open' : 'mdi-download'"
                                      variant="text"
                                      size="small"
                                      color="primary"
                                      @click="item.type === 'directory' ? navigateToDirectory(item.originalId) : downloadDocument(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip
                                  text="Edit"
                                  location="top"
                                >
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-pencil"
                                      variant="text"
                                      size="small"
                                      color="secondary"
                                      @click="editItem(item)"
                                    />
                                  </template>
                                </v-tooltip>
                                
                                <v-tooltip
                                  text="Share"
                                  location="top"
                                >
                                  <template #activator="{ props: tooltipProps }">
                                    <v-btn
                                      v-bind="tooltipProps"
                                      icon="mdi-share-variant"
                                      variant="text"
                                      size="small"
                                      color="info"
                                      @click="shareItem(item)"
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
                              <v-col
                                cols="12"
                                md="8"
                              >
                                <div class="metadata-grid">
                                  <!-- File/Directory specific info -->
                                  <div
                                    v-if="item.type === 'directory'"
                                    class="info-section"
                                  >
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon
                                        size="18"
                                        class="mr-1"
                                      >
                                        mdi-folder-information
                                      </v-icon>
                                      Directory Information
                                    </h4>
                                    <div class="info-grid">
                                      <div class="info-item">
                                        <span class="info-label">Total Items:</span>
                                        <span class="info-value">{{ item.total_count || 0 }}</span>
                                      </div>
                                      <div class="info-item">
                                        <span class="info-label">Subdirectories:</span>
                                        <span class="info-value">{{ item.subdirectory_count || 0 }}</span>
                                      </div>
                                      <div class="info-item">
                                        <span class="info-label">Documents:</span>
                                        <span class="info-value">{{ item.document_count || 0 }}</span>
                                      </div>
                                    </div>
                                  </div>
                                  
                                  <div
                                    v-else
                                    class="info-section"
                                  >
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon
                                        size="18"
                                        class="mr-1"
                                      >
                                        mdi-file-document
                                      </v-icon>
                                      Document Information
                                    </h4>
                                    <div class="info-grid">
                                      <div class="info-item">
                                        <span class="info-label">File Size:</span>
                                        <span class="info-value">{{ formatFileSize(item.file_size) }}</span>
                                      </div>
                                      <div
                                        v-if="item.purpose"
                                        class="info-item"
                                      >
                                        <span class="info-label">Purpose:</span>
                                        <span class="info-value">{{ item.purpose }}</span>
                                      </div>
                                      <div
                                        v-if="getFileName(item)"
                                        class="info-item"
                                      >
                                        <span class="info-label">Filename:</span>
                                        <span class="info-value">{{ getFileName(item) }}</span>
                                      </div>
                                    </div>
                                  </div>
                                  
                                  <!-- Timestamps -->
                                  <div class="info-section">
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon
                                        size="18"
                                        class="mr-1"
                                      >
                                        mdi-clock
                                      </v-icon>
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
                                          <v-avatar
                                            size="20"
                                            class="mr-2"
                                          >
                                            <v-icon size="12">mdi-account</v-icon>
                                          </v-avatar>
                                          {{ item.creator?.name || 'Unknown' }}
                                        </span>
                                      </div>
                                    </div>
                                  </div>
                                  
                                  <!-- Path and Location -->
                                  <div
                                    v-if="item.file_path"
                                    class="info-section"
                                  >
                                    <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                      <v-icon
                                        size="18"
                                        class="mr-1"
                                      >
                                        mdi-map-marker-path
                                      </v-icon>
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
                                </div>
                              </v-col>
                              
                              <!-- Right column - Tags and Actions -->
                              <v-col
                                cols="12"
                                md="4"
                              >
                                <!-- Tags section -->
                                <div
                                  v-if="item.tags && item.tags.length"
                                  class="info-section"
                                >
                                  <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                    <v-icon
                                      size="18"
                                      class="mr-1"
                                    >
                                      mdi-tag-multiple
                                    </v-icon>
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
                                      <v-icon
                                        size="14"
                                        class="mr-1"
                                      >
                                        mdi-tag
                                      </v-icon>
                                      {{ tag.tag_name }}
                                    </v-chip>
                                  </div>
                                </div>
                                
                                <!-- Action buttons -->
                                <div class="info-section">
                                  <h4 class="text-subtitle-1 mb-2 text-medium-emphasis">
                                    <v-icon
                                      size="18"
                                      class="mr-1"
                                    >
                                      mdi-cog
                                    </v-icon>
                                    Actions
                                  </h4>
                                  <div class="action-buttons">
                                    <v-btn
                                      v-if="item.type !== 'directory' && getFileName(item) && getFileName(item).toLowerCase().endsWith('.pdf')"
                                      variant="outlined"
                                      color="primary"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="previewPdf(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-eye
                                      </v-icon>
                                      Preview PDF
                                    </v-btn>
                                    
                                    <v-btn
                                      v-if="item.type !== 'directory'"
                                      variant="outlined"
                                      color="primary"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="downloadDocument(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-download
                                      </v-icon>
                                      Download
                                    </v-btn>
                                    
                                    <v-btn
                                      v-if="item.type !== 'directory'"
                                      variant="outlined"
                                      color="warning"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="moveItem(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-folder-move
                                      </v-icon>
                                      Move To Directory
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="secondary"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="editItem(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-pencil
                                      </v-icon>
                                      Edit Properties
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="info"
                                      size="small"
                                      class="mb-2 w-100"
                                      @click="shareItem(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-share-variant
                                      </v-icon>
                                      Share
                                    </v-btn>
                                    
                                    <v-btn
                                      variant="outlined"
                                      color="error"
                                      size="small"
                                      class="w-100"
                                      @click="deleteItem(item)"
                                    >
                                      <v-icon
                                        size="16"
                                        class="mr-2"
                                      >
                                        mdi-delete
                                      </v-icon>
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
          </div>

          <!-- Grid View -->
          <div
            v-else
            class="pa-4"
          >
            <v-row>
              <v-col
                v-for="item in allItems"
                :key="item.id"
                cols="12"
                sm="6"
                md="4"
                lg="3"
              >
                <v-card
                  :ripple="false"
                  :class="['cursor-pointer', 'grid-item', { 'grid-selected': selectedItemId.value === item.id }]"
                  hover
                  @click="() => { selectedItemId.value = item.id; handleItemClick(item) }"
                >
                  <v-card-text class="text-center pa-4">
                    <v-icon
                      :color="getItemColor(item)"
                      size="48"
                      class="mb-2"
                    >
                      {{ getItemIcon(item) }}
                    </v-icon>
                    <div class="text-body-2 font-weight-medium text-truncate">
                      {{ item.name }}
                    </div>
                    <div class="text-caption text-medium-emphasis mt-1">
                      {{ item.type === 'directory' ? `${item.total_count} items` : formatFileSize(item.file_size) }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ formatDate(item.updated_at || item.created_at) }}
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Move Document Dialog -->
    <v-dialog
      v-model="showMoveDialog"
      max-width="500"
    >
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <span>Move Document</span>
          <v-btn
            icon="mdi-close"
            variant="text"
            @click="showMoveDialog = false"
          />
        </v-card-title>
        <v-card-text>
          <div
            v-if="movingItem"
            class="mb-4"
          >
            <div class="d-flex align-center mb-3">
              <v-icon
                :color="getTypeColor(movingItem)"
                class="mr-3"
                size="24"
              >
                {{ getItemIcon(movingItem) }}
              </v-icon>
              <div>
                <div class="font-weight-medium">
                  {{ movingItem.name }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ getFileName(movingItem) }}
                </div>
              </div>
            </div>
            
            <v-divider class="mb-4" />
            
            <DirectoryTreeSelector
              v-model="moveForm.directory_id"
              label="Select Destination Directory"
              density="compact"
              class="directory-selector-field"
            />
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="showMoveDialog = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :loading="moving"
            :disabled="!moveForm.directory_id && moveForm.directory_id !== 0"
            @click="confirmMove"
          >
            Move Here
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- PDF Viewer removed - using browser's native viewer -->
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, inject } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import DirectoryTreeSelector from '@/components/DirectoryTreeSelector.vue'

const props = defineProps({
  initialDirectoryId: {
    type: [Number, String],
    default: 0
  }
})

const emit = defineEmits(['upload', 'edit', 'delete', 'download', 'createFolder'])

const router = useRouter()
const showSnackbar = inject('showSnackbar')
const loading = ref(false)
const viewMode = ref('list')
const expanded = ref([])
const selectedItemId = ref(null)
const hoveredItemId = ref(null)
const keyboardSelectedIndex = ref(0)

// Move functionality
const showMoveDialog = ref(false)
const movingItem = ref(null)
const moving = ref(false)
const moveForm = ref({
  directory_id: null
})

// Remove PDF viewer state - using browser's native viewer now

// Data
const currentDirectory = ref(null)
const directories = ref([])
const documents = ref([])
const breadcrumbs = ref([])
const stats = ref({
  directory_count: 0,
  document_count: 0,
  total_count: 0
})

// Table headers
const headers = [
  { title: 'Name', key: 'name', sortable: true, width: '30%' },
  { title: 'Type', key: 'type', sortable: true, width: '12%' },
  { title: 'Size/Count', key: 'size', sortable: false, width: '15%' },
  { title: 'Owner', key: 'owner', sortable: true, width: '15%' },
  { title: 'Modified', key: 'modified', sortable: true, width: '12%' },
  { title: 'Status', key: 'status', sortable: true, width: '8%' },
  { title: '', key: 'actions', sortable: false, width: '8%' }
]

// Computed
const allItems = computed(() => {
  const dirItems = directories.value.map(dir => ({
    ...dir,
    type: 'directory',
    id: `dir-${dir.id}`,
    originalId: dir.id
  }))
  
  const docItems = documents.value.map(doc => ({
    ...doc,
    type: doc.type || 'document',
    id: `doc-${doc.id}`,
    originalId: doc.id
  }))
  
  return [...dirItems, ...docItems]
})

const breadcrumbItems = computed(() => {
  return breadcrumbs.value.map((crumb, index) => ({
    title: crumb.name,
    value: crumb.id,
    disabled: index === breadcrumbs.value.length - 1
  }))
})

const canNavigateUp = computed(() => {
  return breadcrumbs.value.length > 1
})

// Methods
const fetchDirectoryContents = async (directoryId = 0) => {
  loading.value = true
  try {
    const response = await apiService.getDirectoryContents(directoryId)
    
    currentDirectory.value = response.data.current_directory
    directories.value = response.data.directories
    documents.value = response.data.documents
    breadcrumbs.value = response.data.breadcrumbs
    stats.value = response.data.stats
  } catch (error) {
    console.error('Failed to fetch directory contents:', error)
  } finally {
    loading.value = false
  }
}

const navigateToDirectory = async (directoryId) => {
  await fetchDirectoryContents(directoryId)
  // Update URL without full page reload
  router.push({ query: { dir: directoryId } })
}

const navigateUp = () => {
  if (canNavigateUp.value) {
    const parentBreadcrumb = breadcrumbs.value[breadcrumbs.value.length - 2]
    navigateToDirectory(parentBreadcrumb.id)
  }
}

const handleItemClick = (item) => {
  if (item.type === 'directory') {
    navigateToDirectory(item.originalId)
  } else {
    // Preview file using the new preview system
    const fileName = getFileName(item)
    if (fileName) {
      previewFile(item, fileName)
    } else {
      // Fallback to download if no filename
      emit('download', item)
    }
  }
}

// Utility functions
const getItemIcon = (item) => {
  if (item.type === 'directory') {
    return 'mdi-folder'
  }
  
  switch (item.type) {
    case 'QA': return 'mdi-help-circle'
    case 'Image': return 'mdi-image'
    case 'Process_Chart': return 'mdi-chart-box'
    case 'Presentation': return 'mdi-presentation'
    case 'General_Doc': return 'mdi-file-document'
    default: return 'mdi-file'
  }
}

const getItemColor = (item) => {
  if (item.type === 'directory') {
    return 'amber'
  }
  
  switch (item.type) {
    case 'QA': return 'blue'
    case 'Image': return 'green'
    case 'Process_Chart': return 'orange'
    case 'Presentation': return 'purple'
    case 'General_Doc': return 'grey'
    default: return 'grey'
  }
}

const getTypeColor = (type) => {
  if (type === 'directory') return 'amber'
  return getItemColor({ type })
}

const getStatusColor = (status) => {
  switch (status) {
    case 'SUCCESS': return 'success'
    case 'FAILED': return 'error'
    case 'UPDATING': return 'warning'
    default: return 'grey'
  }
}

const formatType = (type) => {
  if (type === 'directory') return 'Folder'
  return type.replace(/_/g, ' ')
}

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

const getDocumentPath = (item) => {
  if (item.type === 'directory') return null
  return item.directory_path || item.file_path || 'Root'
}

const getFormattedPath = (item) => {
  const path = getDocumentPath(item)
  if (!path || path === 'Root') {
    return 'Root'
  }
  // Replace forward slashes with arrow icons
  return path.replace(/\//g, ' ➤ ').replace(/^ ➤ /, '')
}

const getFileName = (item) => {
  if (item.type === 'directory' || !item.file_path) return null
  // Extract filename from file_path
  const pathParts = item.file_path.split('/')
  return pathParts[pathParts.length - 1]
}

const _getExtensionFromType = (type) => {
  switch (type) {
    case 'QA': return 'pdf'
    case 'Image': return 'png'
    case 'Process_Chart': return 'svg'
    case 'Presentation': return 'pptx'
    case 'General_Doc': return 'docx'
    default: return 'txt'
  }
}

// Event handlers
const editItem = (item) => {
  emit('edit', item)
}

const deleteItem = (item) => {
  emit('delete', item)
}

const downloadDocument = (item) => {
  emit('download', item)
}

const previewItem = (item) => {
  const fileName = getFileName(item)
  if (fileName) {
    previewFile(item, fileName)
  } else {
    console.log('No filename available for preview:', item)
  }
}

const getFileExtension = (filename) => {
  if (!filename) return ''
  const parts = filename.split('.')
  if (parts.length < 2) return ''
  return parts.pop().toLowerCase()
}

const previewFile = (item, fileName) => {
  const documentId = item.originalId || item.id
  const fileUrl = `/api/documents/${documentId}/download`
  const extension = getFileExtension(fileName).toLowerCase()
  
  console.log('Previewing file:', fileName, 'Extension:', extension)
  
  switch (extension) {
    case 'pdf':
      // PDFs open directly in browser
      window.open(fileUrl, '_blank')
      break
      
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif':
    case 'bmp':
    case 'svg':
    case 'webp':
      // Images open directly in browser
      window.open(fileUrl, '_blank')
      break
      
    case 'doc':
    case 'docx':
      // Word documents - try Office Online or download
      tryOfficeOnlineOrDownload(fileUrl, fileName, 'Word')
      break
      
    case 'ppt':
    case 'pptx':
      // PowerPoint - try Office Online or download
      tryOfficeOnlineOrDownload(fileUrl, fileName, 'PowerPoint')
      break
      
    case 'xls':
    case 'xlsx':
      // Excel - try Office Online or download
      tryOfficeOnlineOrDownload(fileUrl, fileName, 'Excel')
      break
      
    case 'txt':
    case 'md':
    case 'csv':
      // Text files open directly in browser
      window.open(fileUrl, '_blank')
      break
      
    default:
      // Unknown file type - offer download
      console.log('Unknown file type, offering download')
      downloadDocument(item)
  }
}

const tryOfficeOnlineOrDownload = (fileUrl, fileName, appType) => {
  // For Office documents, we can try opening in Office Online or just download
  // Office Online requires the file to be publicly accessible, which might not work
  // So we'll show a dialog asking user preference
  
  if (confirm(`${appType} document: "${fileName}"\n\nClick OK to download and open locally, or Cancel to open in browser (may not display correctly)`)) {
    // User chose to download
    downloadFileDirectly(fileUrl, fileName)
  } else {
    // User chose to try browser view (likely won't work well but let them try)
    window.open(fileUrl, '_blank')
  }
}

const downloadFileDirectly = (fileUrl, fileName) => {
  // Create a temporary link to trigger download
  const link = document.createElement('a')
  link.href = fileUrl
  link.download = fileName
  link.style.display = 'none'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const previewPdf = (item) => {
  // Legacy function - redirect to new preview logic
  const fileName = getFileName(item)
  if (fileName) {
    previewFile(item, fileName)
  }
}

const shareItem = (item) => {
  // Handle sharing logic
  console.log('Share item:', item)
  // You can emit an event for parent component to handle
  // emit('share', item)
}

const moveItem = (item) => {
  movingItem.value = item
  moveForm.value.directory_id = null
  showMoveDialog.value = true
}

const confirmMove = async () => {
  if (!movingItem.value) return
  
  moving.value = true
  try {
    // API call to move the document
    await apiService.updateDocument(movingItem.value.originalId, {
      directory_id: moveForm.value.directory_id === 0 ? null : moveForm.value.directory_id,
      name: movingItem.value.name,
      type: movingItem.value.type,
      purpose: movingItem.value.purpose || '',
      version: movingItem.value.version || '1.0'
    })
    
    showSnackbar(`Moved "${movingItem.value.name}" successfully`, 'success')
    showMoveDialog.value = false
    
    // Refresh the current directory to reflect the change
    const currentDir = router.currentRoute.value.query.dir || 0
    await fetchDirectoryContents(currentDir)
    
  } catch (error) {
    console.error('Failed to move document:', error)
    showSnackbar('Failed to move document', 'error')
  } finally {
    moving.value = false
  }
}

// Keyboard navigation
const handleKeydown = (event) => {
  if (viewMode.value !== 'list' || allItems.value.length === 0) return
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      keyboardSelectedIndex.value = Math.min(keyboardSelectedIndex.value + 1, allItems.value.length - 1)
      selectedItemId.value = allItems.value[keyboardSelectedIndex.value].id
      break
    case 'ArrowUp':
      event.preventDefault()
      keyboardSelectedIndex.value = Math.max(keyboardSelectedIndex.value - 1, 0)
      selectedItemId.value = allItems.value[keyboardSelectedIndex.value].id
      break
    case 'Enter':
      event.preventDefault()
      if (allItems.value[keyboardSelectedIndex.value]) {
        handleItemClick(allItems.value[keyboardSelectedIndex.value])
      }
      break
  }
}

// Row styling for table
const getRowProps = ({ item, index }) => {
  return {
    class: [
      'file-browser-row',
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
    },
    onDblclick: () => {
      handleItemDoubleClick(item)
    }
  }
}

const handleItemDoubleClick = (item) => {
  if (item.type === 'directory') {
    // Navigate to directory
    navigateToDirectory(item.originalId)
  } else {
    // Preview file
    const fileName = getFileName(item)
    if (fileName) {
      previewFile(item, fileName)
    }
  }
}

// Lifecycle
onMounted(() => {
  const initialDir = props.initialDirectoryId || router.currentRoute.value.query.dir || 0
  fetchDirectoryContents(initialDir)
  
  // Add keyboard event listener
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  // Remove keyboard event listener
  document.removeEventListener('keydown', handleKeydown)
})

// Watch for route changes
watch(() => router.currentRoute.value.query.dir, (newDir) => {
  if (newDir !== undefined) {
    fetchDirectoryContents(newDir || 0)
  }
})

// Expose methods to parent components
const refreshCurrentDirectory = async () => {
  const currentDir = router.currentRoute.value.query.dir || 0
  await fetchDirectoryContents(currentDir)
}

defineExpose({
  refreshCurrentDirectory
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.v-breadcrumbs {
  padding: 0;
}

.v-data-table {
  background: transparent;
}

.breadcrumb-clickable {
  cursor: pointer !important;
  transition: text-decoration 0.2s ease;
}

.breadcrumb-clickable:hover {
  text-decoration: underline !important;
}

/* Override Vuetify's disabled state for breadcrumbs */
.v-breadcrumbs-item:not(.v-breadcrumbs-item--disabled) {
  cursor: pointer;
}

.v-breadcrumbs-item:not(.v-breadcrumbs-item--disabled):hover {
  text-decoration: underline;
}

/* File browser table row highlighting */
.file-browser-table :deep(.file-browser-row) {
  transition: background-color 0.2s ease, border-left 0.2s ease;
  cursor: pointer;
}

.file-browser-table :deep(.file-browser-row:hover),
.file-browser-table :deep(.row-hovered) {
  background-color: rgba(0, 0, 0, 0.04) !important;
}

.v-theme--dark .file-browser-table :deep(.file-browser-row:hover),
.v-theme--dark .file-browser-table :deep(.row-hovered) {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.file-browser-table :deep(.row-selected) {
  background-color: rgba(25, 118, 210, 0.08) !important;
  border-left: 4px solid rgb(25, 118, 210) !important;
}

.v-theme--dark .file-browser-table :deep(.row-selected) {
  background-color: rgba(144, 202, 249, 0.12) !important;
  border-left: 4px solid rgb(144, 202, 249) !important;
}

.file-browser-table :deep(.row-selected:hover) {
  background-color: rgba(25, 118, 210, 0.12) !important;
}

.v-theme--dark .file-browser-table :deep(.row-selected:hover) {
  background-color: rgba(144, 202, 249, 0.16) !important;
}

.file-browser-table :deep(.row-keyboard-focus) {
  outline: 2px solid rgb(25, 118, 210) !important;
  outline-offset: -2px;
}

.v-theme--dark .file-browser-table :deep(.row-keyboard-focus) {
  outline: 2px solid rgb(144, 202, 249) !important;
}

/* Make the table focusable for keyboard navigation */
.file-browser-table {
  position: relative;
}

.file-browser-table:focus-within {
  outline: none;
}

/* Grid view selection highlighting */
.grid-item {
  transition: all 0.2s ease;
}

.grid-selected {
  border: 2px solid rgb(25, 118, 210) !important;
  box-shadow: 0 4px 8px rgba(25, 118, 210, 0.3) !important;
}

.v-theme--dark .grid-selected {
  border: 2px solid rgb(144, 202, 249) !important;
  box-shadow: 0 4px 8px rgba(144, 202, 249, 0.3) !important;
}

/* Expanded row styling */
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
/* Item title hover styling */
.item-title {
  transition: all 0.2s ease;
  text-decoration: none;
  position: relative;
}

.item-title:hover {
  color: rgb(25, 118, 210) !important;
  text-decoration: underline;
  text-decoration-color: rgb(25, 118, 210);
  text-underline-offset: 2px;
}

.v-theme--dark .item-title:hover {
  color: rgb(144, 202, 249) !important;
  text-decoration-color: rgb(144, 202, 249);
}


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