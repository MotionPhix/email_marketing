<script setup lang="ts">
import {ref, computed, watch} from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { format } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  IconEdit,
  IconDotsVertical,
  IconCopy,
  IconTrash,
  IconEye,
  IconPlus,
  IconSearch,
  IconFilter,
  IconDownload,
  IconMail,
  IconFile,
  IconChevronUp,
  IconChevronDown,
  IconCalendar,
  IconRefresh
} from "@tabler/icons-vue"
import type { Campaign, PaginatedResponse, CampaignFilters } from '@/types'
import {useDebounceFn} from "@vueuse/core";
import DateRangePicker from "@/Components/DateRangePicker.vue";

interface Props {
  campaigns: PaginatedResponse<Campaign>
  filters: CampaignFilters
}

const props = withDefaults(defineProps<Props>(), {
  filters: () => ({
    sort_by: 'created_at',
    sort_direction: 'desc'
  })
})

const searchQuery = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? 'all')
const sortBy = ref<keyof Campaign>(props.filters.sort_by ?? 'created_at')
const sortDirection = ref<'asc' | 'desc'>(props.filters.sort_direction ?? 'desc')
const dateRange = ref<[string, string]>([
  props.filters.date_from ?? '',
  props.filters.date_to ?? ''
])
const isConfirmingDelete = ref(false)
const campaignToDelete = ref<number | null>(null)

const statusOptions = [
  { value: 'all', label: 'All Status' },
  { value: 'draft', label: 'Draft' },
  { value: 'scheduled', label: 'Scheduled' },
  { value: 'sending', label: 'Sending' },
  { value: 'sent', label: 'Sent' },
  { value: 'failed', label: 'Failed' }
] as const

const tableColumns = {
  name: 'Campaign',
  status: 'Status',
  'stats.recipients_count': 'Recipients',
  created_at: 'Created',
  scheduled_at: 'Schedule'
} as const

const statusColors = {
  draft: 'bg-gray-100 text-gray-800',
  scheduled: 'bg-blue-100 text-blue-800',
  sending: 'bg-yellow-100 text-yellow-800',
  sent: 'bg-green-100 text-green-800',
  failed: 'bg-red-100 text-red-800'
} as const

const updateFilters = (newFilters: Partial<CampaignFilters>) => {
  router.get(route('campaigns.index'), {
    ...props.filters,
    ...newFilters
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handleSort = (column: keyof typeof tableColumns) => {
  const newDirection = sortBy.value === column && sortDirection.value === 'asc' ? 'desc' : 'asc'
  sortBy.value = column
  sortDirection.value = newDirection

  updateFilters({
    sort_by: column,
    sort_direction: newDirection
  })
}

const debouncedSearch = useDebounceFn((value: string) => {
  updateFilters({ search: value || undefined })
}, 300)

watch(searchQuery, (value) => {
  debouncedSearch(value)
})

const handleStatusChange = (status: typeof statusOptions[number]['value']) => {
  statusFilter.value = status
  updateFilters({ status: status === 'all' ? undefined : status })
}

const handleDateRangeChange = ([from, to]: [string, string]) => {
  updateFilters({
    date_from: from || undefined,
    date_to: to || undefined
  })
}

const confirmDelete = (campaignId: number) => {
  campaignToDelete.value = campaignId
  isConfirmingDelete.value = true
}

const handleDelete = () => {
  if (!campaignToDelete.value) return

  router.delete(route('campaigns.destroy', campaignToDelete.value), {
    onSuccess: () => {
      isConfirmingDelete.value = false
      campaignToDelete.value = null
    }
  })
}

const formatDate = (date: string | null) => {
  if (!date) return ''
  return format(new Date(date), 'MMM d, yyyy h:mm a')
}

const calculateStats = (stats?: Campaign['stats']) => {
  if (!stats) return { openRate: 0, clickRate: 0 }

  const deliveredCount = stats.delivered_count || 0

  return {
    openRate: deliveredCount ? ((stats.opened_count / deliveredCount) * 100).toFixed(1) : 0,
    clickRate: deliveredCount ? ((stats.clicked_count / deliveredCount) * 100).toFixed(1) : 0
  }
}

const exportCampaigns = () => {
  const params = new URLSearchParams({
    ...props.filters,
    format: 'csv'
  } as Record<string, string>).toString()

  window.location.href = `${route('campaigns.export')}?${params}`
}
</script>

<template>
  <AppLayout title="Campaigns">
    <template #header>
      Email Campaigns
    </template>

    <template #action>

      <Link
        :href="route('campaigns.create')"
        class="inline-flex items-center">
        <IconPlus class="mr-2 h-4 w-4" />
        New campaign
      </Link>

    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="mb-6 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="relative">
                <IconSearch class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <Input
                  v-model="searchQuery"
                  type="search"
                  placeholder="Search campaigns..."
                  class="pl-9 w-64"
                />
              </div>

              <Select
                v-model="statusFilter"
                @change="handleStatusChange($event.target.value)">
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>

                <SelectContent>

                  <SelectItem
                    v-for="option in statusOptions"
                    :key="option.value"
                    :value="option.value">
                    {{ option.label }}
                  </SelectItem>

                </SelectContent>
              </Select>

              <div class="flex items-center space-x-2">
                <DateRangePicker
                  v-model="dateRange"
                  placeholder="Filter by date range"
                  :min-date="new Date(2024, 0, 1)"
                  :max-date="new Date(2025, 11, 31)"
                  class="w-64"
                />
              </div>
            </div>

            <div class="flex items-center space-x-4">
              <Button
                variant="outline"
                @click="exportCampaigns">
                <IconDownload class="mr-2 h-4 w-4" />
                Export
              </Button>
            </div>
          </div>
        </div>

        <!-- Campaigns Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
              <th
                v-for="(label, column) in tableColumns"
                :key="column"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                @click="handleSort(column)">
                <div class="flex items-center space-x-1 cursor-pointer">
                  <span>{{ label }}</span>
                  <span v-if="sortBy === column" class="inline-flex">
                      <component
                        :is="sortDirection === 'asc' ? IconChevronUp : IconChevronDown"
                        class="h-4 w-4"
                      />
                    </span>
                </div>
              </th>
              <th class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="campaign in campaigns.data" :key="campaign.id">
              <td class="whitespace-nowrap px-6 py-4">
                <div class="flex items-center">
                  <component
                    :is="campaign.status === 'draft' ? IconFile : IconMail"
                    class="mr-3 h-5 w-5 text-gray-400"
                  />
                  <div>
                    <Link
                      :href="route('campaigns.show', campaign.id)"
                      class="font-medium text-gray-900 hover:text-primary"
                    >
                      {{ campaign.name }}
                    </Link>
                    <div class="text-sm text-gray-500">
                      {{ campaign.subject }}
                    </div>
                  </div>
                </div>
              </td>

              <td class="whitespace-nowrap px-6 py-4">
                  <span
                    :class="[
                      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                      statusColors[campaign.status]
                    ]"
                  >
                    {{ campaign.status }}
                  </span>
              </td>

              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                {{ campaign.stats?.recipients_count.toLocaleString() ?? 0 }} recipients
              </td>

              <td class="whitespace-nowrap px-6 py-4">
                <div v-if="campaign.status === 'sent'" class="space-y-1">
                  <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-500">Opens:</span>
                    <span class="font-medium">
                        {{ calculateStats(campaign.stats).openRate }}%
                      </span>
                  </div>
                  <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-500">Clicks:</span>
                    <span class="font-medium">
                        {{ calculateStats(campaign.stats).clickRate }}%
                      </span>
                  </div>
                </div>
                <span v-else class="text-sm text-gray-500">
                    Not available</span>
              </td>

              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                <div v-if="campaign.scheduled_at">
                  {{ formatDate(campaign.scheduled_at) }}
                </div>
                <div v-else-if="campaign.sent_at">
                  Sent: {{ formatDate(campaign.sent_at) }}
                </div>
                <div v-else>Not scheduled</div>
              </td>

              <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                      <IconDotsVertical class="h-4 w-4" />
                      <span class="sr-only">Open menu</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-[160px]">
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.show', campaign.id)">
                        <IconEye class="mr-2 h-4 w-4" />
                        View Details
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.edit', campaign.id)">
                        <IconEdit class="mr-2 h-4 w-4" />
                        Edit
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.preview', campaign.id)" target="_blank">
                        <IconEye class="mr-2 h-4 w-4" />
                        Preview
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.duplicate', campaign.id)">
                        <IconCopy class="mr-2 h-4 w-4" />
                        Duplicate
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      v-if="campaign.status === 'draft'"
                      @click="confirmDelete(campaign.id)"
                      class="text-red-600 focus:bg-red-50"
                    >
                      <IconTrash class="mr-2 h-4 w-4" />
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="campaigns.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <IconMail class="h-12 w-12 text-gray-400" />
                  <h3 class="mt-4 text-lg font-medium text-gray-900">No campaigns found</h3>
                  <p class="mt-1 text-sm text-gray-500">
                    Get started by creating a new campaign.
                  </p>
                  <div class="mt-6">
                    <Link
                      :href="route('campaigns.create')"
                      class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"
                    >
                      <IconPlus class="mr-2 h-4 w-4" />
                      New Campaign
                    </Link>
                  </div>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="campaigns.meta" class="mt-6 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ campaigns.meta.from }}</span>
            to
            <span class="font-medium">{{ campaigns.meta.to }}</span>
            of
            <span class="font-medium">{{ campaigns.meta.total }}</span>
            results
          </div>

          <div class="flex space-x-2">
            <Link
              v-for="link in campaigns.meta.links"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              :class="[
                'inline-flex h-8 min-w-[2rem] items-center justify-center rounded px-3 text-sm',
                link.active
                  ? 'bg-primary text-white'
                  : link.url
                    ? 'bg-white text-gray-500 hover:bg-gray-50'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="isConfirmingDelete" @update:open="isConfirmingDelete = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Campaign</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this campaign? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>

        <div class="mt-6 flex justify-end space-x-4">
          <Button
            variant="outline"
            @click="isConfirmingDelete = false"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            @click="handleDelete"
          >
            Delete Campaign
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.pagination-link {
  @apply inline-flex h-8 min-w-[2rem] items-center justify-center rounded px-3 text-sm;
}

.pagination-link.active {
  @apply bg-primary text-white;
}

.pagination-link:not(.active) {
  @apply bg-white text-gray-500 hover:bg-gray-50;
}

.pagination-link.disabled {
  @apply bg-gray-100 text-gray-400 cursor-not-allowed;
}
</style>
