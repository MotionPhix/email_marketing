<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { UsersIcon, Trash2Icon, CopyIcon, PencilIcon, PlusIcon } from 'lucide-vue-next'
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";

const props = defineProps({
  segments: {
    type: Array,
    required: true
  }
})

const showDeleteDialog = ref(false)
const selectedSegment = ref(null)

const confirmDelete = (segment) => {
  selectedSegment.value = segment
  showDeleteDialog.value = true
}

const handleDelete = () => {
  router.delete(route('segments.destroy', selectedSegment.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteDialog.value = false
      selectedSegment.value = null
    }
  })
}

const duplicate = (segment) => {
  router.post(route('segments.duplicate', segment.id))
}
</script>

<template>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <Card
      v-for="segment in segments"
      :key="segment.id"
      class="relative">
      <CardHeader>
        <CardTitle>{{ segment.name }}</CardTitle>
        <CardDescription>
          {{ segment.description }}
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="flex items-center text-sm text-muted-foreground">
          <UsersIcon class="mr-2 h-4 w-4" />
          {{ segment.subscriber_count }} subscribers
        </div>

        <div class="mt-4 space-x-2">
          <Button
            variant="ghost"
            size="sm"
            :href="route('segments.edit', segment.id)">
            <PencilIcon class="mr-2 h-4 w-4" />
            Edit
          </Button>

          <Button
            variant="ghost"
            size="sm"
            @click="duplicate(segment)">
            <CopyIcon class="mr-2 h-4 w-4" />
            Duplicate
          </Button>

          <Button
            variant="ghost"
            size="sm"
            class="text-destructive"
            @click="confirmDelete(segment)">
            <Trash2Icon class="mr-2 h-4 w-4" />
            Delete
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <div
      v-if="segments.length === 0"
      class="col-span-full flex h-[300px] items-center justify-center rounded-lg border-2 border-dashed">
      <div class="text-center">
        <UsersIcon class="mx-auto h-12 w-12 text-muted-foreground" />
        <h3 class="mt-2 text-sm font-medium">No segments</h3>
        <p class="mt-1 text-sm text-muted-foreground">
          Get started by creating a new segment
        </p>
        <div class="mt-6">
          <Button :href="route('segments.create')">
            <PlusIcon class="mr-2 h-4 w-4" />
            New Segment
          </Button>
        </div>
      </div>
    </div>
  </div>

  <DeleteConfirmationDialog
    :show="showDeleteDialog"
    :title="`Delete ${selectedSegment?.name}`"
    :description="'Are you sure you want to delete this segment? This action cannot be undone.'"
    @confirm="handleDelete"
    @close="showDeleteDialog = false"
  />
</template>
