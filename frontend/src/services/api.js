import axios from 'axios'

const BASE_URL = '/api'

const apiClient = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export const apiService = {
  // Authentication
  login: (credentials) => apiClient.post('/auth/login', credentials),
  logout: () => apiClient.post('/auth/logout'),
  getUser: () => apiClient.get('/auth/user'),

  // Directories
  getDirectories: () => apiClient.get('/directories'),
  getDirectoryContents: (id) => apiClient.get(`/directories/${id}/contents`),
  createDirectory: (data) => apiClient.post('/directories', data),
  updateDirectory: (id, data) => apiClient.put(`/directories/${id}`, data),
  deleteDirectory: (id) => apiClient.delete(`/directories/${id}`),

  // Documents
  getDocuments: (params = {}) => apiClient.get('/documents', { params }),
  getDocument: (id) => apiClient.get(`/documents/${id}`),
  createDocument: (data) => apiClient.post('/documents', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  updateDocument: (id, data) => apiClient.put(`/documents/${id}`, data),
  deleteDocument: (id) => apiClient.delete(`/documents/${id}`),
  processDocument: (id) => apiClient.post(`/documents/${id}/process`),
  downloadDocument: (id) => apiClient.get(`/documents/${id}/download`, {
    responseType: 'blob' // Important for binary file downloads
  }),

  // Tags
  getTags: () => apiClient.get('/tags')
}