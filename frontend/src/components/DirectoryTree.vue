<template>
  <div>
    <v-list
      density="compact"
      class="compact-directory-list"
    >
      <v-list-item class="pa-2">
        <v-list-item-title class="text-subtitle-1 font-weight-medium">
          Directory Structure
        </v-list-item-title>
      </v-list-item>
      
      <v-list-item
        prepend-icon="mdi-folder-open"
        title="File Browser"
        to="/browse"
        class="text-primary compact-item"
      />
      
      <v-divider class="my-1" />
      
      <div
        v-if="directoriesStore.loading"
        class="text-center pa-3"
      >
        <v-progress-circular
          indeterminate
          size="24"
        />
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

const _emit = defineEmits(['select'])

onMounted(async () => {
  if (directoriesStore.directories.length === 0) {
    await directoriesStore.fetchDirectories()
  }
})
</script>

<style scoped>
.compact-directory-list {
  padding: 4px 0 !important;
}

.compact-directory-list :deep(.v-list-item) {
  min-height: 36px !important;
  padding: 0 12px !important;
}

.compact-directory-list :deep(.v-list-item__prepend) {
  width: auto !important;
  margin-right: 0 !important;
  padding-right: 0 !important;
  margin-inline-end: 0 !important;
  padding-inline-end: 0 !important;
}

.compact-directory-list :deep(.v-list-item__prepend > .v-icon) {
  margin-inline-end: 0 !important;
  margin-right: 0 !important;
  margin-left: 0 !important;
  padding: 0 !important;
}

.compact-directory-list :deep(.v-list-item__content) {
  padding-inline-start: 0 !important;
  padding-left: 0 !important;
  margin-left: 0 !important;
}

/* Force no gap between prepend and content */
.compact-directory-list :deep(.v-list-item) {
  gap: 0 !important;
  column-gap: 0 !important;
}

/* Override Vuetify's default 16px gap */
.compact-directory-list :deep(.v-list-item > .v-list-item__overlay ~ .v-list-item__prepend ~ .v-list-item__content) {
  padding-inline-start: 0 !important;
}

/* Remove the 16px default spacing from Vuetify and add minimal spacing */
.compact-directory-list :deep(.v-list-item > *:not(:first-child)) {
  margin-inline-start: 0 !important;
}

/* Add a tiny space between icon and text */
.compact-directory-list :deep(.v-list-item__prepend + .v-list-item__content) {
  margin-inline-start: 2px !important;
}

.compact-item {
  margin-bottom: 4px;
}
</style>