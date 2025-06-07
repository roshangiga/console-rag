<template>
  <div>
    <v-list-item
      :prepend-icon="hasChildren ? (expanded ? 'mdi-folder-open' : 'mdi-folder') : 'mdi-folder'"
      @click="toggleExpanded"
      class="directory-item"
    >
      <v-list-item-title>{{ directory.name }}</v-list-item-title>
      
      <template v-slot:append v-if="hasChildren">
        <v-icon>
          {{ expanded ? 'mdi-chevron-down' : 'mdi-chevron-right' }}
        </v-icon>
      </template>
    </v-list-item>

    <v-expand-transition>
      <div v-if="expanded && hasChildren" class="ml-4">
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
.directory-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}
</style>