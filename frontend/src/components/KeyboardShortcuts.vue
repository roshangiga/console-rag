<template>
  <v-dialog
    v-model="dialog"
    max-width="500"
  >
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <span>Keyboard Shortcuts</span>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="dialog = false"
        />
      </v-card-title>
      
      <v-card-text>
        <v-list density="comfortable">
          <v-list-item
            v-for="shortcut in shortcuts"
            :key="shortcut.key"
            class="px-0"
          >
            <template #prepend>
              <div class="shortcut-key-wrapper">
                <kbd
                  v-for="(key, index) in shortcut.keys"
                  :key="index"
                  class="shortcut-key"
                >
                  {{ key }}
                </kbd>
              </div>
            </template>
            
            <v-list-item-title>{{ shortcut.description }}</v-list-item-title>
            
            <template #append>
              <v-icon
                :color="shortcut.color"
                size="small"
              >
                {{ shortcut.icon }}
              </v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="text"
          @click="dialog = false"
        >
          Close
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const dialog = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0
const cmdKey = isMac ? '⌘' : 'Ctrl'

const shortcuts = ref([
  {
    keys: [cmdKey, 'K'],
    description: 'Search documents',
    icon: 'mdi-magnify',
    color: 'primary'
  },
  {
    keys: [cmdKey, 'U'],
    description: 'Upload document',
    icon: 'mdi-upload',
    color: 'success'
  },
  {
    keys: [cmdKey, 'B'],
    description: 'Open file browser',
    icon: 'mdi-folder-open',
    color: 'warning'
  },
  {
    keys: [cmdKey, 'Shift', 'D'],
    description: 'Toggle dark mode',
    icon: 'mdi-theme-light-dark',
    color: 'secondary'
  },
  {
    keys: ['Esc'],
    description: 'Close dialogs',
    icon: 'mdi-close',
    color: 'error'
  }
])
</script>

<style scoped>
.shortcut-key-wrapper {
  display: flex;
  gap: 4px;
  margin-right: 16px;
}

.shortcut-key {
  display: inline-block;
  padding: 4px 8px;
  font-size: 12px;
  font-family: monospace;
  background-color: rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.v-theme--dark .shortcut-key {
  background-color: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.v-list-item {
  padding: 12px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.v-theme--dark .v-list-item {
  border-bottom-color: rgba(255, 255, 255, 0.05);
}

.v-list-item:last-child {
  border-bottom: none;
}
</style>