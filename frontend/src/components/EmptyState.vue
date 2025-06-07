<template>
  <div class="empty-state-container">
    <div class="empty-state-content">
      <!-- Icon or Illustration -->
      <div class="empty-state-icon mb-4">
        <v-icon
          :size="iconSize"
          :color="iconColor"
          class="empty-state-icon-animated"
        >
          {{ icon }}
        </v-icon>
      </div>

      <!-- Title -->
      <h3 class="text-h5 font-weight-medium mb-2">
        {{ title }}
      </h3>

      <!-- Description -->
      <p
        v-if="description"
        class="text-body-1 text-medium-emphasis mb-4 empty-state-description"
      >
        {{ description }}
      </p>

      <!-- Action Button -->
      <div
        v-if="actionText"
        class="empty-state-actions"
      >
        <v-btn
          :color="actionColor"
          :variant="actionVariant"
          :size="actionSize"
          @click="$emit('action')"
        >
          <v-icon
            v-if="actionIcon"
            :start="true"
          >
            {{ actionIcon }}
          </v-icon>
          {{ actionText }}
        </v-btn>
      </div>

      <!-- Additional Slot -->
      <div
        v-if="$slots.default"
        class="mt-4"
      >
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'mdi-folder-open-outline'
  },
  iconSize: {
    type: Number,
    default: 64
  },
  iconColor: {
    type: String,
    default: 'grey-lighten-1'
  },
  title: {
    type: String,
    default: 'No items found'
  },
  description: {
    type: String,
    default: ''
  },
  actionText: {
    type: String,
    default: ''
  },
  actionIcon: {
    type: String,
    default: ''
  },
  actionColor: {
    type: String,
    default: 'primary'
  },
  actionVariant: {
    type: String,
    default: 'flat'
  },
  actionSize: {
    type: String,
    default: 'default'
  }
})

defineEmits(['action'])
</script>

<style scoped>
.empty-state-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 48px 24px;
}

.empty-state-content {
  text-align: center;
  max-width: 400px;
}

.empty-state-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.04);
  margin: 0 auto;
}

.v-theme--dark .empty-state-icon {
  background-color: rgba(255, 255, 255, 0.04);
}

.empty-state-icon-animated {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.empty-state-description {
  max-width: 300px;
  margin: 0 auto;
  line-height: 1.6;
}

.empty-state-actions {
  margin-top: 24px;
}

/* Hover effect */
.empty-state-icon:hover .empty-state-icon-animated {
  animation-play-state: paused;
  transform: scale(1.05);
  transition: transform 0.3s ease;
}
</style>