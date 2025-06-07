<template>
  <div>
    <v-list>
      <v-list-item>
        <v-list-item-title class="text-h6 mb-2">
          Directory Structure
        </v-list-item-title>
      </v-list-item>
      
      <v-divider />
      
      <div v-if="directoriesStore.loading" class="text-center pa-4">
        <v-progress-circular indeterminate />
      </div>
      
      <template v-else>
        <DirectoryTreeItem
          v-for="directory in directoriesStore.directories"
          :key="directory.id"
          :directory="directory"
          @select="$emit('select', $event)"
        />
      </template>
    </v-list>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useDirectoriesStore } from '@/stores/directories'
import DirectoryTreeItem from './DirectoryTreeItem.vue'

const directoriesStore = useDirectoriesStore()

const emit = defineEmits(['select'])

onMounted(async () => {
  if (directoriesStore.directories.length === 0) {
    await directoriesStore.fetchDirectories()
  }
})
</script>