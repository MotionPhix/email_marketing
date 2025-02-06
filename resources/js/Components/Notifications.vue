<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  IconBell,
  IconCheck,
  IconX,
  IconInfoCircle
} from '@tabler/icons-vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Badge } from '@/Components/ui/badge'

interface Notification {
  id: string
  type: string
  data: {
    message: string
    type: string
    plan_name: string
    amount: number
  }
  read_at: string | null
  created_at: string
}

const props = defineProps<{
  notifications: Notification[]
}>()

const unreadCount = ref(0)

onMounted(() => {
  updateUnreadCount()
})

const updateUnreadCount = () => {
  unreadCount.value = props.notifications.filter(n => !n.read_at).length
}

const markAsRead = (notification: Notification) => {
  router.patch(route('notifications.mark-as-read', notification.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      notification.read_at = new Date().toISOString()
      updateUnreadCount()
    }
  })
}

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'subscription_renewal_success':
      return IconCheck
    case 'subscription_renewal_failed':
      return IconX
    default:
      return IconInfoCircle
  }
}

const getNotificationColor = (type: string) => {
  switch (type) {
    case 'subscription_renewal_success':
      return 'text-green-500'
    case 'subscription_renewal_failed':
      return 'text-red-500'
    default:
      return 'text-blue-500'
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger class="relative">
      <IconBell class="h-5 w-5" />
      <Badge
        v-if="unreadCount > 0"
        class="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center p-0"
      >
        {{ unreadCount }}
      </Badge>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-80">
      <div v-if="notifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
        No notifications
      </div>
      <template v-else>
        <DropdownMenuItem
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'flex items-start gap-2 p-3',
            !notification.read_at && 'bg-muted/50'
          ]"
          @click="markAsRead(notification)"
        >
          <component
            :is="getNotificationIcon(notification.data.type)"
            :class="[
              'h-5 w-5 flex-shrink-0 mt-0.5',
              getNotificationColor(notification.data.type)
            ]"
          />
          <div class="flex-1">
            <p class="text-sm font-medium">
              {{ notification.data.message }}
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              Plan: {{ notification.data.plan_name }} -
              {{ new Intl.NumberFormat('en-MW', {
              style: 'currency',
              currency: 'MWK'
            }).format(notification.data.amount) }}
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              {{ new Date(notification.created_at).toLocaleDateString() }}
            </p>
          </div>
        </DropdownMenuItem>
      </template>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
