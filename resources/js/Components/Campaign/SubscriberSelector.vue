<script setup lang="ts">
import {onMounted, ref, watch} from 'vue'
import { IconCheck, IconCircleCheck, IconMailOpened, IconFilterCog } from '@tabler/icons-vue'
import {PlusIcon} from "lucide-vue-next";
import axios from "axios";
import MultipleCombobox from "@/Components/MultipleCombobox.vue";

interface MailingList {
  id: number
  name: string
  subscriberCount: number
  description?: string
}

interface Segment {
  id: number
  name: string
  subscriberCount: number
  description?: string
}

interface Props {
  modelValue: {
    mailingLists: MailingList[]
    segments: Segment[]
    excludedLists: MailingList[]
  }
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => ({
    mailingLists: [],
    segments: [],
    excludedLists: []
  })
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: Props['modelValue']): void
  (e: 'create-list'): void
  (e: 'create-segment'): void
}>()

const mailingLists = ref<MailingList[]>([])
const segments = ref<Segment[]>([])

// Change these to store IDs instead of full objects
const selectedListIds = ref<number[]>([])
const selectedSegmentIds = ref<number[]>([])
const excludedListIds = ref<number[]>([])

// Initialize with IDs from props if available
onMounted(() => {
  // Use optional chaining and provide default empty arrays
  selectedListIds.value = props.modelValue?.mailingLists?.map(list => list.id) || []
  selectedSegmentIds.value = props.modelValue?.segments?.map(segment => segment.id) || []
  excludedListIds.value = props.modelValue?.excludedLists?.map(list => list.id) || []
})

const fetchLists = async (search?: string) => {
  try {
    const response = await axios.get(route('api.mailing-lists.index'), {
      params: { search }
    })
    mailingLists.value = response.data.lists
  } catch (error) {
    console.error('Failed to fetch mailing lists:', error)
  }
}

const fetchSegments = async (search?: string) => {
  try {
    const response = await axios.get(route('api.segments.index'), {
      params: { search }
    })
    segments.value = response.data.segments
  } catch (error) {
    console.error('Failed to fetch segments:', error)
  }
}

const navigateToCreateList = () => {
  emit('create-list')
}

const navigateToCreateSegment = () => {
  emit('create-segment')
}

// Watch for changes and emit updates with full objects
watch([selectedListIds, selectedSegmentIds, excludedListIds], () => {
  emit('update:modelValue', {
    mailingLists: mailingLists.value.filter(list => selectedListIds.value.includes(list.id)),
    segments: segments.value.filter(segment => selectedSegmentIds.value.includes(segment.id)),
    excludedLists: mailingLists.value.filter(list => excludedListIds.value.includes(list.id)),
  })
}, { deep: true })

// Fetch lists and segments on mount
onMounted(async () => {
  fetchLists()
  fetchSegments()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Lists Selection -->
    <div class="space-y-4">
      <Label>Mailing Lists</Label>
      <div v-if="mailingLists.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="list in mailingLists"
          :key="list.id"
          :class="[
            'cursor-pointer transition-colors hover:border-primary',
            selectedListIds.includes(list.id) ? 'border-primary bg-primary/5' : ''
          ]"
          @click="
            selectedListIds.includes(list.id)
              ? selectedListIds = selectedListIds.filter(id => id !== list.id)
              : selectedListIds.push(list.id)
          ">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium">{{ list.name }}</h4>

                <p class="text-sm text-muted-foreground">
                  {{ list.subscriberCount }} subscribers
                </p>

                <p v-if="list.description" class="mt-1 text-xs text-muted-foreground">
                  {{ list.description }}
                </p>
              </div>

              <IconCheck
                v-if="selectedListIds.includes(list.id)"
                class="h-5 w-5 text-primary"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="rounded-lg border border-dashed p-8 text-center">
        <div class="mx-auto flex max-w-[420px] flex-col items-center justify-center text-center">
          <IconMailOpened class="h-10 w-10 text-muted-foreground" />

          <h3 class="mt-4 text-lg font-semibold">No mailing lists</h3>

          <p class="mt-2 text-sm text-muted-foreground">
            You haven't created any mailing lists yet. Lists help you organize your subscribers into groups.
          </p>

          <Button
            variant="outline"
            class="mt-4"
            @click="navigateToCreateList">
            <PlusIcon class="h-4 w-4" />
            Create your first list
          </Button>
        </div>
      </div>
    </div>

    <!-- Segments Selection -->
    <div class="space-y-4">
      <Label>Segments</Label>

      <div v-if="segments.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="segment in segments"
          :key="segment.id"
          :class="[
            'cursor-pointer transition-colors hover:border-primary',
            selectedSegmentIds.includes(segment.id) ? 'border-primary bg-primary/5' : ''
          ]"
          @click="
            selectedSegmentIds.includes(segment.id)
              ? selectedSegmentIds = selectedSegmentIds.filter(id => id !== segment.id)
              : selectedSegmentIds.push(segment.id)
          ">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium">{{ segment.name }}</h4>

                <p class="text-sm text-muted-foreground">
                  {{ segment.subscriberCount }} subscribers
                </p>

                <p v-if="segment.description" class="mt-1 text-xs text-muted-foreground">
                  {{ segment.description }}
                </p>
              </div>

              <IconCircleCheck
                v-if="selectedSegmentIds.includes(segment.id)"
                class="h-5 w-5 text-primary"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="rounded-lg border border-dashed p-8 text-center">
        <div class="mx-auto flex max-w-[420px] flex-col items-center justify-center text-center">
          <IconFilterCog class="h-10 w-10 text-muted-foreground" />

          <h3 class="mt-4 text-lg font-semibold">No segments defined</h3>
          <p class="mt-2 text-sm text-muted-foreground">
            Create segments to filter subscribers based on their properties or behavior.
          </p>

          <Button
            variant="outline"
            class="mt-4"
            @click="navigateToCreateSegment">
            <PlusIcon class="h-4 w-4" />
            Create your first segment
          </Button>
        </div>
      </div>
    </div>

    <!-- Exclusion Lists -->
    <div class="space-y-4">
      <Label>Exclude Lists (Optional)</Label>
      <MultipleCombobox
        v-model="excludedListIds"
        :options="mailingLists
          .filter(list => !selectedListIds.includes(list.id))
          .map(list => ({
            value: list.id,
            label: list.name
          }))"
        placeholder="Select lists to exclude"
        search-placeholder="Search lists..."
        empty-message="No lists available"
        :trigger-text="selected =>
          selected.length
            ? `${selected.length} list${selected.length === 1 ? '' : 's'} excluded`
            : 'Select lists to exclude'"
      />
    </div>
  </div>
</template>
