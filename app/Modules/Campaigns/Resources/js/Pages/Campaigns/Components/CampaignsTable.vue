<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import type { Campaign } from '../../../types'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { Badge } from '@/Components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/Components/ui/table'
import { formatDistance } from 'date-fns'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps<{
  campaigns: Campaign[]
  search: string
}>()

const { toast } = useToast()

const filteredCampaigns = computed(() => {
  if (!props.search) return props.campaigns

  const searchLower = props.search.toLowerCase()
  return props.campaigns.filter(campaign =>
    campaign.name.toLowerCase().includes(searchLower) ||
    campaign.subject.toLowerCase().includes(searchLower)
  )
})

const getStatusBadgeVariant = (status: Campaign['status']) => {
  switch (status) {
    case 'draft':
      return 'secondary'
    case 'scheduled':
      return 'warning'
    case 'sending':
      return 'default'
    case 'sent':
      return 'success'
    case 'failed':
      return 'destructive'
    default:
      return 'outline'
  }
}

const deleteCampaign = (campaign: Campaign) => {
  if (campaign.status !== 'draft' && campaign.status !== 'scheduled') {
    toast({
      title: 'Error',
      description: 'Only draft or scheduled campaigns can be deleted.',
      variant: 'destructive',
    })
    return
  }

  if (confirm('Are you sure you want to delete this campaign?')) {
    window.Inertia.delete(route('campaigns.destroy', campaign.uuid))
  }
}
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Name</TableHead>
        <TableHead>Status</TableHead>
        <TableHead>Recipients</TableHead>
        <TableHead>Open Rate</TableHead>
        <TableHead>Click Rate</TableHead>
        <TableHead>Scheduled</TableHead>
        <TableHead class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="campaign in filteredCampaigns" :key="campaign.uuid">
        <TableCell>
          <div>
            <p class="font-medium">{{ campaign.name }}</p>
            <p class="text-sm text-muted-foreground">{{ campaign.subject }}</p>
          </div>
        </TableCell>
        <TableCell>
          <Badge :variant="getStatusBadgeVariant(campaign.status)">
            {{ campaign.status }}
          </Badge>
        </TableCell>
        <TableCell>
          <div class="space-y-1">
            <p class="text-sm font-medium">{{ campaign.total_recipients }}</p>
            <Progress
              :value="(campaign.sent_count / campaign.total_recipients) * 100"
              class="h-2 w-[60px]"
            />
          </div>
        </TableCell>
        <TableCell>
          {{ ((campaign.opened_count / campaign.sent_count) * 100 || 0).toFixed(1) }}%
        </TableCell>
        <TableCell>
          {{ ((campaign.clicked_count / campaign.sent_count) * 100 || 0).toFixed(1) }}%
        </TableCell>
        <TableCell>
          <span v-if="campaign.scheduled_at">
            {{ formatDistance(new Date(campaign.scheduled_at), new Date(), { addSuffix: true }) }}
          </span>
          <span v-else>-</span>
        </TableCell>
        <TableCell class="text-right">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" size="icon">
                <MoreVerticalIcon class="h-4 w-4" />
                <span class="sr-only">Open menu</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem asChild>
                <Link :href="route('campaigns.preview', campaign.uuid)">
                  Preview
                </Link>
              </DropdownMenuItem>
              <DropdownMenuItem
                v-if="campaign.status === 'draft' || campaign.status === 'scheduled'"
                asChild
              >
                <Link :href="route('campaigns.edit', campaign.uuid)">
                  Edit
                </Link>
              </DropdownMenuItem>
              <DropdownMenuItem
                v-if="campaign.status === 'scheduled'"
                @click="$inertia.post(route('campaigns.cancel', campaign.uuid))"
              >
                Cancel Schedule
              </DropdownMenuItem>
              <DropdownMenuItem
                v-if="campaign.status === 'draft' || campaign.status === 'scheduled'"
                class="text-red-600"
                @click="deleteCampaign(campaign)"
              >
                Delete
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </TableCell>
      </TableRow>
      <TableRow v-if="filteredCampaigns.length === 0">
        <TableCell colspan="7" class="text-center py-8">
          <div class="text-muted-foreground">
            {{ search ? 'No campaigns found' : 'No campaigns created yet' }}
          </div>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
