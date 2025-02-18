<script setup lang="ts">
import { computed, ref } from 'vue'
import {Head, Link, router, useForm} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import VueApexCharts from 'vue3-apexcharts'
import {
  MoreVertical,
  Mail,
  Edit2,
  Trash2,
  AlertCircle,
  CheckCircle2,
  XCircle,
  MousePointerClick,
  Eye,
  Ban,
  Flag,
  MailX,
  Clock,
  Building2
} from 'lucide-vue-next'
import {toast} from "vue-sonner";
import {useStorage} from "@vueuse/core";

// Props and composables
const props = defineProps<{
  subscriber: {
    id: number
    email: string
    first_name: string
    last_name: string
    company: string
    status: string
    created_at: string
    metadata: any
    stats: {
      total_campaigns: number
      open_rate: number
      click_rate: number
      bounce_rate: number
      spam_rate: number
    }
    activity: Array<{
      id: number
      type: string
      campaign_name: string
      created_at: string
    }>
    campaigns: Array<{
      id: number
      name: string
      subject: string
      sent_at: string
      stats: {
        opens: number
        clicks: number
        bounces: number
        complaints: number
      }
    }>
  }
}>()

const showDeleteDialog = ref(false)
const showEditDialog = ref(false)
const activeTab = useStorage('subscriber_tabs', 'overview')

// Computed properties
const fullName = computed(() => {
  return `${props.subscriber.first_name} ${props.subscriber.last_name}`
})

const statusColor = computed(() => {
  const colors = {
    subscribed: 'success',
    unsubscribed: 'warning',
    bounced: 'destructive',
    complained: 'destructive'
  }
  return colors[props.subscriber.status] || 'default'
})

// Chart options and data
const engagementChartOptions = {
  chart: {
    type: 'area',
    height: 350,
    toolbar: {
      show: false
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth'
  },
  xaxis: {
    type: 'datetime'
  },
  tooltip: {
    x: {
      format: 'dd MMM yyyy'
    }
  }
}

const engagementChartSeries = computed(() => {
  // Transform campaign data for chart
  const dates = props.subscriber.campaigns.map(c => new Date(c.sent_at).getTime())
  const opens = props.subscriber.campaigns.map(c => c.stats.opens)
  const clicks = props.subscriber.campaigns.map(c => c.stats.clicks)

  return [
    {
      name: 'Opens',
      data: dates.map((date, i) => [date, opens[i]])
    },
    {
      name: 'Clicks',
      data: dates.map((date, i) => [date, clicks[i]])
    }
  ]
})

const performanceChartOptions = {
  chart: {
    type: 'radialBar',
    height: 350
  },
  plotOptions: {
    radialBar: {
      dataLabels: {
        name: {
          fontSize: '22px',
        },
        value: {
          fontSize: '16px',
        },
        total: {
          show: true,
          label: 'Total',
          formatter: function (w) {
            return Math.round(w.globals.seriesTotals.reduce((a, b) => a + b) / w.globals.series.length) + '%'
          }
        }
      }
    }
  },
  labels: ['Open Rate', 'Click Rate', 'Delivery Rate']
}

const performanceChartSeries = computed(() => [
  Math.round(props.subscriber.stats.open_rate * 100),
  Math.round(props.subscriber.stats.click_rate * 100),
  Math.round((1 - props.subscriber.stats.bounce_rate) * 100)
])

// Actions
const handleDelete = () => {
  router.delete(route('subscribers.destroy', props.subscriber.id), {
    onSuccess: () => {
      toast.success('Subscriber deleted successfully')
      router.visit(route('subscribers.index'))
    }
  })
}

const handleUnsubscribe = () => {
  router.patch(route('subscribers.unsubscribe', props.subscriber.id), {}, {
    onSuccess: () => {
      toast.success('Subscriber unsubscribed successfully')
    }
  })
}

// Add form handling
const form = useForm({
  first_name: props.subscriber.first_name,
  last_name: props.subscriber.last_name,
  email: props.subscriber.email,
  company: props.subscriber.company,
  status: props.subscriber.status,
})

const handleUpdate = () => {
  form.patch(route('subscribers.update', props.subscriber.id), {
    onSuccess: () => {
      showEditDialog.value = false
      toast.success('Subscriber updated successfully')
    },
  })
}

// Add activity icon helper
const getActivityIcon = (type: string) => {
  const icons = {
    open: Eye,
    click: MousePointerClick,
    bounce: Ban,
    complaint: Flag,
    unsubscribe: MailX,
  }
  return icons[type] || Clock
}

// Add activity text helper
const getActivityText = (type: string) => {
  const texts = {
    open: 'Opened email',
    click: 'Clicked link',
    bounce: 'Email bounced',
    complaint: 'Marked as spam',
    unsubscribe: 'Unsubscribed',
  }
  return texts[type] || 'Unknown activity'
}
</script>

<template>
  <AppLayout>
    <Head :title="`Subscriber: ${fullName}`" />

    <!-- Header -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Explore {{ fullName }}
      </h2>
    </template>

    <!-- Action Button -->
    <template #action>
      <div class="flex items-center gap-2">
        <GlobalLink
          as="Button"
          class="bg-stone-500" size="icon"
          :href="route('subscribers.create')">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
            <path d="M12.5 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C7.67837 14.2307 10.1368 13.7719 12.5 14.1052C13.3575 14.2261 14.1926 14.4514 15 14.7809" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M18.5 22L18.5 15M15 18.5H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </GlobalLink>

        <GlobalLink
          size="icon"
          href="#"
          as="Button">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
            <path d="M11.1002 3C7.45057 3.00657 5.53942 3.09617 4.31806 4.31754C3 5.63559 3 7.75698 3 11.9997C3 16.2425 3 18.3639 4.31806 19.6819C5.63611 21 7.7575 21 12.0003 21C16.243 21 18.3644 21 19.6825 19.6819C20.9038 18.4606 20.9934 16.5494 21 12.8998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20.9995 6.02511L20 6.02258C16.2634 6.01313 14.3951 6.0084 13.0817 6.95247C12.6452 7.2662 12.2622 7.64826 11.9474 8.08394C11 9.39497 11 11.2633 11 14.9998M20.9995 6.02511C21.0062 5.86248 20.9481 5.69887 20.8251 5.55315C20.0599 4.64668 18.0711 2.99982 18.0711 2.99982M20.9995 6.02511C20.9934 6.17094 20.9352 6.31598 20.8249 6.44663C20.0596 7.35292 18.0711 8.99982 18.0711 8.99982" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </GlobalLink>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-6 flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ fullName }}
            </h1>
            <div class="mt-1 flex items-center space-x-4">
              <span class="text-gray-500">{{ subscriber.email }}</span>
              <Badge :variant="statusColor" class="capitalize">{{ subscriber.status }}</Badge>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <Button @click="showEditDialog = true">
              <Edit2 class="h-4 w-4 mr-2" />
              Edit
            </Button>

            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon">
                  <MoreVertical class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>

              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="showDeleteDialog = true">
                  <Trash2 class="h-4 w-4 mr-2" />
                  Delete Subscriber
                </DropdownMenuItem>

                <DropdownMenuItem @click="handleUnsubscribe">
                  <MailX class="h-4 w-4 mr-2" />
                  Unsubscribe
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <!-- Main Content -->
        <Tabs v-model="activeTab" class="space-y-4">
          <TabsList>
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="campaigns">Campaigns</TabsTrigger>
            <TabsTrigger value="activity">Activity</TabsTrigger>
          </TabsList>

          <!-- Overview Tab -->
          <TabsContent value="overview">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              <!-- Stats Cards -->
              <Card>
                <CardHeader>
                  <CardTitle>Campaign Activity</CardTitle>
                  <CardDescription>Total campaigns received</CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="text-2xl font-bold">{{ subscriber.stats.total_campaigns }}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Engagement Rates</CardTitle>
                  <CardDescription>Opens and clicks performance</CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span>Open Rate</span>
                      <span class="font-medium">{{ Math.round(subscriber.stats.open_rate * 100) }}%</span>
                    </div>
                    <div class="flex justify-between">
                      <span>Click Rate</span>
                      <span class="font-medium">{{ Math.round(subscriber.stats.click_rate * 100) }}%</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Deliverability</CardTitle>
                  <CardDescription>Bounce and spam metrics</CardDescription>
                </CardHeader>

                <CardContent>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span>Bounce Rate</span>
                      <span class="font-medium">{{ Math.round(subscriber.stats.bounce_rate * 100) }}%</span>
                    </div>
                    <div class="flex justify-between">
                      <span>Spam Rate</span>
                      <span class="font-medium">{{ Math.round(subscriber.stats.spam_rate * 100) }}%</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Charts -->
              <Card class="md:col-span-2">
                <CardHeader>
                  <CardTitle>Engagement Over Time</CardTitle>
                  <CardDescription>Campaign opens and clicks history</CardDescription>
                </CardHeader>

                <CardContent>
                  <VueApexCharts
                    type="area"
                    height="350"
                    :options="engagementChartOptions"
                    :series="engagementChartSeries"
                  />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Overall Performance</CardTitle>
                  <CardDescription>Key metrics summary</CardDescription>
                </CardHeader>
                <CardContent>
                  <VueApexCharts
                    type="radialBar"
                    height="350"
                    :options="performanceChartOptions"
                    :series="performanceChartSeries"
                  />
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <!-- Campaigns Tab -->
          <TabsContent value="campaigns">
            <Card>
              <CardHeader>
                <CardTitle>Campaign History</CardTitle>
                <CardDescription>All campaigns this subscriber has received</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div v-for="campaign in subscriber.campaigns" :key="campaign.id"
                       class="flex items-start justify-between p-4 border rounded-lg">
                    <div>
                      <h4 class="font-medium">{{ campaign.name }}</h4>
                      <p class="text-sm text-gray-500">{{ campaign.subject }}</p>
                      <div class="mt-2 text-sm">
                        <span class="text-gray-500">Sent: </span>
                        <time>{{ new Date(campaign.sent_at).toLocaleDateString() }}</time>
                      </div>
                    </div>
                    <div class="flex space-x-4">
                      <div class="text-center">
                        <Eye class="h-4 w-4 mx-auto text-gray-400" />
                        <span class="text-sm">{{ campaign.stats.opens }}</span>
                      </div>
                      <div class="text-center">
                        <MousePointerClick class="h-4 w-4 mx-auto text-gray-400" />
                        <span class="text-sm">{{ campaign.stats.clicks }}</span>
                      </div>
                      <div class="text-center">
                        <Ban class="h-4 w-4 mx-auto text-gray-400" />
                        <span class="text-sm">{{ campaign.stats.bounces }}</span>
                      </div>
                      <div class="text-center">
                        <Flag class="h-4 w-4 mx-auto text-gray-400" />
                        <span class="text-sm">{{ campaign.stats.complaints }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Activity Tab -->
          <TabsContent value="activity">
            <Card>
              <CardHeader>
                <CardTitle>Recent Activity</CardTitle>
                <CardDescription>Subscriber's interaction timeline</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div v-for="activity in subscriber.activity" :key="activity.id"
                       class="flex items-start space-x-4 p-4 border-l-2"
                       :class="{
                         'border-green-500': activity.type === 'open' || activity.type === 'click',
                         'border-red-500': activity.type === 'bounce' || activity.type === 'complaint',
                         'border-yellow-500': activity.type === 'unsubscribe'
                       }">
                    <div class="flex-shrink-0">
                      <component
                        :is="getActivityIcon(activity.type)"
                           class="h-5 w-5"
                           :class="{
                           'text-green-500': activity.type === 'open' || activity.type === 'click',
                           'text-red-500': activity.type === 'bounce' || activity.type === 'complaint',
                           'text-yellow-500': activity.type === 'unsubscribe'
                         }"
                      />
                    </div>
                    <div>
                      <p class="font-medium">
                        {{ getActivityText(activity.type) }}
                        <span class="font-normal text-gray-500">
                          in {{ activity.campaign_name }}
                        </span>
                      </p>
                      <time class="text-sm text-gray-500">
                        {{ new Date(activity.created_at).toLocaleString() }}
                      </time>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        <!-- Modals -->
        <Dialog v-model:open="showDeleteDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Delete Subscriber</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete this subscriber? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <div class="flex justify-end space-x-4">
              <Button variant="ghost" @click="showDeleteDialog = false">
                Cancel
              </Button>
              <Button variant="destructive" @click="handleDelete">
                Delete
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        <!-- Edit Subscriber Dialog -->
        <Dialog v-model:open="showEditDialog">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Edit Subscriber</DialogTitle>
              <DialogDescription>
                Update subscriber information and preferences.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-4 items-center gap-4">
                <Label class="text-right">First Name</Label>
                <Input
                  v-model="form.first_name"
                  class="col-span-3"
                  placeholder="First name"
                />
              </div>

              <div class="grid grid-cols-4 items-center gap-4">
                <Label class="text-right">Last Name</Label>
                <Input
                  v-model="form.last_name"
                  class="col-span-3"
                  placeholder="Last name"
                />
              </div>

              <div class="grid grid-cols-4 items-center gap-4">
                <Label class="text-right">Email</Label>
                <Input
                  v-model="form.email"
                  class="col-span-3"
                  type="email"
                  placeholder="Email address"
                />
              </div>

              <div class="grid grid-cols-4 items-center gap-4">
                <Label class="text-right">Company</Label>
                <Input
                  v-model="form.company"
                  class="col-span-3"
                  placeholder="Company name"
                />
              </div>

              <div class="grid grid-cols-4 items-center gap-4">
                <Label class="text-right">Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger class="col-span-3">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem value="subscribed">Subscribed</SelectItem>
                    <SelectItem value="unsubscribed">Unsubscribed</SelectItem>
                    <SelectItem value="bounced">Bounced</SelectItem>
                    <SelectItem value="complained">Complained</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <DialogFooter>
              <Button variant="ghost" @click="showEditDialog = false">
                Cancel
              </Button>

              <Button type="submit" @click="handleUpdate" :disabled="form.processing">
                Save Changes
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AppLayout>
</template>
