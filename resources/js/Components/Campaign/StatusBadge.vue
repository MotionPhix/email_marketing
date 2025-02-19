<script setup lang="ts">
import {
  IconCircleCheck,
  IconClock,
  IconSend,
  IconFileText,
  IconAlertTriangle
} from '@tabler/icons-vue'
import {computed} from "vue";

interface Props {
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
  size?: 'sm' | 'md'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
})

const statusConfig = {
  draft: {
    icon: IconFileText,
    color: 'bg-gray-100 text-gray-800',
    label: 'Draft'
  },
  scheduled: {
    icon: IconClock,
    color: 'bg-blue-100 text-blue-800',
    label: 'Scheduled'
  },
  sending: {
    icon: IconSend,
    color: 'bg-yellow-100 text-yellow-800',
    label: 'Sending'
  },
  sent: {
    icon: IconCircleCheck,
    color: 'bg-green-100 text-green-800',
    label: 'Sent'
  },
  failed: {
    icon: IconAlertTriangle,
    color: 'bg-red-100 text-red-800',
    label: 'Failed'
  }
} as const

const classes = computed(() => ({
  badge: [
    'inline-flex items-center gap-1.5 rounded-full font-medium',
    props.size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-sm',
    statusConfig[props.status].color
  ],
  icon: props.size === 'sm' ? 'h-3 w-3' : 'h-4 w-4'
}))
</script>

<template>
  <span :class="classes.badge">
    <component
      :is="statusConfig[status].icon"
      :class="classes.icon"
    />
    {{ statusConfig[status].label }}
  </span>
</template>
