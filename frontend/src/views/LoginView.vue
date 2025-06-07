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
        <v-card class="elevation-12">
          <v-card-title class="text-center pa-6">
            <h2 class="text-h4 font-weight-bold primary--text">
              Console
            </h2>
            <p class="text-subtitle-1 mt-2">
              Document Management System
            </p>
          </v-card-title>

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