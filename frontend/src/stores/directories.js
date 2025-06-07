import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiService } from '@/services/api'

export const useDirectoriesStore = defineStore('directories', () => {
  const directories = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchDirectories = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await apiService.getDirectories()
      directories.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch directories'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createDirectory = async (directoryData) => {
    try {
      const response = await apiService.createDirectory(directoryData)
      await fetchDirectories() // Refresh the tree
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create directory'
      throw err
    }
  }

  const updateDirectory = async (id, directoryData) => {
    try {
      const response = await apiService.updateDirectory(id, directoryData)
      await fetchDirectories() // Refresh the tree
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update directory'
      throw err
    }
  }

  const deleteDirectory = async (id) => {
    try {
      await apiService.deleteDirectory(id)
      await fetchDirectories() // Refresh the tree
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete directory'
      throw err
    }
  }

  // Flatten directories for select dropdown with tree structure
  const flatDirectories = computed(() => {
    const flattenDirectory = (dir, level = 0) => {
      const indent = '  '.repeat(level)
      const arrow = level > 0 ? '→ ' : ''
      const item = {
        id: dir.id,
        display_name: `${indent}${arrow}${dir.name}`,
        name: dir.name,
        level: level
      }
      
      let result = [item]
      
      if (dir.children && dir.children.length > 0) {
        dir.children.forEach(child => {
          result = result.concat(flattenDirectory(child, level + 1))
        })
      }
      
      return result
    }

    // Add root option
    const result = [{
      id: null,
      display_name: '📁 Root',
      name: 'Root',
      level: 0
    }]
    
    directories.value.forEach(dir => {
      result.push(...flattenDirectory(dir, 1))
    })
    
    return result
  })

  return {
    directories,
    loading,
    error,
    flatDirectories,
    fetchDirectories,
    createDirectory,
    updateDirectory,
    deleteDirectory
  }
})