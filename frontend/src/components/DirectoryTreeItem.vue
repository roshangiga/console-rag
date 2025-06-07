<template>
  <div>
    <v-list-item
      :prepend-icon="hasChildren ? (expanded ? 'mdi-folder-open' : 'mdi-folder') : 'mdi-folder'"
      class="directory-item compact-tree-item"
      density="compact"
      @click="toggleExpanded"
    >
      <v-list-item-title class="text-body-2">
        {{ directory.name }}
      </v-list-item-title>
      
      <template
        v-if="hasChildren"
        #append
      >
        <v-icon size="18">
          {{ expanded ? 'mdi-chevron-down' : 'mdi-chevron-right' }}
        </v-icon>
      </template>
    </v-list-item>

    <v-expand-transition>
      <div
        v-if="expanded && hasChildren"
        class="compact-children"
      >
        <DirectoryTreeItem
          v-for="child in directory.children"
          :key="child.id"
          :directory="child"
          @select="$emit('select', $event)"
        />
      </div>
    </v-expand-transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  directory: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['select'])

const expanded = ref(false)

const hasChildren = computed(() => {
  return props.directory.children && props.directory.children.length > 0
})

const toggleExpanded = () => {
  if (hasChildren.value) {
    expanded.value = !expanded.value
  }
  emit('select', props.directory)
}
</script>

<style scoped>
.compact-tree-item {
  min-height: 32px !important;
  padding-top: 2px !important;
  padding-bottom: 2px !important;
}

.compact-tree-item :deep(.v-list-item__prepend) {
  width: auto !important;
  margin-right: 0 !important;
  padding-right: 0 !important;
  margin-inline-end: 0 !important;
  padding-inline-end: 0 !important;
}

.compact-tree-item :deep(.v-list-item__prepend > .v-icon) {
  font-size: 18px !important;
  margin-inline-end: 0 !important;
  margin-right: 0 !important;
  margin-left: 0 !important;
  padding: 0 !important;
}

.compact-tree-item :deep(.v-list-item__content) {
  padding-inline-start: 0 !important;
  padding-left: 0 !important;
  margin-left: 0 !important;
}

/* Force no gap between prepend and content */
.compact-tree-item {
  gap: 0 !important;
  column-gap: 0 !important;
}

/* Override Vuetify's default 16px gap */
.compact-tree-item:deep(.v-list-item__overlay ~ .v-list-item__prepend ~ .v-list-item__content) {
  padding-inline-start: 0 !important;
}

/* Remove the 16px default spacing from Vuetify */
.compact-tree-item > :deep(*:not(:first-child)) {
  margin-inline-start: 0 !important;
}

/* Add a tiny space between icon and text */
.compact-tree-item :deep(.v-list-item__prepend + .v-list-item__content) {
  margin-inline-start: 2px !important;
}

.directory-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.v-theme--dark .directory-item:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.compact-children {
  margin-left: 20px;
}

/* Make the text more visible */
.compact-tree-item :deep(.v-list-item-title) {
  line-height: 1.2;
  font-size: 0.875rem !important;
}
</style>