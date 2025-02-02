<script setup lang="ts">
import {ref, computed} from 'vue'
import AppLayout from "@/Layouts/AppLayout.vue"
import {router, Link} from '@inertiajs/vue3'
import {Checkbox} from "@/Components/ui/checkbox"
import {Input} from "@/Components/ui/input"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog"
import {Badge} from "@/Components/ui/badge"
import {
  Trash2Icon,
  SendIcon,
  PlusIcon,
  SearchIcon,
  EyeIcon,
  PenIcon,
  UsersIcon,
  CalendarIcon,
  MailIcon,
  EllipsisIcon,
  SortAscIcon,
} from 'lucide-vue-next'
import PageTitle from "@/Components/PageTitle.vue"
import EmptyState from "@/Components/EmptyState.vue"
import {useToast} from "maz-ui"

interface Campaign {
  id: number
  uuid: string
  title: string
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
  scheduled_at: string | null
  template_id: number | null
  audience_id: number | null
  audience_name: string
  recipients_count: number
}

interface Props {
  campaigns: {
    data: Campaign[]
    total: number
    per_page: number
    current_page: number
  }
}

const props = defineProps<Props>()
const toast = useToast()

// State
const searchQuery = ref('')
const showDeleteDialog = ref(false)
const campaignToDelete = ref<Campaign | null>(null)
const selectedCampaigns = ref(new Set<number>())
const sortBy = ref<'title' | 'status' | 'scheduled_at'>('scheduled_at')
const sortOrder = ref<'asc' | 'desc'>('desc')
const isLoading = ref(false)

// Computed
const filteredCampaigns = computed(() => {
  let filtered = [...props.campaigns.data]

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(campaign =>
      campaign.title.toLowerCase().includes(query) ||
      campaign.audience_name.toLowerCase().includes(query)
    )
  }

  filtered.sort((a, b) => {
    const modifier = sortOrder.value === 'asc' ? 1 : -1
    if (sortBy.value === 'title') {
      return a.title.localeCompare(b.title) * modifier
    }
    if (sortBy.value === 'scheduled_at') {
      return ((a.scheduled_at || '') > (b.scheduled_at || '') ? 1 : -1) * modifier
    }
    return (a.status > b.status ? 1 : -1) * modifier
  })

  return filtered
})

const allSelected = computed(() =>
  props.campaigns.data.length > 0 &&
  props.campaigns.data.every(campaign => selectedCampaigns.value.has(campaign.id))
)

const hasSelection = computed(() => selectedCampaigns.value.size > 0)

// Methods
const toggleAll = (checked: boolean) => {
  if (checked) {
    props.campaigns.data.forEach(campaign => {
      selectedCampaigns.value.add(campaign.id)
    })
  } else {
    selectedCampaigns.value.clear()
  }
}

const confirmDelete = (campaign: Campaign) => {
  campaignToDelete.value = campaign
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  if (!campaignToDelete.value) return

  try {
    isLoading.value = true
    await router.delete(route('campaigns.destroy', campaignToDelete.value.uuid))
    toast.success('Campaign deleted successfully')
    showDeleteDialog.value = false
    campaignToDelete.value = null
  } catch (error) {
    toast.error('Failed to delete campaign')
  } finally {
    isLoading.value = false
  }
}

const handleSend = async (campaign: Campaign) => {
  try {
    isLoading.value = true
    await router.post(route('campaigns.send', campaign.uuid))
    toast.success('Campaign queued for sending')
  } catch (error) {
    toast.error('Failed to send campaign')
  } finally {
    isLoading.value = false
  }
}

const getStatusBadge = (status: Campaign['status']) => {
  const statusConfig = {
    draft: {label: 'Draft', class: 'bg-muted text-muted-foreground'},
    scheduled: {label: 'Scheduled', class: 'bg-blue-500/20 text-blue-700'},
    sending: {label: 'Sending', class: 'bg-yellow-500/20 text-yellow-700'},
    sent: {label: 'Sent', class: 'bg-green-500/20 text-green-700'},
    failed: {label: 'Failed', class: 'bg-red-500/20 text-red-700'}
  }
  return statusConfig[status] || statusConfig.draft
}

const toggleSort = (field: typeof sortBy.value) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortOrder.value = 'asc'
  }
}
</script>

<template>
  <AppLayout title="Campaigns">
    <template #header>
      <PageTitle title="Campaigns"/>

      <div class="flex items-center gap-2">
        <div class="relative w-full sm:w-64">
          <SearchIcon
            class="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
          />
          <Input
            v-model="searchQuery"
            placeholder="Search campaigns..."
            class="pl-8"
          />
        </div>

        <Button
          variant="outline"
          size="icon"
          @click="toggleSort(sortBy)">
          <SortAscIcon
            class="h-4 w-4"
            :class="{ 'rotate-180': sortOrder === 'desc' }"
          />
        </Button>

        <Button
          v-if="filteredCampaigns.length"
          as-child>
          <Link :href="route('campaigns.create')">
            <PlusIcon class="h-4 w-4 mr-2"/>
            New Campaign
          </Link>
        </Button>
      </div>
    </template>

    <div class="py-6 px-4 sm:px-6 lg:px-8 my-12">
      <!-- Empty State -->
      <div v-if="!props.campaigns.total">
        <EmptyState
          title="Create your first campaign"
          description="Start reaching out to your audience with targeted email campaigns"
          :icon="MailIcon">
          <template #action>
            <Button as-child>
              <Link :href="route('campaigns.create')">
                Create Campaign
              </Link>
            </Button>
          </template>
        </EmptyState>
      </div>

      <!-- Campaigns Table -->
      <div v-else class="rounded-lg border bg-card">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[50px]">
                <Checkbox
                  :checked="allSelected"
                  @update:checked="toggleAll"
                />
              </TableHead>
              <TableHead>Campaign</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Schedule</TableHead>
              <TableHead>Recipients</TableHead>
              <TableHead class="w-[100px]" align="end"></TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="campaign in filteredCampaigns"
              :key="campaign.id"
              class="group">
              <TableCell>
                <Checkbox
                  :checked="selectedCampaigns.has(campaign.id)"
                  @update:checked="(checked) => {
                    checked
                      ? selectedCampaigns.add(campaign.id)
                      : selectedCampaigns.delete(campaign.id)
                  }"
                />
              </TableCell>

              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium">{{ campaign.title }}</span>
                  <span
                    v-if="campaign.audience_name"
                    class="text-sm text-muted-foreground">
                    {{ campaign.audience_name }}
                  </span>
                </div>
              </TableCell>

              <TableCell>
                <Badge
                  :class="getStatusBadge(campaign.status).class"
                  class="capitalize">
                  {{ getStatusBadge(campaign.status).label }}
                </Badge>
              </TableCell>

              <TableCell>
                <div class="flex items-center gap-2">
                  <CalendarIcon class="h-4 w-4 text-muted-foreground"/>
                  <span>
                    {{ campaign.scheduled_at || 'Not scheduled' }}
                  </span>
                </div>
              </TableCell>

              <TableCell>
                <div class="flex items-center gap-2">
                  <UsersIcon class="h-4 w-4 text-muted-foreground"/>
                  <span>{{ campaign.recipients_count }}</span>
                </div>
              </TableCell>

              <TableCell align="end">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="opacity-0 group-hover:opacity-100">
                      <EllipsisIcon class="h-4 w-4"/>
                    </Button>
                  </DropdownMenuTrigger>

                  <DropdownMenuContent align="end" class="w-48" :side-offset="-36">
                    <DropdownMenuGroup>
                      <DropdownMenuItem asChild>
                        <Link :href="route('campaigns.show', campaign.uuid)">
                          <EyeIcon class="h-4 w-4 mr-2"/>
                          Go to campaign
                        </Link>
                      </DropdownMenuItem>

                      <DropdownMenuItem asChild>
                        <Link :href="route('campaigns.edit', campaign.uuid)">
                          <PenIcon class="h-4 w-4 mr-2"/>
                          Edit campaign
                        </Link>
                      </DropdownMenuItem>
                    </DropdownMenuGroup>

                    <DropdownMenuSeparator/>

                    <DropdownMenuItem
                      v-if="campaign.status === 'draft' && campaign.template_id && campaign.audience_id"
                      @click="handleSend(campaign)">
                      <SendIcon class="h-4 w-4 mr-2"/>
                      Send campaign
                    </DropdownMenuItem>

                    <DropdownMenuItem
                      @click="confirmDelete(campaign)"
                      class="text-destructive">
                      <Trash2Icon class="h-4 w-4 mr-2"/>
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Batch Actions Bar -->
      <div
        v-if="hasSelection"
        class="fixed bottom-0 left-0 right-0 bg-background border-t p-4 flex items-center justify-between z-50">
        <span class="text-sm font-medium">
          {{ selectedCampaigns.size }} campaigns selected
        </span>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="selectedCampaigns.clear()">
            Clear Selection
          </Button>
          <Button
            variant="destructive"
            size="sm"
            @click="confirmDelete(null)">
            Delete Selected
          </Button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>Delete Campaign</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete
            {{
              campaignToDelete
                ? `"${campaignToDelete.title}"`
                : `${selectedCampaigns.size} campaigns`
            }}?
            This action cannot be undone.
          </DialogDescription>
        </DialogHeader>

        <DialogFooter>
          <Button
            variant="outline"
            @click="showDeleteDialog = false">
            Cancel
          </Button>
          <Button
            variant="destructive"
            :disabled="isLoading"
            @click="handleDelete">
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
