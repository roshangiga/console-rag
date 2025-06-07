<template>
  <div class="skeleton-wrapper">
    <!-- Table Skeleton -->
    <template v-if="type === 'table'">
      <v-card elevation="0">
        <v-card-text>
          <div
            v-for="i in rows"
            :key="i"
            class="skeleton-row"
          >
            <v-skeleton-loader
              type="table-cell"
              class="skeleton-cell"
            />
            <v-skeleton-loader
              type="table-cell"
              class="skeleton-cell"
            />
            <v-skeleton-loader
              type="table-cell"
              class="skeleton-cell"
            />
            <v-skeleton-loader
              type="table-cell"
              class="skeleton-cell"
            />
          </div>
        </v-card-text>
      </v-card>
    </template>

    <!-- Card Grid Skeleton -->
    <template v-else-if="type === 'card-grid'">
      <v-row>
        <v-col
          v-for="i in count"
          :key="i"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <v-skeleton-loader
            type="card"
            elevation="2"
          />
        </v-col>
      </v-row>
    </template>

    <!-- Stats Card Skeleton -->
    <template v-else-if="type === 'stats'">
      <v-row>
        <v-col
          v-for="i in 4"
          :key="i"
          cols="12"
          md="6"
          lg="3"
        >
          <v-card
            class="stat-skeleton"
            elevation="0"
          >
            <v-card-text class="pa-5">
              <div class="d-flex align-center">
                <v-skeleton-loader
                  type="avatar"
                  class="mr-4"
                  width="56"
                  height="56"
                />
                <div>
                  <v-skeleton-loader
                    type="heading"
                    width="60"
                  />
                  <v-skeleton-loader
                    type="text"
                    width="80"
                    class="mt-2"
                  />
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>

    <!-- List Skeleton -->
    <template v-else-if="type === 'list'">
      <v-list density="compact">
        <v-list-item
          v-for="i in count"
          :key="i"
          class="px-0 mb-2"
        >
          <template #prepend>
            <v-skeleton-loader
              type="avatar"
              width="24"
              height="24"
              class="mr-3"
            />
          </template>
          <div>
            <v-skeleton-loader
              type="text"
              width="180"
              height="16"
            />
            <v-skeleton-loader
              type="text"
              width="120"
              height="14"
              class="mt-1"
            />
          </div>
        </v-list-item>
      </v-list>
    </template>

    <!-- Directory Tree Skeleton -->
    <template v-else-if="type === 'tree'">
      <v-list density="compact">
        <v-list-item
          v-for="i in count"
          :key="i"
          :style="{ paddingLeft: `${(i % 3) * 16}px` }"
        >
          <template #prepend>
            <v-skeleton-loader
              type="avatar"
              width="20"
              height="20"
            />
          </template>
          <v-skeleton-loader
            type="text"
            :width="120 - (i % 3) * 20"
          />
        </v-list-item>
      </v-list>
    </template>

    <!-- Custom Skeleton -->
    <template v-else>
      <v-skeleton-loader
        :type="type"
        v-bind="$attrs"
      />
    </template>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'card'
  },
  count: {
    type: Number,
    default: 3
  },
  rows: {
    type: Number,
    default: 5
  }
})
</script>

<style scoped>
.skeleton-wrapper {
  width: 100%;
}

.skeleton-row {
  display: flex;
  gap: 16px;
  margin-bottom: 12px;
  align-items: center;
}

.skeleton-cell {
  flex: 1;
}

.stat-skeleton {
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.v-theme--dark .stat-skeleton {
  border: 1px solid rgba(255, 255, 255, 0.05);
}

/* Shimmer animation */
.skeleton-wrapper :deep(.v-skeleton-loader__bone) {
  animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>