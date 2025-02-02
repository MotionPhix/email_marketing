<script setup lang="ts">
import {ref, computed, watch, onMounted, onUnmounted} from 'vue'
import {router, Link, usePage} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageTitle from '@/Components/PageTitle.vue'
import {Tabs, TabsContent, TabsList, TabsTrigger} from '@/Components/ui/tabs'
import {ScrollArea} from '@/Components/ui/scroll-area'
import {Badge} from '@/Components/ui/badge'
import {
  Send,
  Calendar as CalendarIcon,
  Users,
  Mail,
  MousePointer,
  AlertTriangle,
  CheckCircle,
  Clock,
  Edit,
  Eye,
  Trash2,
  ChevronRight,
  PencilIcon,
} from 'lucide-vue-next'
import {format} from 'date-fns'
import {useTabPersistence} from "@/composables/useTabPersistence";
import {useDeviceDetection} from "@/composables/useDeviceDetection";
import {useDark} from "@vueuse/core";
import PerformanceOverTimeChart from "@/Pages/Campaigns/Components/PerformanceOverTimeChart.vue";
import {Separator} from "@/Components/ui/separator";
import {toast} from "vue-sonner";

// Types
interface Template {
  id?: number
  uuid: string
  name: string
}

interface Recipient {
  recipient_id?: number
  uuid: string
  name: string
  email: string
}

interface Audience {
  id?: number
  uuid: string
  name: string
  recipients?: Recipient[]
}

interface Campaign {
  id?: number
  uuid: string
  title: string
  status: string
  subject?: string
  frequency?: string
  description?: string
  formatted_scheduled_at?: string
  formatted_end_date?: string
  template?: Template
  audience?: Audience
}

interface Statistics {
  stats: {
    bounced: number
    clicked: number
    unique_clicked: number
    unique_opened: number
    spam_report: number
    delivered: number
    opened: number
  }
  chart: Record<string, Record<string, number>>
}

interface Props {
  campaign: Campaign
  statistics: Statistics
  startDate: string
  endDate: string
}

const props = defineProps<Props>()

// State
const isDark = useDark()
const {activeTab, handleTabChange} = useTabPersistence()
const isLoadingStats = ref(false)
const {isMobile} = useDeviceDetection()
const isDatePickerOpen = ref(false)
const page = usePage()

const stats = ref(props.statistics.stats)
const chartData = ref(props.statistics.chart)

// Initialize Echo listener
let echoChannel

// Update your date range state
const dateRange = ref({
  start: new Date(props.startDate),
  end: new Date(props.endDate)
})

// Computed
const displayedRecipients = computed(() =>
  props.campaign.audience?.recipients?.slice(0, 5) || []
)

const remainingRecipients = computed(() =>
  props.campaign.audience?.recipients
    ? Math.max(0, props.campaign.audience.recipients.length - 5)
    : 0
)

const deliveryRate = computed(() => {
  const total = props.statistics.stats.delivered + props.statistics.stats.bounced
  return total > 0
    ? ((props.statistics.stats.delivered / total) * 100).toFixed(1)
    : 0
})

const openRate = computed(() => {
  const total = props.statistics.stats.delivered
  return total > 0
    ? ((props.statistics.stats.unique_opened / total) * 100).toFixed(1)
    : 0
})

const clickRate = computed(() => {
  const total = props.statistics.stats.delivered
  return total > 0
    ? ((props.statistics.stats.unique_clicked / total) * 100).toFixed(1)
    : 0
})

// Update your method
const updateDateRange = (range: { start: Date | null; end: Date | null }) => {
  if (!range.start || !range.end) return

  router.get(
    route('campaigns.show', props.campaign.uuid),
    {
      start_date: format(range.start, 'yyyy-MM-dd'),
      end_date: format(range.end, 'yyyy-MM-dd')
    },
    {
      preserveState: true,
      preserveScroll: true
    }
  )
}

const getStatusBadge = (status: string) => {
  const statusConfig = {
    draft: {
      label: 'Draft',
      class: 'bg-muted text-muted-foreground'
    },
    scheduled: {
      label: 'Scheduled',
      class: 'bg-blue-500/20 text-blue-700'
    },
    sending: {
      label: 'Sending',
      class: 'bg-yellow-500/20 text-yellow-700'
    },
    sent: {
      label: 'Sent',
      class: 'bg-green-500/20 text-green-700'
    },
    failed: {
      label: 'Failed',
      class: 'bg-red-500/20 text-red-700'
    }
  }
  return statusConfig[status] || statusConfig.draft
}

const removeRecipient = async (recipient: Recipient) => {
  if (!props.campaign.audience) return

  await router.delete(
    route('audiences.remove_recipient', {
      audience: props.campaign.audience.uuid,
      recipient: recipient.uuid
    }),
    {
      preserveScroll: true
    }
  )
}

onMounted(() => {
  // Subscribe to private channel for this user's campaign updates
  echoChannel = window.Echo.private(`campaign.stats.${page.props.auth.user.id}`)
    .listen('.stats.updated', (e) => {
      console.log(e)

      if (e.campaignId === props.campaign.uuid) {
        // Only update if it's for the current campaign
        stats.value = e.stats
        chartData.value = e.chartData

        // Update the statistics prop to maintain reactivity
        props.statistics.stats = e.stats
        props.statistics.chart = e.chartData

        // Show toast notification
        toast.success('Campaign statistics updated', {
          description: 'Latest engagement data received'
        })
      }
    })
})

onUnmounted(() => {
  // Clean up Echo listener
  if (echoChannel) {
    echoChannel.stopListening('.stats.updated')
  }
})

watch(dateRange, async (newRange) => {
  if (!newRange.start || !newRange.end) return

  isLoadingStats.value = true

  try {
    await router.get(
      route('campaigns.show', props.campaign.uuid),
      {
        start_date: format(newRange.start, 'yyyy-MM-dd'),
        end_date: format(newRange.end, 'yyyy-MM-dd')
      },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Update URL without page reload
          const url = new URL(window.location.href)
          url.searchParams.set('start_date', format(newRange.start, 'yyyy-MM-dd'))
          url.searchParams.set('end_date', format(newRange.end, 'yyyy-MM-dd'))
          window.history.pushState({}, '', url.toString())
        },
        onError: (errors) => {
          toast.error('Failed to fetch statistics:', {
            description: errors
          })
        }
      }
    )
  } catch (error) {
    toast.error('Error updating statistics:', {
      description: error
    })
  } finally {
    isLoadingStats.value = false
  }
}, {deep: true})
</script>

<template>
  <AppLayout :title="campaign.title">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-muted-foreground">
          <Link :href="route('campaigns.index')">Campaigns</Link>
          <ChevronRight class="h-4 w-4"/>
          <span class="font-medium text-foreground">{{ campaign.title }}</span>
        </div>

        <PageTitle :title="campaign.title"/>
      </div>
    </template>

    <template #action>
      <div class="flex items-center gap-2">
        <GlobalLink
          as="Button"
          preseerve-scroll
          v-if="campaign?.template?.id && campaign?.audience?.id && !campaign?.formatted_scheduled_at"
          :href="route('campaigns.schedule', campaign.uuid)">
          <Clock class="h-4 w-4"/>
          Schedule
        </GlobalLink>

        <Button
          variant="secondary"
          v-if="campaign?.template?.id && campaign?.audience?.id && !campaign?.formatted_scheduled_at"
          @click="router.post(route('campaigns.send', campaign.uuid), {}, { preserveScroll: true })">
          <Send class="h-4 w-4"/>
          Send Now
        </Button>

        <Button
          variant="outline"
          @click="router.get(route('campaigns.edit', campaign.uuid), {}, { replace: true, preserveScroll: true })">
          <Edit class="h-4 w-4"/>
          Edit
        </Button>
      </div>
    </template>

    <div class="mt-12 pb-12">
      <Tabs v-model="activeTab" class="space-y-6" @change="handleTabChange">
        <TabsList class="grid w-full grid-cols-2 lg:w-[400px]">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="statistics">Statistics</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" class="space-y-6">
          <!-- Campaign Details -->
          <Card>
            <CardHeader>
              <CardTitle>Campaign Details</CardTitle>
              <CardDescription>
                <Badge :class="getStatusBadge(campaign.status).class">
                  {{ getStatusBadge(campaign.status).label }}
                </Badge>
              </CardDescription>
            </CardHeader>

            <CardContent class="space-y-6">
              <div class="grid gap-1">
                <h3 class="font-medium">Subject</h3>
                <p class="text-muted-foreground">
                  {{ campaign.subject }}
                </p>
              </div>

              <div class="grid gap-1">
                <h3 class="font-medium">Description</h3>
                <p class="text-muted-foreground">
                  {{ campaign.description || 'No description provided.' }}
                </p>
              </div>

              <Separator/>

              <div class="grid gap-1">
                <h3 class="font-medium">Schedule</h3>
                <div class="flex items-center gap-2 text-muted-foreground">
                  <CalendarIcon class="h-4 w-4"/>
                  <span>
                    {{ campaign.formatted_scheduled_at || 'Not scheduled' }}
                    <template v-if="campaign.formatted_end_date">
                      until {{ campaign.formatted_end_date }}
                    </template>
                  </span>
                </div>
                <p v-if="campaign.frequency" class="text-sm text-muted-foreground">
                  Running {{ campaign.frequency }}
                </p>
              </div>

              <Separator/>

              <div class="grid gap-1">
                <div class="flex items-center justify-between">
                  <h3 class="font-medium">Template</h3>
                  <div class="flex items-center gap-2">
                    <GlobalLink
                      as="Button"
                      v-if="campaign?.template?.id"
                      :href="route('templates.preview', campaign.template.uuid)"
                      variant="outline"
                      size="sm">
                      <Eye class="h-4 w-4"/>
                      Preview
                    </GlobalLink>

                    <Button
                      size="sm"
                      as-child>
                      <Link
                        as="button"
                        :href="campaign?.template?.id
                          ? route('templates.edit', campaign.template.uuid)
                          : route('templates.create', { campaign: campaign.uuid })">
                        <Edit class="h-4 w-4"/>
                        {{ campaign?.template?.id ? 'Edit' : 'Add Template' }}
                      </Link>
                    </Button>
                  </div>
                </div>

                <p class="text-muted-foreground">
                  {{ campaign.template?.name || 'No template assigned.' }}
                </p>
              </div>

              <Separator/>

              <!-- Recipients Section -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="space-y-1">
                    <h3 class="font-medium">Recipients</h3>

                    <p class="text-sm text-muted-foreground">
                      From {{ campaign.audience?.name }} audience
                    </p>
                  </div>

                  <div class="flex items-center gap-2">
                    <GlobalLink
                      v-if="campaign.audience"
                      variant="outline"
                      size="sm"
                      as="Button"
                      :href="route('audiences.add_recipient', campaign.audience.uuid)">
                      <Users class="h-4 w-4"/>
                      Add Recipients
                    </GlobalLink>

                    <Button
                      v-if="campaign.audience"
                      size="sm"
                      as-child>
                      <Link :href="route('audiences.show', campaign.audience.uuid)">
                        View All
                      </Link>
                    </Button>
                  </div>
                </div>

                <ScrollArea v-if="displayedRecipients.length" class="h-[300px] rounded-md border p-4">
                  <div class="space-y-4">
                    <div
                      v-for="recipient in displayedRecipients"
                      :key="recipient.uuid"
                      class="group flex items-center justify-between rounded-lg border p-3">
                      <div class="grid gap-1">
                        <h4 class="font-medium">{{ recipient.name }}</h4>
                        <p class="text-sm text-muted-foreground">
                          {{ recipient.email }}
                        </p>
                      </div>

                      <div
                        class="opacity-0 group-hover:opacity-100 gap-2 flex">
                        <GlobalLink
                          preserve-scroll
                          variant="outline"
                          as="Button" size="icon"
                          :href="route('recipients.edit', recipient.uuid)">
                          <PencilIcon class="h-4 w-4"/>
                        </GlobalLink>

                        <Button
                          variant="ghost"
                          size="icon"
                          @click="removeRecipient(recipient)">
                          <Trash2 class="h-4 w-4"/>
                        </Button>
                      </div>
                    </div>

                    <div
                      v-if="remainingRecipients"
                      class="text-center text-sm text-muted-foreground">
                      + {{ remainingRecipients }} more recipients
                    </div>
                  </div>
                </ScrollArea>

                <div
                  v-else
                  class="flex h-[200px] items-center justify-center rounded-lg border">
                  <div class="text-center">
                    <Users class="mx-auto h-8 w-8 text-muted-foreground"/>
                    <h3 class="mt-2 font-medium">No recipients</h3>
                    <p class="text-sm text-muted-foreground">
                      Add recipients to start sending emails
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="statistics" class="space-y-6">
          <!-- Date Range Picker -->
          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Statistics</CardTitle>
                <Button
                  variant="outline"
                  @click="isDatePickerOpen = true">
                  <CalendarIcon class="h-4 w-4"/>
                  {{ format(dateRange.start, 'PP') }} - {{ format(dateRange.end, 'PP') }}
                </Button>
              </div>
            </CardHeader>

            <CardContent>
              <VDatePicker
                v-model.range="dateRange"
                mode="date" expanded
                :columns="isMobile ? 1 : 2"
                :is-dark="isDark"
              />
            </CardContent>
          </Card>

          <!-- Statistics Grid -->
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Delivery Rate
                  <CheckCircle class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>

              <CardContent>
                <div class="text-2xl font-bold">
                  {{ deliveryRate }}%
                </div>

                <p class="text-xs text-muted-foreground">
                  {{ stats.delivered }} of {{ stats.delivered + stats.bounced }}
                  delivered
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Open Rate
                  <Mail class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>

              <CardContent>
                <div class="text-2xl font-bold">{{ openRate }}%</div>
                <p class="text-xs text-muted-foreground">
                  {{ stats.unique_opened }} unique opens
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Click Rate
                  <MousePointer class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>

              <CardContent>
                <div class="text-2xl font-bold">{{ clickRate }}%</div>
                <p class="text-xs text-muted-foreground">
                  {{ stats.unique_clicked }} unique clicks
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Total Opens
                  <Mail class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>

              <CardContent>
                <div class="text-2xl font-bold">
                  {{ stats.opened }}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Total Clicks
                  <MousePointer class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">
                  {{ stats.clicked }}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-between text-sm font-medium">
                  Bounces & Spam
                  <AlertTriangle class="h-4 w-4 text-muted-foreground"/>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">
                  {{ stats.bounced + stats.spam_report }}
                </div>
                <p class="text-xs text-muted-foreground">
                  {{ stats.bounced }} bounces, {{ stats.spam_report }} spam reports
                </p>
              </CardContent>
            </Card>
          </div>

          <!-- Chart -->
          <Card>
            <CardHeader>
              <CardTitle>Performance Over Time</CardTitle>
            </CardHeader>

            <CardContent>
              <PerformanceOverTimeChart :data="chartData"/>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
