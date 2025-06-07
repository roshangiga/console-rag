<template>
  <v-container fluid>
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold">
        File Browser
      </h1>
    </div>

    <FileBrowser
      @upload="handleUpload"
      @edit="handleEdit"
      @delete="handleDelete"
      @download="handleDownload"
      @create-folder="() => showCreateFolderDialog = true"
    />

    <!-- Create Folder Dialog -->
    <v-dialog
      v-model="showCreateFolderDialog"
      max-width="500"
    >
      <v-card>
        <v-card-title>Create New Folder</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="newFolderName"
            label="Folder Name"
            variant="outlined"
            :error-messages="folderNameErrors"
            @keyup.enter="createFolder"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showCreateFolderDialog = false">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :loading="creatingFolder"
            @click="createFolder"
          >
            Create
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Upload Dialog -->
    <v-dialog
      v-model="showUploadDialog"
      max-width="600"
    >
      <v-card>
        <v-card-title>Upload Documents</v-card-title>
        <v-card-text>
          <!-- Current Location Display -->
          <v-alert
            type="info"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            <v-icon slot="prepend">mdi-folder</v-icon>
            <strong>Upload Location:</strong> {{ currentDirectoryPath }}
          </v-alert>
          <v-file-input
            v-model="selectedFiles"
            label="Select files"
            multiple
            variant="outlined"
            show-size
            accept="*/*"
          />
          
          <v-select
            v-model="uploadType"
            :items="documentTypes"
            label="Document Type"
            variant="outlined"
            class="mt-4"
          />
          
          <v-text-field
            v-model="uploadPurpose"
            label="Purpose (optional)"
            variant="outlined"
            class="mt-2"
          />
          
          <v-row class="mt-2">
            <v-col
              cols="12"
              md="6"
            >
              <v-select
                v-model="uploadTags"
                :items="tagOptions"
                label="Tags"
                variant="outlined"
                multiple
                chips
              />
            </v-col>
            <v-col
              cols="12"
              md="6"
            >
              <v-text-field
                v-model="uploadVersion"
                label="Version"
                variant="outlined"
                min="0.1"
                step="0.1"
                hint="Default version is 1.0 for new documents"
                persistent-hint
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showUploadDialog = false">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :loading="uploading"
            :disabled="!selectedFiles || selectedFiles.length === 0"
            @click="uploadFiles"
          >
            Upload
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Edit Dialog -->
    <v-dialog
      v-model="showEditDialog"
      max-width="500"
    >
      <v-card>
        <v-card-title>Edit {{ editingItem?.type === 'directory' ? 'Folder' : 'Document' }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="editName"
            label="Name"
            variant="outlined"
            :error-messages="editNameErrors"
          />
          
          <v-select
            v-if="editingItem?.type !== 'directory'"
            v-model="editType"
            :items="documentTypes"
            label="Type"
            variant="outlined"
            class="mt-2"
          />
          
          <v-text-field
            v-if="editingItem?.type !== 'directory'"
            v-model="editPurpose"
            label="Purpose"
            variant="outlined"
            class="mt-2"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showEditDialog = false">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :loading="editing"
            @click="saveEdit"
          >
            Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog
      v-model="showDeleteDialog"
      max-width="400"
    >
      <v-card>
        <v-card-title class="text-error">
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete "{{ deletingItem?.name }}"?
          <div
            v-if="deletingItem?.type === 'directory'"
            class="text-warning mt-2"
          >
            <v-icon left>
              mdi-alert
            </v-icon>
            This will also delete all contents inside this folder.
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="showDeleteDialog = false">
            Cancel
          </v-btn>
          <v-btn
            color="error"
            :loading="deleting"
            @click="confirmDelete"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FileBrowser from '@/components/FileBrowser.vue'
import { apiService } from '@/services/api'

const route = useRoute()
const showSnackbar = inject('showSnackbar')

// Dialog states
const showCreateFolderDialog = ref(false)
const showUploadDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)

// Loading states
const creatingFolder = ref(false)
const uploading = ref(false)
const editing = ref(false)
const deleting = ref(false)

// Create folder
const newFolderName = ref('')
const folderNameErrors = ref([])

// Upload
const selectedFiles = ref([])
const uploadType = ref('General_Doc')
const uploadPurpose = ref('')
const uploadTags = ref([])
const uploadVersion = ref('1.0')
const currentDirectoryPath = ref('/')

// Edit
const editingItem = ref(null)
const editName = ref('')
const editType = ref('')
const editPurpose = ref('')
const editNameErrors = ref([])

// Delete
const deletingItem = ref(null)

// Options
const documentTypes = [
  { title: 'QA Documents', value: 'QA' },
  { title: 'Images', value: 'Image' },
  { title: 'Process Charts', value: 'Process_Chart' },
  { title: 'Presentations', value: 'Presentation' },
  { title: 'General Documents', value: 'General_Doc' }
]

const tagOptions = [
  { title: 'Internal', value: 'Internal' },
  { title: 'Enterprise', value: 'Enterprise' },
  { title: 'Public', value: 'Public' }
]

// Event handlers
const handleUpload = async () => {
  await updateCurrentDirectoryPath()
  showUploadDialog.value = true
}

const handleEdit = (item) => {
  editingItem.value = item
  editName.value = item.name
  editType.value = item.type
  editPurpose.value = item.purpose || ''
  editNameErrors.value = []
  showEditDialog.value = true
}

const handleDelete = (item) => {
  deletingItem.value = item
  showDeleteDialog.value = true
}

const handleDownload = (item) => {
  if (item.type === 'directory') return
  
  // Simulate download
  showSnackbar(`Downloading ${item.name}...`, 'info')
  // In a real app, you would initiate the actual download here
}

// Create folder
const createFolder = async () => {
  folderNameErrors.value = []
  
  if (!newFolderName.value.trim()) {
    folderNameErrors.value = ['Folder name is required']
    return
  }
  
  creatingFolder.value = true
  try {
    await apiService.createDirectory({
      name: newFolderName.value.trim(),
      parent_id: getCurrentDirectoryId()
    })
    
    showSnackbar('Folder created successfully', 'success')
    showCreateFolderDialog.value = false
    newFolderName.value = ''
    
    // Refresh the current directory
    window.location.reload()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to create folder'
    showSnackbar(message, 'error')
  } finally {
    creatingFolder.value = false
  }
}

// Upload files
const uploadFiles = async () => {
  uploading.value = true
  try {
    const formData = new FormData()
    
    selectedFiles.value.forEach(file => {
      formData.append('files[]', file)
    })
    
    formData.append('type', uploadType.value)
    formData.append('purpose', uploadPurpose.value)
    formData.append('directory_id', getCurrentDirectoryId())
    formData.append('version', uploadVersion.value)
    
    uploadTags.value.forEach(tag => {
      formData.append('tags[]', tag)
    })
    
    await apiService.createDocument(formData)
    
    showSnackbar('Files uploaded successfully', 'success')
    showUploadDialog.value = false
    resetUploadForm()
    
    // Refresh the current directory
    window.location.reload()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to upload files'
    showSnackbar(message, 'error')
  } finally {
    uploading.value = false
  }
}

// Save edit
const saveEdit = async () => {
  editNameErrors.value = []
  
  if (!editName.value.trim()) {
    editNameErrors.value = ['Name is required']
    return
  }
  
  editing.value = true
  try {
    const data = { name: editName.value.trim() }
    
    if (editingItem.value.type === 'directory') {
      await apiService.updateDirectory(editingItem.value.originalId, data)
    } else {
      data.type = editType.value
      data.purpose = editPurpose.value
      await apiService.updateDocument(editingItem.value.originalId, data)
    }
    
    showSnackbar('Item updated successfully', 'success')
    showEditDialog.value = false
    
    // Refresh the current directory
    window.location.reload()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to update item'
    showSnackbar(message, 'error')
  } finally {
    editing.value = false
  }
}

// Confirm delete
const confirmDelete = async () => {
  deleting.value = true
  try {
    if (deletingItem.value.type === 'directory') {
      await apiService.deleteDirectory(deletingItem.value.originalId)
    } else {
      await apiService.deleteDocument(deletingItem.value.originalId)
    }
    
    showSnackbar('Item deleted successfully', 'success')
    showDeleteDialog.value = false
    
    // Refresh the current directory
    window.location.reload()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to delete item'
    showSnackbar(message, 'error')
  } finally {
    deleting.value = false
  }
}

// Utility functions
const getCurrentDirectoryId = () => {
  const urlParams = new URLSearchParams(window.location.search)
  return urlParams.get('dir') || 0
}

const updateCurrentDirectoryPath = async () => {
  // Get the current directory path from URL parameters or set to root
  const dirParam = getCurrentDirectoryId()
  if (dirParam === 0 || dirParam === '0') {
    currentDirectoryPath.value = 'Root'
  } else {
    try {
      // Fetch the directory path from the API
      const response = await apiService.getDirectoryContents(dirParam)
      const breadcrumbs = response.data.breadcrumbs || []
      
      // Build the path with arrows
      if (breadcrumbs.length > 0) {
        currentDirectoryPath.value = breadcrumbs
          .map(crumb => crumb.name)
          .join(' ➤ ')
      } else {
        currentDirectoryPath.value = 'Root'
      }
    } catch (error) {
      console.error('Failed to fetch directory path:', error)
      currentDirectoryPath.value = 'Root'
    }
  }
}

const resetUploadForm = () => {
  selectedFiles.value = []
  uploadType.value = 'General_Doc'
  uploadPurpose.value = ''
  uploadTags.value = []
  uploadVersion.value = '1.0'
}

// Check for actions on mount
onMounted(async () => {
  if (route.query.action === 'create-folder') {
    showCreateFolderDialog.value = true
  } else if (route.query.action === 'upload') {
    await updateCurrentDirectoryPath()
    showUploadDialog.value = true
  }
})
</script>