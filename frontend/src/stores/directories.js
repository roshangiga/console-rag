import { defineStore } from 'pinia'
import { ref } from 'vue'
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

  return {
    directories,
    loading,
    error,
    fetchDirectories,
    createDirectory,
    updateDirectory,
    deleteDirectory
  }
})