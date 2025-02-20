<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon } from 'lucide-vue-next'
import MailingListsTable from './Partials/MailingListsTable.vue'

defineProps({
  lists: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <Head title="Mailing Lists" />

  <AppLayout title="Mailing Lists">
    <template #action>
      <GlobalLink
        as="Button"
        :href="route('mailing-lists.create')">
        <PlusIcon class="h-4 w-4" />
        Create List
      </GlobalLink>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>Your Mailing Lists</CardTitle>
            <CardDescription>
              Manage your subscriber lists and their settings
            </CardDescription>
          </CardHeader>

          <CardContent>
            <MailingListsTable :lists="lists" />
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
