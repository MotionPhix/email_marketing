<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { format } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'
import {IconEdit, IconDotsVertical} from "@tabler/icons-vue";

interface Campaign {
  id: number
  name: string
  subject: string
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
  scheduled_at: string | null
  sent_at: string | null
  open_rate: number
  click_rate: number
  recipient_count: number
}

const campaigns = ref<Campaign[]>([])
const isLoading = ref(true)

const statusColors = {
  draft: 'bg-gray-100 text-gray-800',
  scheduled: 'bg-blue-100 text-blue-800',
  sending: 'bg-yellow-100 text-yellow-800',
  sent: 'bg-green-100 text-green-800',
  failed: 'bg-red-100 text-red-800',
}

const fetchCampaigns = async () => {
  try {
    const response = await fetch('/api/campaigns')
    campaigns.value = await response.json()
  } catch (error) {
    console.error('Failed to fetch campaigns:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchCampaigns)
</script>

<template>
  <AppLayout title="Campaigns">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Email Campaigns
        </h2>
        <Link
          :href="route('campaigns.create')"
          class="button-primary"
        >
          <Icon name="plus" class="mr-2 h-4 w-4" />
          New Campaign
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Filters and Search -->
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Input
              type="search"
              placeholder="Search campaigns..."
              class="w-64"
            >
              <template #prefix>
                <Icon name="search" class="h-4 w-4 text-gray-400" />
              </template>
            </Input>
            <Select class="w-40">
              <option value="all">All Status</option>
              <option value="draft">Draft</option>
              <option value="scheduled">Scheduled</option>
              <option value="sending">Sending</option>
              <option value="sent">Sent</option>
              <option value="failed">Failed</option>
            </Select>
          </div>
          <div class="flex items-center space-x-4">
            <Button variant="outline">
              <Icon name="filter" class="mr-2 h-4 w-4" />
              Filter
            </Button>
            <Button variant="outline">
              <Icon name="download" class="mr-2 h-4 w-4" />
              Export
            </Button>
          </div>
        </div>

        <!-- Campaigns Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                Campaign
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                Recipients
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                Performance
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                Schedule
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="campaign in campaigns" :key="campaign.id">
              <td class="whitespace-nowrap px-6 py-4">
                <div class="flex items-center">
                  <Icon
                    :name="campaign.status === 'draft' ? 'file' : 'mail'"
                    class="mr-3 h-5 w-5 text-gray-400"
                  />
                  <div>
                    <div class="font-medium text-gray-900">
                      {{ campaign.name }}
                    </div>
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
                      statusColors[campaign.status],
                    ]"
                  >
                    {{ campaign.status }}
                  </span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                {{ campaign.recipient_count.toLocaleString() }} recipients
              </td>
              <td class="whitespace-nowrap px-6 py-4">
                <div v-if="campaign.status === 'sent'" class="space-y-1">
                  <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-500">Opens:</span>
                    <span class="font-medium">{{ campaign.open_rate }}%</span>
                  </div>
                  <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-500">Clicks:</span>
                    <span class="font-medium">{{ campaign.click_rate }}%</span>
                  </div>
                </div>
                <span v-else class="text-sm text-gray-500">
                    Not available
                  </span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                <div v-if="campaign.scheduled_at">
                  {{ format(new Date(campaign.scheduled_at), 'MMM d, yyyy h:mm a') }}
                </div>
                <div v-else-if="campaign.sent_at">
                  Sent: {{ format(new Date(campaign.sent_at), 'MMM d, yyyy h:mm a') }}
                </div>
                <div v-else>Not scheduled</div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                      <IconDotsVertical class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.edit', campaign.id)">
                        <IconEdit class="mr-2 h-4 w-4" />
                        Edit
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.preview', campaign.id)">
                        <Icon name="eye" class="mr-2 h-4 w-4" />
                        Preview
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.duplicate', campaign.id)">
                        <Icon name="copy" class="mr-2 h-4 w-4" />
                        Duplicate
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      v-if="campaign.status === 'draft'"
                      @click="deleteCampaign(campaign.id)"
                      class="text-red-600"
                    >
                      <Icon name="trash" class="mr-2 h-4 w-4" />
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
