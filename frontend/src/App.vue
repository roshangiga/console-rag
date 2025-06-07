<template>
  <v-app>
    <v-navigation-drawer
      v-if="authStore.isAuthenticated"
      v-model="drawer"
      app
      temporary
    >
      <DirectoryTree />
    </v-navigation-drawer>

    <v-app-bar
      v-if="authStore.isAuthenticated"
      app
      color="primary"
      dark
    >
      <v-app-bar-nav-icon @click="drawer = !drawer" />
      
      <v-toolbar-title class="text-h6" style="cursor: pointer" @click="router.push('/')">
        Console
      </v-toolbar-title>

      <v-spacer />

      <v-btn
        icon
        @click="themeStore.toggleTheme"
      >
        <v-icon>
          {{ themeStore.isDark ? 'mdi-brightness-7' : 'mdi-brightness-4' }}
        </v-icon>
      </v-btn>

      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn
            icon
            v-bind="props"
          >
            <v-avatar size="32">
              <v-icon>mdi-account-circle</v-icon>
            </v-avatar>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>
            <v-list-item-title>{{ authStore.user?.name }}</v-list-item-title>
            <v-list-item-subtitle>{{ authStore.user?.email }}</v-list-item-subtitle>
          </v-list-item>
          <v-divider />
          <v-list-item @click="logout">
            <v-list-item-title>
              <v-icon left>mdi-logout</v-icon>
              Logout
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-main>
      <router-view />
    </v-main>

    <!-- Footer -->
    <div class="footer-spacer"></div>
    <v-footer
      v-if="authStore.isAuthenticated"
      :color="themeStore.isDark ? 'grey-darken-3' : 'grey-lighten-4'"
      class="text-center footer-custom"
      elevation="3"
    >
      <v-container class="py-2">
        <v-row align="center" justify="center" class="footer-main-row">
          <v-col cols="12" md="4" class="text-left py-0">
            <div class="d-flex align-center">
              <v-icon size="16" class="mr-2" :color="themeStore.isDark ? 'white' : 'primary'">mdi-console</v-icon>
              <span class="font-weight-medium footer-text">Console RAG</span>
            </div>
          </v-col>
          
          <v-col cols="12" md="4" class="text-center py-0">
            <div class="text-body-2 footer-text">
              &copy; {{ currentYear }} Mauritius Telecom
            </div>
          </v-col>
          
          <v-col cols="12" md="4" class="text-right py-0">
            <div class="d-flex justify-end align-center">
              <v-btn
                icon="mdi-help-circle-outline"
                variant="text"
                size="x-small"
                class="mr-1 footer-btn"
                @click="showHelp"
              />
              <v-btn
                icon="mdi-information-outline"
                variant="text"
                size="x-small"
                class="footer-btn"
                @click="showAbout"
              />
              <span class="text-caption footer-subtext ml-2">v{{ appVersion }}</span>
            </div>
          </v-col>
        </v-row>
        
        <!-- Links Row -->
        <v-row class="footer-links-row">
          <v-col cols="12" class="text-center py-0">
            <v-btn
              variant="text"
              size="small"
              class="mx-2 footer-link"
              to="/browse"
            >
              File Browser
            </v-btn>
            <v-btn
              variant="text"
              size="small"
              class="mx-2 footer-link"
              to="/documents"
            >
              Documents
            </v-btn>
            <v-btn
              variant="text"
              size="small"
              class="mx-2 footer-link"
              @click="showPrivacyPolicy"
            >
              Privacy Policy
            </v-btn>
            <v-btn
              variant="text"
              size="small"
              class="mx-2 footer-link"
              @click="showTerms"
            >
              Terms of Service
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
    >
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, onMounted, provide, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import DirectoryTree from '@/components/DirectoryTree.vue'

const router = useRouter()
const vuetifyTheme = useTheme()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const drawer = ref(false)
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
  timeout: 4000
})

// Footer data
const currentYear = new Date().getFullYear()
const appVersion = '1.0.0'

const showSnackbar = (message, color = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
    timeout: 4000
  }
}

provide('showSnackbar', showSnackbar)

const logout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
    showSnackbar('Logged out successfully')
  } catch (error) {
    showSnackbar('Error during logout', 'error')
  }
}

// Footer functions
const showHelp = () => {
  showSnackbar('Help documentation coming soon', 'info')
}

const showAbout = () => {
  showSnackbar('Console RAG - Document Management System for Mauritius Telecom Innovation Department', 'info')
}

const showPrivacyPolicy = () => {
  showSnackbar('Privacy Policy coming soon', 'info')
}

const showTerms = () => {
  showSnackbar('Terms of Service coming soon', 'info')
}

onMounted(async () => {
  themeStore.initializeTheme()
  vuetifyTheme.global.name.value = themeStore.isDark ? 'dark' : 'light'
  
  if (authStore.isAuthenticated) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Failed to fetch user:', error)
    }
  }
})

watch(() => themeStore.isDark, (newIsDarkValue) => {
  vuetifyTheme.global.name.value = newIsDarkValue ? 'dark' : 'light'
})
</script>

<style scoped>
.footer-spacer {
  height: 48px;
  width: 100%;
}

.footer-custom {
  margin-top: auto;
  min-height: auto !important;
}

.footer-main-row {
  margin: 0 !important;
}

.footer-links-row {
  margin: 0 !important;
  margin-top: 4px !important;
}

/* Dark theme specific footer styling */
.v-theme--dark .footer-custom {
  border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.v-theme--light .footer-custom {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.footer-text {
  color: rgb(var(--v-theme-on-surface)) !important;
  opacity: 0.9;
}

.footer-subtext {
  color: rgb(var(--v-theme-on-surface)) !important;
  opacity: 0.7;
}

.footer-btn {
  color: rgb(var(--v-theme-on-surface)) !important;
  opacity: 0.8;
}

.footer-btn:hover {
  opacity: 1;
  background-color: rgba(var(--v-theme-on-surface), 0.1);
}

.footer-link {
  color: rgb(var(--v-theme-on-surface)) !important;
  opacity: 0.8;
  text-transform: none;
  font-weight: 400;
}

.footer-link:hover {
  opacity: 1;
  background-color: rgba(var(--v-theme-on-surface), 0.1);
  color: rgb(var(--v-theme-primary)) !important;
}

/* Theme-specific overrides */
.v-theme--dark .footer-text {
  color: rgba(255, 255, 255, 0.9) !important;
}

.v-theme--dark .footer-subtext {
  color: rgba(255, 255, 255, 0.7) !important;
}

.v-theme--dark .footer-btn,
.v-theme--dark .footer-link {
  color: rgba(255, 255, 255, 0.8) !important;
}

.v-theme--dark .footer-btn:hover,
.v-theme--dark .footer-link:hover {
  color: rgba(255, 255, 255, 1) !important;
  background-color: rgba(255, 255, 255, 0.1);
}

.v-theme--light .footer-text {
  color: rgba(0, 0, 0, 0.9) !important;
}

.v-theme--light .footer-subtext {
  color: rgba(0, 0, 0, 0.7) !important;
}

.v-theme--light .footer-btn,
.v-theme--light .footer-link {
  color: rgba(0, 0, 0, 0.8) !important;
}

.v-theme--light .footer-btn:hover,
.v-theme--light .footer-link:hover {
  color: rgba(0, 0, 0, 1) !important;
  background-color: rgba(0, 0, 0, 0.1);
}
</style>