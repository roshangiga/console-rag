import { defineStore } from 'pinia'
import { ref } from 'vue'
import { apiService } from '@/services/api'

export const useDocumentsStore = defineStore('documents', () => {
  const documents = ref([])
  const currentDocument = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const tags = ref([])

  const fetchDocuments = async (filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiService.getDocuments(filters)
      documents.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch documents'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchDocument = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiService.getDocument(id)
      currentDocument.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch document'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createDocument = async (documentData) => {
    try {
      const response = await apiService.createDocument(documentData)
      await fetchDocuments() // Refresh the list
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create document'
      throw err
    }
  }

  const updateDocument = async (id, documentData) => {
    try {
      const response = await apiService.updateDocument(id, documentData)
      await fetchDocuments() // Refresh the list
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update document'
      throw err
    }
  }

  const deleteDocument = async (id) => {
    try {
      await apiService.deleteDocument(id)
      await fetchDocuments() // Refresh the list
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete document'
      throw err
    }
  }

  const processDocument = async (id) => {
    try {
      const response = await apiService.processDocument(id)
      await fetchDocuments() // Refresh to get updated status
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to process document'
      throw err
    }
  }

  const fetchTags = async () => {
    try {
      const response = await apiService.getTags()
      tags.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tags'
      throw err
    }
  }

  return {
    documents,
    currentDocument,
    loading,
    error,
    tags,
    fetchDocuments,
    fetchDocument,
    createDocument,
    updateDocument,
    deleteDocument,
    processDocument,
    fetchTags
  }
})