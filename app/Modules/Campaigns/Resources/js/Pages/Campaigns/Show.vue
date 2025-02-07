<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import CampaignStats from './Components/CampaignStats.vue'
import CampaignTrackingChart from './Components/CampaignTrackingChart.vue'
import { formatDistance } from 'date-fns'
import {computed} from "vue";
import {
  IconMailOpened,
  IconClick,
  IconAlertTriangle,
  IconAlertOctagon,
  IconPencil,
  IconTrash,
  IconDotsVertical,
  IconClock,
  IconCheck,
  IconSend,
  IconMail,
  IconBrandCampaignmonitor
} from '@tabler/icons-vue'

interface Props {
  campaign: {
    id: string
    name: string
    subject: string
    content: string
    from_name: string
    from_email: string
    reply_to: string | null
    status: string
    scheduled_at: string | null
    started_at: string | null
    completed_at: string | null
    total_recipients: number
    sent_count: number
    opened_count: number
    clicked_count: number
    bounced_count: number
    complained_count: number
    created_at: string
    updated_at: string
    lists: Array<{
      id: string
      name: string
      description: string | null
      subscriber_count: number
    }>
  }
  events: Array<{
    id: string
    event_type: 'opened' | 'clicked' | 'bounced' | 'complained'
    created_at: string
    recipient: {
      id: string
      email: string
      name: string
    }
  }>
}

const props = defineProps<Props>()

const campaignStatus = computed(() => {
  switch (props.campaign.status) {
    case 'draft':
      return {
        label: 'Draft',
        color: 'text-gray-500 bg-gray-100'
      }
    case 'scheduled':
      return {
        label: 'Scheduled',
        color: 'text-blue-500 bg-blue-100'
      }
    case 'sending':
      return {
        label: 'Sending',
        color: 'text-yellow-500 bg-yellow-100'
      }
    case 'sent':
      return {
        label: 'Sent',
        color: 'text-green-500 bg-green-100'
      }
    case 'failed':
      return {
        label: 'Failed',
        color: 'text-red-500 bg-red-100'
      }
    default:
      return {
        label: props.campaign.status,
        color: 'text-gray-500 bg-gray-100'
      }
  }
})

const openRate = computed(() => {
  if (props.campaign.sent_count === 0) return 0
  return ((props.campaign.opened_count / props.campaign.sent_count) * 100).toFixed(2)
})

const clickRate = computed(() => {
  if (props.campaign.sent_count === 0) return 0
  return ((props.campaign.clicked_count / props.campaign.sent_count) * 100).toFixed(2)
})

const showDeleteDialog = ref(false)

const deleteCampaign = () => {
  router.delete(`/campaigns/${props.campaign.id}`, {
    onSuccess: () => {
      showDeleteDialog.value = false
    }
  })
}

const formatDate = (date: string) => {
  return formatDistance(new Date(date), new Date(), { addSuffix: true })
}

const getEventIcon = (type: string) => {
  switch (type) {
    case 'opened':
      return 'mail-open'
    case 'clicked':
      return 'mouse-pointer'
    case 'bounced':
      return 'alert-triangle'
    case 'complained':
      return 'alert-octagon'
    default:
      return 'circle'
  }
}

const getEventColor = (type: string) => {
  switch (type) {
    case 'opened':
      return 'text-green-500 dark:text-green-400'
    case 'clicked':
      return 'text-blue-500 dark:text-blue-400'
    case 'bounced':
      return 'text-red-500 dark:text-red-400'
    case 'complained':
      return 'text-orange-500 dark:text-orange-400'
    default:
      return 'text-gray-500 dark:text-gray-400'
  }
}
</script>

<template>
  <AppLayout :title="campaign.name">
    <Head :title="campaign.name" />

    <div class="py-6 sm:py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100">
              {{ campaign.name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Created {{ formatDate(campaign.created_at) }}
            </p>
          </div>

          <div class="flex items-center gap-4">
            <Badge :class="campaignStatus.color">
              {{ campaignStatus.label }}
            </Badge>

            <DropdownMenu>
              <DropdownMenuTrigger>
                <Button variant="ghost" size="icon">
                  <MoreVertical class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem
                  v-if="campaign.status === 'draft'"
                  @click="router.get(`/campaigns/${campaign.id}/edit`)"
                >
                  <Pencil class="mr-2 h-4 w-4" />
                  Edit Campaign
                </DropdownMenuItem>
                <DropdownMenuItem
                  v-if="campaign.status === 'draft'"
                  @click="showDeleteDialog = true"
                  class="text-red-600 dark:text-red-400"
                >
                  <Trash2 class="mr-2 h-4 w-4" />
                  Delete Campaign
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <!-- Campaign Stats -->
        <CampaignStats :campaign="campaign" />

        <!-- Campaign Performance Chart -->
        <CampaignTrackingChart :campaign="campaign" :events="events" />

        <!-- Campaign Details -->
        <Card>
          <CardHeader>
            <CardTitle>Campaign Details</CardTitle>
          </CardHeader>
          <CardContent>
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
              <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">Subject</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                  {{ campaign.subject }}
                </dd>
              </div>
              <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">From</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                  {{ campaign.from_name }}
                  <span class="text-gray-500 dark:text-gray-400">
                    &lt;{{ campaign.from_email }}&gt;
                  </span>
                </dd>
              </div>
              <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">Reply To</dt>
                <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                  {{ campaign.reply_to || campaign.from_email }}
                </dd>
              </div>
              <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">Mailing Lists</dt>
                <dd class="mt-1 sm:col-span-2 sm:mt-0">
                  <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                      v-for="list in campaign.lists"
                      :key="list.id"
                      class="py-2"
                    >
                      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ list.name }}
                          </p>
                          <p v-if="list.description" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ list.description }}
                          </p>
                        </div>
                        <Badge variant="secondary">
                          {{ list.subscriber_count }} subscribers
                        </Badge>
                      </div>
                    </li>
                  </ul>
                </dd>
              </div>
            </dl>
          </CardContent>
        </Card>

        <!-- Recent Events -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Events</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="relative">
              <ScrollArea class="h-[400px] w-full rounded-md border dark:border-gray-800">
                <!-- Mobile view -->
                <div class="block sm:hidden">
                  <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                      v-for="event in events"
                      :key="event.id"
                      class="p-4"
                    >
                      <div class="flex items-start gap-3">
                        <div :class="getEventColor(event.event_type)">
                          <component :is="getEventIcon(event.event_type)" class="h-5 w-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ event.recipient.name }}
                          </p>
                          <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ event.recipient.email }}
                          </p>
                          <div class="mt-1 flex items-center gap-2">
                            <Badge
                              :variant="
                                event.event_type === 'bounced' || event.event_type === 'complained'
                                  ? 'destructive'
                                  : 'default'
                              "
                            >
                              {{ event.event_type }}
                            </Badge>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                              {{ formatDate(event.created_at) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>

                <!-- Desktop view -->
                <div class="hidden sm:block">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Event</TableHead>
                        <TableHead>Recipient</TableHead>
                        <TableHead>Time</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      <TableRow
                        v-for="event in events"
                        :key="event.id"
                      >
                        <TableCell>
                          <div class="flex items-center gap-2">
                            <component
                              :is="getEventIcon(event.event_type)"
                              :class="['h-4 w-4', getEventColor(event.event_type)]"
                            />
                            <Badge
                              :variant="
                                event.event_type === 'bounced' || event.event_type === 'complained'
                                  ? 'destructive'
                                  : 'default'
                              "
                            >
                              {{ event.event_type }}
                            </Badge>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                              {{ event.recipient.name }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                              {{ event.recipient.email }}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell>
                          {{ formatDate(event.created_at) }}
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>
              </ScrollArea>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Campaign</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this campaign? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>

        <DialogFooter class="sm:flex-row-reverse">
          <Button
            variant="destructive"
            @click="deleteCampaign">
            Delete
          </Button>

          <Button
            variant="ghost"
            @click="showDeleteDialog = false">
            Cancel
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
