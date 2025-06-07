<template>
  <v-container fluid>
    <!-- Header with stats and view controls -->
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <div class="d-flex align-center">
          <v-btn
            icon="mdi-arrow-up"
            variant="text"
            size="small"
            :disabled="!canNavigateUp"
            @click="navigateUp"
            class="mr-2"
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
      <v-col cols="12" md="4" class="text-right">
        <v-btn-toggle v-model="viewMode" mandatory class="mr-2">
          <v-btn value="list" icon="mdi-view-list" size="small" />
          <v-btn value="grid" icon="mdi-view-grid" size="small" />
        </v-btn-toggle>
        <v-btn
          color="secondary"
          prepend-icon="mdi-folder-plus"
          class="mr-2"
          @click="$emit('createFolder')"
        >
          New Folder
        </v-btn>
        <v-btn
          color="primary"
          prepend-icon="mdi-upload"
          @click="$emit('upload')"
        >
          Upload
        </v-btn>
      </v-col>
    </v-row>

    <!-- Breadcrumbs -->
    <v-row class="mb-4">
      <v-col cols="12">
        <v-breadcrumbs :items="breadcrumbItems" class="pa-0">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item
              :disabled="item.disabled"
              :class="{ 'breadcrumb-clickable': !item.disabled }"
              @click="navigateToDirectory(item.value)"
            >
              <v-icon v-if="item.value === 0" size="small" class="mr-1">
                mdi-home
              </v-icon>
              {{ item.title }}
            </v-breadcrumbs-item>
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
              class="elevation-1"
              item-value="id"
              show-expand
              :expanded="expanded"
            >
              <!-- Name column with icons -->
              <template v-slot:item.name="{ item }">
                <div class="d-flex align-center cursor-pointer" @click="handleItemClick(item)">
                  <v-icon :color="getItemColor(item)" class="mr-3">
                    {{ getItemIcon(item) }}
                  </v-icon>
                  <div>
                    <div class="font-weight-medium">{{ item.name }}</div>
                    <div v-if="item.type === 'directory'" class="text-caption text-medium-emphasis">
                      {{ item.total_count }} items
                    </div>
                  </div>
                </div>
              </template>

              <!-- Type column -->
              <template v-slot:item.type="{ item }">
                <v-chip
                  :color="getTypeColor(item.type)"
                  size="small"
                  variant="outlined"
                >
                  {{ formatType(item.type) }}
                </v-chip>
              </template>

              <!-- Size/Count column -->
              <template v-slot:item.size="{ item }">
                <span v-if="item.type === 'directory'">
                  {{ item.subdirectory_count }} folders, {{ item.document_count }} files
                </span>
                <span v-else>
                  {{ formatFileSize(item.file_size) }}
                </span>
              </template>

              <!-- Owner column -->
              <template v-slot:item.owner="{ item }">
                <div class="d-flex align-center">
                  <v-avatar size="24" class="mr-2">
                    <v-icon size="16">mdi-account</v-icon>
                  </v-avatar>
                  <span>{{ item.creator?.name || 'Unknown' }}</span>
                </div>
              </template>

              <!-- Date modified -->
              <template v-slot:item.modified="{ item }">
                <div>
                  <div>{{ formatDate(item.updated_at || item.created_at) }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ formatTime(item.updated_at || item.created_at) }}
                  </div>
                </div>
              </template>

              <!-- Status for documents -->
              <template v-slot:item.status="{ item }">
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
              <template v-slot:item.actions="{ item }">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      v-bind="props"
                    />
                  </template>
                  <v-list>
                    <v-list-item v-if="item.type === 'directory'" @click="navigateToDirectory(item.id)">
                      <v-list-item-title>
                        <v-icon left>mdi-folder-open</v-icon>
                        Open
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-else @click="downloadDocument(item)">
                      <v-list-item-title>
                        <v-icon left>mdi-download</v-icon>
                        Download
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="editItem(item)">
                      <v-list-item-title>
                        <v-icon left>mdi-pencil</v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteItem(item)" class="text-error">
                      <v-list-item-title>
                        <v-icon left>mdi-delete</v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>

              <!-- Expanded details -->
              <template v-slot:expanded-row="{ item }">
                <tr>
                  <td colspan="7">
                    <v-card flat class="ma-2">
                      <v-card-text>
                        <v-row>
                          <v-col cols="6">
                            <strong>Created:</strong> {{ formatDateTime(item.created_at) }}
                          </v-col>
                          <v-col cols="6">
                            <strong>Last Modified:</strong> {{ formatDateTime(item.updated_at) }}
                          </v-col>
                          <v-col cols="6" v-if="item.purpose">
                            <strong>Purpose:</strong> {{ item.purpose }}
                          </v-col>
                          <v-col cols="6" v-if="item.version">
                            <strong>Version:</strong> {{ item.version }}
                          </v-col>
                          <v-col cols="12" v-if="item.tags && item.tags.length">
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
                          <v-col cols="12" v-if="item.file_path">
                            <strong>Path:</strong> {{ item.file_path }}
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </td>
                </tr>
              </template>
            </v-data-table>
          </div>

          <!-- Grid View -->
          <div v-else class="pa-4">
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
                  @click="handleItemClick(item)"
                  class="cursor-pointer"
                  hover
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
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'

const props = defineProps({
  initialDirectoryId: {
    type: [Number, String],
    default: 0
  }
})

const emit = defineEmits(['upload', 'edit', 'delete', 'download', 'createFolder'])

const router = useRouter()
const loading = ref(false)
const viewMode = ref('list')
const expanded = ref([])

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
  { title: 'Modified', key: 'modified', sortable: true, width: '15%' },
  { title: 'Status', key: 'status', sortable: true, width: '8%' },
  { title: '', key: 'actions', sortable: false, width: '5%' }
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
    // Handle document click (preview, download, etc.)
    emit('download', item)
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

// Lifecycle
onMounted(() => {
  const initialDir = props.initialDirectoryId || router.currentRoute.value.query.dir || 0
  fetchDirectoryContents(initialDir)
})

// Watch for route changes
watch(() => router.currentRoute.value.query.dir, (newDir) => {
  if (newDir !== undefined) {
    fetchDirectoryContents(newDir || 0)
  }
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
</style>