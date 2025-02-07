<script setup lang="ts">
import { computed } from 'vue'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import type { Subscription } from '../../../types'

dayjs.extend(relativeTime)

const props = defineProps<{
  subscription: Subscription
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()

const statusColor = computed(() => {
  switch (props.subscription.status) {
    case 'active':
      return 'text-green-700 bg-green-50 ring-green-600/20'
    case 'cancelled':
      return 'text-red-700 bg-red-50 ring-red-600/20'
    case 'expired':
      return 'text-gray-700 bg-gray-50 ring-gray-600/20'
    case 'scheduled':
      return 'text-blue-700 bg-blue-50 ring-blue-600/20'
    default:
      return 'text-yellow-700 bg-yellow-50 ring-yellow-600/20'
  }
})

const formattedEndDate = computed(() => {
  if (!props.subscription.ends_at) return null
  return dayjs(props.subscription.ends_at).format('MMMM D, YYYY')
})

const trialEndsIn = computed(() => {
  if (!props.subscription.trial_ends_at) return null
  return dayjs(props.subscription.trial_ends_at).fromNow()
})
</script>

<template>
  <div class="rounded-lg bg-white shadow">
    <div class="px-4 py-5 sm:p-6">
      <div class="sm:flex sm:items-start sm:justify-between">
        <div>
          <h3 class="text-base font-semibold leading-6 text-gray-900">
            Subscription Status
          </h3>

          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>
              You are currently on the {{ subscription.plan.name }} plan.
            </p>
            <p v-if="subscription.trial_ends_at" class="mt-1">
              Trial ends {{ trialEndsIn }}
            </p>
            <p v-if="formattedEndDate" class="mt-1">
              Subscription ends on {{ formattedEndDate }}
            </p>
          </div>

          <!-- Status Badge -->
          <div class="mt-4">
            <span
              class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
              :class="statusColor"
            >
              {{ subscription.status }}
            </span>
          </div>
        </div>

        <div class="mt-5 sm:ml-6 sm:mt-0">
          <button
            v-if="subscription.status === 'active'"
            type="button"
            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
            @click="emit('cancel')"
          >
            Cancel Subscription
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
