<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold mb-6">
          Welcome to Console
        </h1>
        
        <v-row>
          <v-col cols="12" md="6" lg="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon color="primary" size="40" class="mr-4">
                    mdi-folder-multiple
                  </v-icon>
                  <div>
                    <div class="text-h6">{{ directoriesStore.directories.length }}</div>
                    <div class="text-subtitle-2">Directories</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon color="success" size="40" class="mr-4">
                    mdi-file-document
                  </v-icon>
                  <div>
                    <div class="text-h6">{{ documentsStore.documents.length }}</div>
                    <div class="text-subtitle-2">Documents</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon color="warning" size="40" class="mr-4">
                    mdi-clock-outline
                  </v-icon>
                  <div>
                    <div class="text-h6">{{ processingCount }}</div>
                    <div class="text-subtitle-2">Processing</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-icon color="error" size="40" class="mr-4">
                    mdi-alert-circle
                  </v-icon>
                  <div>
                    <div class="text-h6">{{ failedCount }}</div>
                    <div class="text-subtitle-2">Failed</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row class="mt-6">
          <v-col cols="12" lg="8">
            <v-card>
              <v-card-title>Recent Documents</v-card-title>
              <v-card-text>
                <v-list>
                  <v-list-item
                    v-for="document in recentDocuments"
                    :key="document.id"
                    :to="`/documents?id=${document.id}`"
                  >
                    <template v-slot:prepend>
                      <v-icon :color="getStatusColor(document.status)">
                        {{ getDocumentIcon(document.type) }}
                      </v-icon>
                    </template>

                    <v-list-item-title>{{ document.name }}</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ document.directory?.name }} • Version {{ document.version }}
                    </v-list-item-subtitle>

                    <template v-slot:append>
                      <v-chip
                        :color="getStatusColor(document.status)"
                        size="small"
                        variant="outlined"
                      >
                        {{ document.status }}
                      </v-chip>
                    </template>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" lg="4">
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
                  <v-icon left>mdi-file-plus</v-icon>
                  Upload Document
                </v-btn>

                <v-btn
                  color="secondary"
                  variant="outlined"
                  block
                  class="mb-3"
                  @click="showCreateDirectoryDialog = true"
                >
                  <v-icon left>mdi-folder-plus</v-icon>
                  Create Directory
                </v-btn>

                <v-btn
                  color="info"
                  variant="outlined"
                  block
                  to="/documents"
                >
                  <v-icon left>mdi-magnify</v-icon>
                  Search Documents
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Create Directory Dialog -->
    <CreateDirectoryDialog
      v-model="showCreateDirectoryDialog"
      @created="handleDirectoryCreated"
    />
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useDirectoriesStore } from '@/stores/directories'
import { useDocumentsStore } from '@/stores/documents'
import CreateDirectoryDialog from '@/components/CreateDirectoryDialog.vue'

const directoriesStore = useDirectoriesStore()
const documentsStore = useDocumentsStore()
const showSnackbar = inject('showSnackbar')

const showCreateDirectoryDialog = ref(false)

const recentDocuments = computed(() => {
  return documentsStore.documents
    .slice()
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 5)
})

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

const handleDirectoryCreated = () => {
  showSnackbar('Directory created successfully!', 'success')
}

onMounted(async () => {
  try {
    await Promise.all([
      directoriesStore.fetchDirectories(),
      documentsStore.fetchDocuments()
    ])
  } catch (error) {
    showSnackbar('Failed to load data', 'error')
  }
})
</script>