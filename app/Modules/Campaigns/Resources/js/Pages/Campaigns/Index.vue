<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import type { CampaignsPageProps } from '../../types'
import AppLayout from '@/Layouts/AppLayout.vue'
import CampaignsTable from './Components/CampaignsTable.vue'
import CampaignsStats from './Components/CampaignsStats.vue'
import { Input } from '@/Components/ui/input'

const props = defineProps<CampaignsPageProps>()

const search = ref('')
</script>

<template>
  <AppLayout title="Campaigns">
    <Head title="Campaigns" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
          <h2 class="text-xl font-semibold">Campaigns</h2>
          <Link
            href="/campaigns/create"
            class="inline-flex items-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90"
          >
            Create Campaign
          </Link>
        </div>

        <CampaignsStats :stats="stats" class="mb-6" />

        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <CardTitle>Your Campaigns</CardTitle>
                <CardDescription>
                  Manage and track all your email campaigns
                </CardDescription>
              </div>

              <div class="w-[300px]">
                <Input
                  v-model="search"
                  placeholder="Search campaigns..."
                />
              </div>
            </div>
          </CardHeader>

          <CardContent>
            <CampaignsTable
              :campaigns="campaigns.data"
              :search="search"
            />
          </CardContent>

          <!-- Pagination -->
          <CardFooter>
            <div class="flex items-center justify-between">
              <p class="text-sm text-muted-foreground">
                Showing {{ campaigns.data.length }} of {{ campaigns.total }} campaigns
              </p>

              <div class="space-x-2">
                <Button
                  :disabled="campaigns.current_page === 1"
                  variant="outline"
                  @click="$inertia.get(`/campaigns?page=${campaigns.current_page - 1}`)"
                >
                  Previous
                </Button>
                <Button
                  :disabled="campaigns.current_page === campaigns.last_page"
                  variant="outline"
                  @click="$inertia.get(`/campaigns?page=${campaigns.current_page + 1}`)"
                >
                  Next
                </Button>
              </div>
            </div>
          </CardFooter>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
