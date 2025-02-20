<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SegmentForm from './Partials/SegmentsForm.vue'

defineProps({
  segment: {
    type: Object,
    required: true
  },
  conditions: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <Head :title="`Edit ${segment.name}`" />

  <AppLayout title="Edit Segment">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Edit Segment
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>Edit {{ segment.name }}</CardTitle>
            <CardDescription>
              Modify your segment criteria
            </CardDescription>
          </CardHeader>

          <CardContent>
            <SegmentForm
              :conditions="conditions"
              :segment="segment"
              :submit-route="route('segments.update', segment.id)"
              submit-method="put"
            />
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
