<template>
  <div class="directory-selector">
    <v-text-field
      :model-value="selectedPath"
      :label="label"
      readonly
      variant="outlined"
      :density="density"
      class="directory-selector-input"
      @click="showSelector = true"
    >
      <template #prepend-inner>
        <v-icon size="small">
          mdi-folder-outline
        </v-icon>
      </template>
      <template #append-inner>
        <v-icon size="small">
          mdi-chevron-down
        </v-icon>
      </template>
    </v-text-field>

    <v-menu
      v-model="showSelector"
      :close-on-content-click="false"
      location="bottom"
      width="300"
    >
      <template #activator="{ props: slotProps }">
        <div v-bind="slotProps" />
      </template>
      
      <v-card class="directory-tree-card">
        <v-card-title class="d-flex align-center justify-space-between pa-3">
          <span class="text-subtitle-2">Select Directory</span>
          <v-btn
            icon="mdi-close"
            size="x-small"
            variant="text"
            @click="showSelector = false"
          />
        </v-card-title>
        
        <!-- Breadcrumb Navigation -->
        <div
          v-if="currentPath.length > 0"
          class="px-3 pb-2"
        >
          <v-breadcrumbs
            :items="breadcrumbItems"
            density="compact"
            class="pa-0"
          >
            <template #item="{ item }">
              <v-breadcrumbs-item
                :class="{ 'breadcrumb-clickable': !item.disabled }"
                @click="navigateToLevel(item.level)"
              >
                {{ item.title }}
              </v-breadcrumbs-item>
            </template>
            <template #divider>
              <v-icon size="small">
                mdi-chevron-right
              </v-icon>
            </template>
          </v-breadcrumbs>
        </div>

        <v-divider />

        <div class="directory-list">
          <!-- Root Option -->
          <v-list-item
            v-if="currentPath.length === 0"
            class="directory-item"
            @click="selectDirectory(null, 'Root')"
          >
            <template #prepend>
              <v-icon color="amber">
                mdi-folder-home
              </v-icon>
            </template>
            <v-list-item-title>Root</v-list-item-title>
            <template #append>
              <v-btn
                icon="mdi-check"
                size="x-small"
                variant="text"
                color="success"
              />
            </template>
          </v-list-item>

          <!-- Current Level Directories -->
          <v-list-item
            v-for="directory in currentDirectories"
            :key="directory.id"
            class="directory-item"
            @click="handleDirectoryClick(directory)"
          >
            <template #prepend>
              <v-icon color="amber">
                mdi-folder
              </v-icon>
            </template>
            
            <v-list-item-title>{{ directory.name }}</v-list-item-title>
            
            <template #append>
              <div class="d-flex align-center">
                <v-btn
                  v-if="hasChildren(directory)"
                  icon="mdi-chevron-right"
                  size="x-small"
                  variant="text"
                  @click.stop="drillDown(directory)"
                />
                <v-btn
                  icon="mdi-check"
                  size="x-small"
                  variant="text"
                  color="success"
                  @click.stop="selectDirectory(directory.id, getDirectoryPath(directory))"
                />
              </div>
            </template>
          </v-list-item>

          <!-- Empty State -->
          <div
            v-if="currentDirectories.length === 0 && currentPath.length > 0"
            class="text-center pa-4 text-medium-emphasis"
          >
            <v-icon
              size="24"
              class="mb-2"
            >
              mdi-folder-open-outline
            </v-icon>
            <div class="text-caption">
              No subdirectories
            </div>
          </div>
        </div>
      </v-card>
    </v-menu>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useDirectoriesStore } from '@/stores/directories'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  },
  label: {
    type: String,
    default: 'Select Directory'
  },
  density: {
    type: String,
    default: 'default'
  }
})

const emit = defineEmits(['update:modelValue'])

const directoriesStore = useDirectoriesStore()
const showSelector = ref(false)
const currentPath = ref([])
const selectedPath = ref('Root')

// Find directory by ID and return with path
const findDirectoryWithPath = (directories, id, path = []) => {
  for (const dir of directories) {
    const currentPath = [...path, dir.name]
    if (dir.id === id) {
      return { directory: dir, path: currentPath }
    }
    if (dir.children) {
      const found = findDirectoryWithPath(dir.children, id, currentPath)
      if (found) return found
    }
  }
  return null
}

// Find directory by ID (simple)
const _findDirectory = (directories, id) => {
  const result = findDirectoryWithPath(directories, id)
  return result ? result.directory : null
}

// Get directories at current level
const currentDirectories = computed(() => {
  if (currentPath.value.length === 0) {
    return directoriesStore.directories
  }
  
  let current = directoriesStore.directories
  for (const pathItem of currentPath.value) {
    const dir = current.find(d => d.id === pathItem.id)
    if (dir && dir.children) {
      current = dir.children
    } else {
      return []
    }
  }
  
  return current
})

// Breadcrumb items
const breadcrumbItems = computed(() => {
  const items = [{ title: 'Root', level: -1, disabled: false }]
  
  currentPath.value.forEach((pathItem, index) => {
    items.push({
      title: pathItem.name,
      level: index,
      disabled: index === currentPath.value.length - 1
    })
  })
  
  return items
})

// Check if directory has children
const hasChildren = (directory) => {
  return directory.children && directory.children.length > 0
}

// Get full path for directory with arrows
const getDirectoryPath = (directory) => {
  const path = [...currentPath.value.map(p => p.name), directory.name]
  return path.join(' ➤ ')
}

// Navigate to specific level
const navigateToLevel = (level) => {
  if (level === -1) {
    currentPath.value = []
  } else {
    currentPath.value = currentPath.value.slice(0, level + 1)
  }
}

// Drill down into directory
const drillDown = (directory) => {
  currentPath.value.push({
    id: directory.id,
    name: directory.name
  })
}

// Handle directory click
const handleDirectoryClick = (directory) => {
  if (hasChildren(directory)) {
    drillDown(directory)
  } else {
    selectDirectory(directory.id, getDirectoryPath(directory))
  }
}

// Select directory
const selectDirectory = (id, path) => {
  emit('update:modelValue', id)
  selectedPath.value = path
  showSelector.value = false
  currentPath.value = []
}

// Watch for external value changes
watch(() => props.modelValue, (newValue) => {
  if (newValue === null) {
    selectedPath.value = 'Root'
  } else {
    // Find the directory and set the full path with arrows
    const result = findDirectoryWithPath(directoriesStore.directories, newValue)
    if (result) {
      selectedPath.value = result.path.join(' ➤ ')
    }
  }
}, { immediate: true })
</script>

<style scoped>
.directory-selector {
  position: relative;
}

.directory-tree-card {
  max-height: 400px;
  overflow: hidden;
}

.directory-list {
  max-height: 300px;
  overflow-y: auto;
}

.directory-item {
  transition: background-color 0.2s ease;
}

.directory-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.v-theme--dark .directory-item:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.breadcrumb-clickable {
  cursor: pointer !important;
}

.breadcrumb-clickable:hover {
  text-decoration: underline;
}

.v-breadcrumbs {
  padding: 0 !important;
}

/* Make the directory selector field clickable */
.directory-selector-input {
  cursor: pointer !important;
}

.directory-selector-input * {
  cursor: pointer !important;
}

.directory-selector-input .v-field {
  cursor: pointer !important;
}

.directory-selector-input .v-field__input {
  cursor: pointer !important;
}

.directory-selector-input .v-field__overlay {
  cursor: pointer !important;
}

.directory-selector-input input {
  cursor: pointer !important;
}

.directory-selector-input .v-text-field__details {
  cursor: pointer !important;
}
</style>