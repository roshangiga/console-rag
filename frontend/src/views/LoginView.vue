<template>
  <v-container
    :class="['fill-height', 'login-page-background', theme.global.name.value === 'dark' ? 'login-page-background-dark' : 'login-page-background-light']"
    fluid
  >
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="6"
        lg="4"
      >
        <v-card class="elevation-12 login-card">
          <div :class="['login-header', theme.global.name.value === 'dark' ? 'login-header-dark' : 'login-header-light']">
            <v-icon
              size="64"
              :color="theme.global.name.value === 'dark' ? 'white' : 'white'"
              class="mb-4 login-icon"
            >
              mdi-console
            </v-icon>
            <h2 :class="['text-h5 font-weight-bold']">
              Console RAG
            </h2>
            <p :class="['text-subtitle-2 mt-2']">
              Document Management System
            </p>
          </div>

          <v-card-text>
            <v-form @submit.prevent="handleLogin">
              <v-text-field
                v-model="form.email"
                :error-messages="errors.email"
                label="Email"
                type="email"
                prepend-inner-icon="mdi-email"
                variant="outlined"
                required
              />

              <v-text-field
                v-model="form.password"
                :error-messages="errors.password"
                label="Password"
                type="password"
                prepend-inner-icon="mdi-lock"
                variant="outlined"
                required
              />

              <v-btn
                :loading="authStore.loading"
                type="submit"
                color="primary"
                size="large"
                block
                class="mt-4"
              >
                Login
              </v-btn>
            </v-form>

            <div class="d-flex align-center my-6">
              <v-divider />
              <span class="mx-4 text-body-2 text-medium-emphasis">OR</span>
              <v-divider />
            </div>

            <v-btn
              variant="outlined"
              color="primary"
              size="large"
              block
              disabled
              class="entra-btn"
            >
              <div class="d-flex align-center justify-space-between w-100">
                <div class="d-flex align-center">
                  <v-icon class="ms-icon mr-2">
                    mdi-microsoft-windows
                  </v-icon>
                  <span class="entra-text">Login with Microsoft</span>
                </div>
                <v-chip
                  size="x-small"
                  variant="flat"
                  color="orange"
                  style="margin-left: 80px !important;"
                >
                  Soon
                </v-chip>
              </div>
            </v-btn>
          </v-card-text>

          <v-card-text class="text-center">
            <p class="text-caption">
              Demo Credentials: admin@telecom.mu / password
            </p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from 'vuetify'

const router = useRouter()
const authStore = useAuthStore()
const showSnackbar = inject('showSnackbar')
const theme = useTheme()

const form = ref({
  email: '',
  password: ''
})

const errors = ref({
  email: [],
  password: []
})

const _themeBackgroundClass = computed(() => {
  return theme.global.name.value === 'dark' ? 'login-page-background-dark' : 'login-page-background-light'
})

const handleLogin = async () => {
  errors.value = { email: [], password: [] }

  if (!form.value.email) {
    errors.value.email.push('Email is required')
  }
  if (!form.value.password) {
    errors.value.password.push('Password is required')
  }

  if (errors.value.email.length || errors.value.password.length) {
    return
  }

  try {
    await authStore.login(form.value)
    // showSnackbar('Login successful!', 'success')
    router.push('/')
  } catch (error) {
    const message = error.response?.data?.message || 'Login failed'
    showSnackbar(message, 'error')
    
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  }
}
</script>

<style scoped>
.login-card {
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-card:hover {
  /* transform: translateY(-4px); */
  /* box-shadow: 0 20px 40px -14px rgba(0, 0, 0, 0.25) !important; */
}

.login-header {
  padding: 24px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
  background-image: linear-gradient(270deg, #8E2DE2, #4A00E0, #00c3ff, #fc00ff, #8E2DE2) !important;
  background-size: 400% 400% !important;
  background-repeat: no-repeat !important;
  animation: animatedGradient 16s ease infinite !important;
}

.login-header-light {
  background-image: linear-gradient(270deg, #8E2DE2, #4A00E0, #00c3ff, #fc00ff, #8E2DE2) !important;
  background-size: 400% 400% !important;
  background-repeat: no-repeat !important;
  animation: animatedGradient 16s ease infinite !important;
}

.login-header h2,
.login-header p {
  color: #fff !important;
}

.login-icon {
  position: relative;
  z-index: 1;
  animation: pulse-scale 2s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  50% {
    transform: translate(-30px, -30px) rotate(180deg);
  }
}

@keyframes pulse-scale {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.v-text-field {
  margin-bottom: 16px;
}

/* Input focus effects */
.v-field--focused {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}

/* Button hover effect */
.v-btn {
  transition: all 0.3s ease;
}

.v-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.2);
}

/* Demo credentials styling */
.text-caption {
  position: relative;
  padding: 12px;
  background-color: rgba(0, 0, 0, 0.04);
  border-radius: 8px;
  border: 1px dashed rgba(0, 0, 0, 0.12);
}

.v-theme--dark .text-caption {
  background-color: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.12);
}

.login-page-background {
  min-height: 100vh;
}

.login-page-background-light {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.login-page-background-dark {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

@keyframes animatedGradient {
  0% {
    background-position: 0 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0 50%;
  }
}

@keyframes animatedGradientBg {
  0% {
    background-position: 0 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0 50%;
  }
}

.entra-btn {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  text-transform: none !important;
  padding: 12px 16px !important;
}

.entra-btn .v-btn__content {
  width: 100% !important;
  justify-content: space-between !important;
}

.entra-btn.v-btn--disabled {
  background-color: rgba(0, 0, 0, 0.08) !important;
  border-color: rgba(0, 0, 0, 0.3) !important;
  color: rgba(0, 0, 0, 0.87) !important;
  opacity: 1 !important;
}

.entra-btn.v-btn--disabled .entra-text {
  color: rgba(0, 0, 0, 0.87) !important;
}

.v-theme--dark .entra-btn {
  background-color: rgba(50, 50, 50, 0.8) !important;
  border-color: rgba(150, 150, 150, 0.8) !important;
  color: rgba(200, 200, 200, 1) !important;
}

.v-theme--dark .entra-btn.v-btn--disabled {
  background-color: rgba(50, 50, 50, 0.8) !important;
  border-color: rgba(150, 150, 150, 0.8) !important;
  color: rgba(200, 200, 200, 1) !important;
  opacity: 1 !important;
}

.v-theme--dark .entra-btn .v-icon {
  color: rgba(200, 200, 200, 1) !important;
}

.v-theme--dark .entra-btn.v-btn--disabled .v-icon {
  color: rgba(200, 200, 200, 1) !important;
}

.v-theme--dark .entra-btn .ms-icon,
.v-theme--dark .entra-btn.v-btn--disabled .ms-icon {
  color: #00A4EF !important;
}

.entra-text {
  margin-left: 8px;
}

.v-theme--dark .entra-text {
  color: rgba(200, 200, 200, 1) !important;
}

.v-theme--dark .entra-btn.v-btn--disabled .entra-text {
  color: rgba(200, 200, 200, 1) !important;
}

.ms-icon {
  color: #00A4EF !important;
  font-size: 24px !important;
}

.v-theme--dark .ms-icon {
  color: #00A4EF !important;
  font-size: 24px !important;
}
</style>