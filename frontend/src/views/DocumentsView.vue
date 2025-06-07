<template>
  <v-container fluid>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4 font-weight-bold">Documents</h1>
      <v-btn
        color="primary"
        @click="showUploadDialog = true"
      >
        <v-icon left>mdi-upload</v-icon>
        Upload Document
      </v-btn>
    </div>

    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="searchQuery"
                  label="Search documents..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  dense
                  @input="handleSearch"
                />
              </v-col>
              <v-col cols="12" md="3">
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
              <v-col cols="12" md="3">
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
              <v-col cols="12" md="2">
                <v-btn
                  variant="outlined"
                  @click="clearFilters"
                  block
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
            >
              <template v-slot:item.type="{ item }">
                <v-icon :color="getTypeColor(item.type)" class="mr-2">
                  {{ getDocumentIcon(item.type) }}
                </v-icon>
                {{ formatType(item.type) }}
              </template>

              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="getStatusColor(item.status)"
                  size="small"
                  variant="outlined"
                >
                  {{ item.status }}
                </v-chip>
              </template>

              <template v-slot:item.tags="{ item }">
                <v-chip
                  v-for="tag in item.tags"
                  :key="tag.id"
                  size="small"
                  class="mr-1"
                  variant="outlined"
                >
                  {{ tag.tag_name }}
                </v-chip>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      v-bind="props"
                    />
                  </template>
                  <v-list>
                    <v-list-item @click="processDocument(item)">
                      <v-list-item-title>
                        <v-icon left>mdi-cog</v-icon>
                        Process
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="editDocument(item)">
                      <v-list-item-title>
                        <v-icon left>mdi-pencil</v-icon>
                        Edit
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteDocument(item)" class="text-error">
                      <v-list-item-title>
                        <v-icon left>mdi-delete</v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Upload Dialog Placeholder -->
    <v-dialog v-model="showUploadDialog" max-width="600">
      <v-card>
        <v-card-title>Upload Document</v-card-title>
        <v-card-text>
          <p>Document upload functionality will be implemented in the next phase.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showUploadDialog = false">Close</v-btn>
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

const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Version', key: 'version', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Tags', key: 'tags', sortable: false },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false }
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
  } catch (error) {
    showSnackbar('Failed to fetch documents', 'error')
  }
}

const processDocument = async (document) => {
  try {
    await documentsStore.processDocument(document.id)
    showSnackbar(`Processing initiated for ${document.name}`, 'info')
  } catch (error) {
    showSnackbar('Failed to process document', 'error')
  }
}

const editDocument = (document) => {
  showSnackbar('Edit functionality coming soon', 'info')
}

const deleteDocument = async (document) => {
  if (confirm(`Are you sure you want to delete "${document.name}"?`)) {
    try {
      await documentsStore.deleteDocument(document.id)
      showSnackbar('Document deleted successfully', 'success')
    } catch (error) {
      showSnackbar('Failed to delete document', 'error')
    }
  }
}

onMounted(() => {
  fetchDocuments()
})
</script>