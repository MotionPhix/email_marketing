<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { IconCheck, IconX } from '@tabler/icons-vue'
import { usePage } from '@inertiajs/vue3'

const show = ref(false)
const timeout = ref<number | null>(null)

const flash = computed(() => usePage().props.flash)

const message = computed(() => flash.value?.success || flash.value?.error)
const type = computed(() => (flash.value?.success ? 'success' : 'error'))

const bgColor = computed(() => ({
  success: 'bg-green-50',
  error: 'bg-red-50',
}[type.value]))

const textColor = computed(() => ({
  success: 'text-green-800',
  error: 'text-red-800',
}[type.value]))

const iconColor = computed(() => ({
  success: 'text-green-400',
  error: 'text-red-400',
}[type.value]))

onMounted(() => {
  if (message.value) {
    show.value = true
    timeout.value = window.setTimeout(() => {
      show.value = false
    }, 5000)
  }
})

const close = () => {
  show.value = false
  if (timeout.value) {
    clearTimeout(timeout.value)
  }
}
</script>

<template>
  <div
    v-if="show && message"
    class="fixed bottom-4 right-4 z-50 max-w-sm overflow-hidden rounded-lg shadow-lg"
    :class="bgColor"
  >
    <div class="p-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <IconCheck
            v-if="type === 'success'"
            class="h-6 w-6"
            :class="iconColor"
          />
          <IconX v-else class="h-6 w-6" :class="iconColor" />
        </div>
        <div class="ml-3 w-0 flex-1 pt-0.5">
          <p class="text-sm font-medium" :class="textColor">
            {{ message }}
          </p>
        </div>
        <div class="ml-4 flex flex-shrink-0">
          <button
            type="button"
            class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            :class="textColor"
            @click="close"
          >
            <span class="sr-only">Close</span>
            <IconX class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
