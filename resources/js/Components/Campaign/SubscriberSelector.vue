<script setup lang="ts">
import {onMounted, ref, watch} from 'vue'
import { IconCheck, IconCircleCheck } from '@tabler/icons-vue'
import {router} from "@inertiajs/vue3";
import axios from "axios";

interface Props {
  modelValue: {
    mailingLists: number[]
    segments: number[]
    excludedLists: number[]
  }
  error?: string
}

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

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => ({
    mailingLists: [],
    segments: [],
    excludedLists: []
  })
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: Props['modelValue']): void
}>()

const mailingLists = ref<MailingList[]>([])
const segments = ref<Segment[]>([])

const selectedLists = ref(props.modelValue.mailingLists)
const selectedSegments = ref(props.modelValue.segments)
const excludedLists = ref(props.modelValue.excludedLists)

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

// Watch for changes and emit updates
watch([selectedLists, selectedSegments, excludedLists], () => {
  emit('update:modelValue', {
    mailingLists: selectedLists.value,
    segments: selectedSegments.value,
    excludedLists: excludedLists.value,
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
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="list in mailingLists"
          :key="list.id"
          :class="[
            'cursor-pointer transition-colors hover:border-primary',
            selectedLists.includes(list.id) ? 'border-primary bg-primary/5' : ''
          ]"
          @click="
            selectedLists.includes(list.id)
              ? selectedLists = selectedLists.filter(id => id !== list.id)
              : selectedLists.push(list.id)
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
                v-if="selectedLists.includes(list.id)"
                class="h-5 w-5 text-primary"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Segments Selection -->
    <div class="space-y-4">
      <Label>Segments</Label>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="segment in segments"
          :key="segment.id"
          :class="[
            'cursor-pointer transition-colors hover:border-primary',
            selectedSegments.includes(segment.id) ? 'border-primary bg-primary/5' : ''
          ]"
          @click="
            selectedSegments.includes(segment.id)
              ? selectedSegments = selectedSegments.filter(id => id !== segment.id)
              : selectedSegments.push(segment.id)
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
                v-if="selectedSegments.includes(segment.id)"
                class="h-5 w-5 text-primary"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Exclusion Lists -->
    <div class="space-y-4">
      <Label>Exclude Lists (Optional)</Label>
      <Select
        multiple
        v-model="excludedLists"
        :options="mailingLists.filter(list => !selectedLists.includes(list.id))"
        option-label="name"
        option-value="id">
        <template #trigger>
          <Button variant="outline" class="w-full justify-start">
            {{ excludedLists.length ? `${excludedLists.length} lists excluded` : 'Select lists to exclude' }}
          </Button>
        </template>
      </Select>
    </div>
  </div>
</template>
