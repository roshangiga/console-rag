<template>
  <v-container
    class="fill-height"
    fluid
  >
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="4"
      >
        <v-card class="elevation-12 login-card">
          <div class="login-header">
            <v-icon
              size="64"
              color="white"
              class="mb-4 login-icon"
            >
              mdi-console
            </v-icon>
            <h2 class="text-h4 font-weight-bold text-white">
              Console RAG
            </h2>
            <p class="text-subtitle-1 mt-2 text-white-darken-1">
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

            <v-divider class="my-6" />

            <v-btn
              variant="outlined"
              color="primary"
              size="large"
              block
              disabled
            >
              <v-icon left>
                mdi-microsoft
              </v-icon>
              Login with Microsoft Entra ID
              <v-chip
                size="small"
                class="ml-2"
              >
                Coming Soon
              </v-chip>
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
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const showSnackbar = inject('showSnackbar')

const form = ref({
  email: '',
  password: ''
})

const errors = ref({
  email: [],
  password: []
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
    showSnackbar('Login successful!', 'success')
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
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -14px rgba(0, 0, 0, 0.25) !important;
}

.login-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 48px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.login-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  animation: float 6s ease-in-out infinite;
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
</style>