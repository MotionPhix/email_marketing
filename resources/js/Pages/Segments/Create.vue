<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SegmentsForm from "@/Pages/Segments/Partials/SegmentsForm.vue";

defineProps<{
  conditions: {}
}>()
</script>

<template>
  <Head title="Create Segment" />

  <AppLayout title="Create Segment">

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>New Segment</CardTitle>
            <CardDescription>
              Define your segment criteria to filter subscribers
            </CardDescription>
          </CardHeader>

          <CardContent>
            <SegmentsForm
              :conditions="conditions"
              :segment="{
                name: '',
                description: '',
                conditions: []
              }"
              submit-route="segments.store"
              submit-method="post"
            />
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
