import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useTheme } from 'vuetify'

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(localStorage.getItem('theme') === 'dark')
  
  const toggleTheme = () => {
    isDark.value = !isDark.value
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    
    // Update Vuetify theme if available
    if (typeof window !== 'undefined' && window.__vuetify_theme) {
      window.__vuetify_theme.global.name.value = isDark.value ? 'dark' : 'light'
    }
  }

  const initializeTheme = (vuetifyTheme) => {
    vuetifyTheme.global.name.value = isDark.value ? 'dark' : 'light'
  }

  return {
    isDark,
    toggleTheme,
    initializeTheme
  }
})