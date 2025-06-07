import { defineStore } from 'pinia';
import { ref, onMounted } from 'vue';

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(false); // Default to false, will be set by initializeTheme

  // Function to load theme from localStorage and set initial isDark state
  const initializeTheme = () => {
    const storedTheme = localStorage.getItem('theme');
    isDark.value = storedTheme === 'dark';
  };

  const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    // Note: The actual Vuetify theme change will be handled by a watcher in App.vue
  };

  // Call initializeTheme when the store is created (or accessed for the first time)
  // This is a common pattern, but App.vue will also call it to ensure timing.
  // For simplicity and explicit control, we can rely on App.vue's onMounted call.
  // initializeTheme(); // Or let App.vue handle the initial call explicitly.

  return {
    isDark,
    initializeTheme, // Expose for App.vue to call on mount
    toggleTheme,
  };
});