<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { IconPlus } from '@tabler/icons-vue'
import CampaignList from './Components/CampaignList.vue'
import type { Campaign } from '@/types'
import AppLayout from "@/Layouts/AppLayout.vue";
import {toast} from "vue-sonner";

interface Props {
  campaigns: {
    data: Campaign[]
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  filters: {
    search: string
    status: string
    date_from: string
    date_to: string
    sort_by: string
    sort_direction: 'asc' | 'desc'
  }
}

const props = defineProps<Props>()
const selectedCampaigns = ref<number[]>([])
const isDeleteDialogOpen = ref(false)
const campaignsToDelete = ref<number[]>([])

const handleFilter = (newFilters: Partial<Props['filters']>) => {
  router.get(
    route('campaigns.index'),
    { ...props.filters, ...newFilters },
    { preserveState: true, preserveScroll: true }
  )
}

const confirmDelete = (ids: number | number[]) => {
  campaignsToDelete.value = Array.isArray(ids) ? ids : [ids]
  isDeleteDialogOpen.value = true
}

const handleDelete = () => {
  router.delete(
    route('campaigns.bulk-delete'),
    {
      data: { ids: campaignsToDelete.value },
      onSuccess: () => {
        isDeleteDialogOpen.value = false
        campaignsToDelete.value = []
        selectedCampaigns.value = []
        toast({
          title: 'Success',
          description: 'Selected campaigns have been deleted.',
        })
      },
      onError: () => {
        toast({
          title: 'Error',
          description: 'There was an error deleting the campaigns.',
          variant: 'destructive'
        })
      }
    }
  )
}

const handleExport = () => {
  const params = new URLSearchParams(props.filters as any)
  const queryString = params.toString()

  window.location.href = route(
    'campaigns.export',
    queryString ? `?${queryString}` : ''
  )
}
</script>

<template>
  <AppLayout
    title="Email Campaigns"
    container-size="md">
    <template #action>
      <Button asChild>
        <Link :href="route('campaigns.create')">
          <IconPlus class="mr-2 h-4 w-4" />
          New Campaign
        </Link>
      </Button>
    </template>

    <CampaignList
      :campaigns="campaigns.data"
      :selected="selectedCampaigns"
      :filters="filters"
      @update:selected="selectedCampaigns = $event"
      @delete="confirmDelete"
      @filter="handleFilter"
      @export="handleExport"
    />

    <div v-if="campaigns.total > 0" class="mt-4">
      <Pagination
        :links="campaigns.links"
        :meta="campaigns.meta"
      />
    </div>

    <Dialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Campaign{{ campaignsToDelete.length > 1 ? 's' : '' }}</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete {{ campaignsToDelete.length > 1 ? 'these campaigns' : 'this campaign' }}?
            This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button
            variant="outline"
            @click="isDeleteDialogOpen = false"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            @click="handleDelete"
          >
            Delete {{ campaignsToDelete.length > 1 ? 'Campaigns' : 'Campaign' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
