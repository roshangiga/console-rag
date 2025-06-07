<template>
  <v-dialog
    :model-value="modelValue"
    max-width="500"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title>Create New Directory</v-card-title>
      
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field
            v-model="form.name"
            :error-messages="errors.name"
            label="Directory Name"
            variant="outlined"
            required
            autofocus
          />

          <v-select
            v-model="form.parent_id"
            :items="parentOptions"
            label="Parent Directory (Optional)"
            variant="outlined"
            clearable
            item-title="name"
            item-value="id"
          />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn @click="$emit('update:modelValue', false)">
          Cancel
        </v-btn>
        <v-btn
          :loading="loading"
          color="primary"
          @click="handleSubmit"
        >
          Create
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, inject } from 'vue'
import { useDirectoriesStore } from '@/stores/directories'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'created'])

const directoriesStore = useDirectoriesStore()
const showSnackbar = inject('showSnackbar')

const loading = ref(false)
const form = ref({
  name: '',
  parent_id: null
})

const errors = ref({
  name: []
})

const parentOptions = computed(() => {
  const flattenDirectories = (dirs, prefix = '') => {
    let result = []
    dirs.forEach(dir => {
      result.push({
        id: dir.id,
        name: prefix + dir.name
      })
      if (dir.children && dir.children.length > 0) {
        result = result.concat(flattenDirectories(dir.children, prefix + dir.name + ' / '))
      }
    })
    return result
  }
  
  return flattenDirectories(directoriesStore.directories)
})

const handleSubmit = async () => {
  errors.value = { name: [] }
  
  if (!form.value.name.trim()) {
    errors.value.name.push('Directory name is required')
    return
  }

  loading.value = true
  try {
    await directoriesStore.createDirectory(form.value)
    emit('created')
    emit('update:modelValue', false)
    resetForm()
  } catch (_error) {
    showSnackbar('Failed to create directory', 'error')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    parent_id: null
  }
  errors.value = { name: [] }
}

watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    resetForm()
  }
})
</script>