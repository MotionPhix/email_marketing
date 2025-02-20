<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {PlusIcon} from 'lucide-vue-next'
import SegmentsGrid from './Partials/SegmentsGrid.vue'

withDefaults(
  defineProps<{
    segments: []
    filters: {}
  }>(), {
    filters: () => ({})
  }
)
</script>

<template>
  <Head title="Segments"/>

  <AppLayout title="Explore Segments">
    <template #action>
      <Link
        as="button"
        class="flex items-center gap-x-2"
        :href="route('segments.create')">
        <PlusIcon class="h-4 w-4"/>
        Create Segment
      </Link>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>Your Segments</CardTitle>
            <CardDescription>
              Create and manage subscriber segments based on specific criteria
            </CardDescription>
          </CardHeader>

          <CardContent>
            <SegmentsGrid :segments="segments"/>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
