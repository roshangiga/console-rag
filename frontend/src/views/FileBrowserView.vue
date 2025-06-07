<template>
  <v-container fluid>
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold">
        File Browser
      </h1>
    </div>

    <FileBrowser
      ref="fileBrowserRef"
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
          <!-- Loading State -->
          <div
            v-if="loadingUploadDialog"
            class="text-center py-8"
          >
            <v-progress-circular
              indeterminate
              color="primary"
              size="40"
              class="mb-4"
            />
            <p class="text-body-2">
              Preparing upload dialog...
            </p>
          </div>
          
          <!-- Upload Content -->
          <v-row v-else>
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
                  
                  <v-text-field
                    v-model="uploadForm.title"
                    label="Document Title"
                    variant="outlined"
                    density="compact"
                    class="mb-3"
                    placeholder="Enter a descriptive title for the document"
                    hint="This will be the display name for your document"
                    persistent-hint
                  />
                  
                  <DirectoryTreeSelector
                    v-model="uploadForm.directory_id"
                    label="Upload Directory"
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
        <v-card-actions v-if="!loadingUploadDialog">
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
import DragDropUpload from '@/components/DragDropUpload.vue'
import DirectoryTreeSelector from '@/components/DirectoryTreeSelector.vue'
import { apiService } from '@/services/api'

const route = useRoute()
const showSnackbar = inject('showSnackbar')

// Component refs
const fileBrowserRef = ref(null)

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
const loadingUploadDialog = ref(false)

// Create folder
const newFolderName = ref('')
const folderNameErrors = ref([])

// Upload
const uploadComponent = ref(null)
const uploadFiles = ref([])
const uploadForm = ref({
  title: '',
  directory_id: null,
  type: 'General_Doc',
  purpose: '',
  tags: []
})
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

// const tagOptions = [
//   { title: 'Internal', value: 'Internal' },
//   { title: 'Enterprise', value: 'Enterprise' },
//   { title: 'Public', value: 'Public' }
// ]

// Event handlers
const handleUpload = async () => {
  showUploadDialog.value = true
  loadingUploadDialog.value = true
  
  try {
    await updateCurrentDirectoryPath()
    // Set the current directory as the default upload location
    uploadForm.value.directory_id = getCurrentDirectoryId()
  } catch (error) {
    console.error('Failed to prepare upload dialog:', error)
    showSnackbar('Failed to prepare upload dialog', 'error')
  } finally {
    loadingUploadDialog.value = false
  }
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
    
    // Refresh the FileBrowser component
    if (fileBrowserRef.value) {
      await fileBrowserRef.value.refreshCurrentDirectory()
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to create folder'
    showSnackbar(message, 'error')
  } finally {
    creatingFolder.value = false
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
  loadingUploadDialog.value = false
  uploadFiles.value = []
  uploadForm.value = {
    title: '',
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
  uploading.value = true
  try {
    const totalFiles = uploadFiles.value.length
    let successCount = 0
    let failedFiles = []
    
    for (let i = 0; i < totalFiles; i++) {
      const file = uploadFiles.value[i]
      
      // Check file size (50MB limit)
      const maxSizeBytes = 50 * 1024 * 1024 // 50MB in bytes
      if (file.size > maxSizeBytes) {
        failedFiles.push(file.name)
        showSnackbar(`✗ ${file.name} is too large. Maximum size is 50MB.`, 'error')
        continue
      }
      
      // Show progress toast
      showSnackbar(`Uploading ${file.name}... (${i + 1}/${totalFiles})`, 'info')
      
      // Create FormData for file upload
      const formData = new FormData()
      formData.append('file', file)
      formData.append('name', uploadForm.value.title || file.name.replace(/\.[^/.]+$/, '')) // Use title if provided, fallback to filename
      formData.append('directory_id', uploadForm.value.directory_id || getCurrentDirectoryId())
      formData.append('type', uploadForm.value.type)
      formData.append('purpose', uploadForm.value.purpose || `Upload of ${file.name}`)
      formData.append('version', '1.0') // Default version
      
      // Add tags if any
      if (uploadForm.value.tags && uploadForm.value.tags.length > 0) {
        uploadForm.value.tags.forEach((tag, index) => {
          formData.append(`tags[${index}]`, tag)
        })
      }
      
      try {
        // Upload the document
        const response = await apiService.createDocument(formData)
        
        if (response.status === 201) {
          successCount++
          showSnackbar(`✓ ${file.name} uploaded successfully`, 'success')
        }
      } catch (error) {
        console.error(`Failed to upload ${file.name}:`, error)
        failedFiles.push(file.name)
        
        // Extract validation error message if available
        let errorMessage = `✗ Failed to upload ${file.name}`
        if (error.response?.data?.errors) {
          const firstError = Object.values(error.response.data.errors)[0]
          errorMessage += `: ${Array.isArray(firstError) ? firstError[0] : firstError}`
        } else if (error.response?.data?.message) {
          errorMessage += `: ${error.response.data.message}`
        }
        
        showSnackbar(errorMessage, 'error')
      }
    }
    
    // Show final summary
    if (successCount === totalFiles) {
      showSnackbar(`All ${totalFiles} files uploaded successfully!`, 'success')
    } else if (successCount > 0) {
      showSnackbar(`Uploaded ${successCount}/${totalFiles} files. Failed: ${failedFiles.join(', ')}`, 'warning')
    } else {
      showSnackbar('All uploads failed. Please try again.', 'error')
    }
    
    if (successCount > 0) {
      closeUploadDialog()
      // Refresh the FileBrowser component
      if (fileBrowserRef.value) {
        await fileBrowserRef.value.refreshCurrentDirectory()
      }
    }
  } catch (error) {
    console.error('Upload error:', error)
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
    
    // Refresh the FileBrowser component
    if (fileBrowserRef.value) {
      await fileBrowserRef.value.refreshCurrentDirectory()
    }
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
    
    // Refresh the FileBrowser component
    if (fileBrowserRef.value) {
      await fileBrowserRef.value.refreshCurrentDirectory()
    }
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
  const dirParam = urlParams.get('dir')
  return dirParam ? parseInt(dirParam) : 0 // 0 represents root directory
}

const updateCurrentDirectoryPath = async () => {
  // Get the current directory path from URL parameters or set to root
  const dirParam = getCurrentDirectoryId()
  if (dirParam === 0) {
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


// Check for actions on mount
onMounted(async () => {
  if (route.query.action === 'create-folder') {
    showCreateFolderDialog.value = true
  } else if (route.query.action === 'upload') {
    await handleUpload()
  }
})
</script>

<style scoped>
/* Directory selector field styling */
.directory-selector-field {
  cursor: pointer !important;
}

.directory-selector-field * {
  cursor: pointer !important;
}
</style>