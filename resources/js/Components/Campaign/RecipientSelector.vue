<script setup lang="ts">
import { ref, watch } from 'vue'
import type { CampaignDraft } from '@/types/campaign'

const props = defineProps<{
  modelValue: CampaignDraft['recipients']
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: CampaignDraft['recipients']): void
}>()

const lists = ref<{ id: string; name: string; subscriberCount: number }[]>([])
const segments = ref<{ id: string; name: string; subscriberCount: number }[]>([])

const selectedLists = ref(props.modelValue.lists || [])
const selectedSegments = ref(props.modelValue.segments || [])
const excludedLists = ref(props.modelValue.excludedLists || [])

// Fetch lists and segments on mount
const fetchLists = async () => {
  try {
    const response = await fetch('/api/lists')
    lists.value = await response.json()
  } catch (error) {
    console.error('Failed to fetch lists:', error)
  }
}

const fetchSegments = async () => {
  try {
    const response = await fetch('/api/segments')
    segments.value = await response.json()
  } catch (error) {
    console.error('Failed to fetch segments:', error)
  }
}

// Watch for changes and emit updates
watch([selectedLists, selectedSegments, excludedLists], () => {
  emit('update:modelValue', {
    lists: selectedLists.value,
    segments: selectedSegments.value,
    excludedLists: excludedLists.value,
  })
}, { deep: true })

// Initialize
fetchLists()
fetchSegments()
</script>

<template>
  <div class="space-y-6">
    <!-- Lists Selection -->
    <div class="space-y-4">
      <Label>Mailing Lists</Label>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="list in lists"
          :key="list.id"
          :class="[
            'cursor-pointer transition-colors hover:border-primary',
            selectedLists.includes(list.id) ? 'border-primary bg-primary/5' : ''
          ]"
          @click="
            selectedLists.includes(list.id)
              ? selectedLists = selectedLists.filter(id => id !== list.id)
              : selectedLists.push(list.id)
          "
        >
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium">{{ list.name }}</h4>
                <p class="text-sm text-muted-foreground">
                  {{ list.subscriberCount }} subscribers
                </p>
              </div>
              <Icon
                v-if="selectedLists.includes(list.id)"
                name="check-circle"
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
          "
        >
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium">{{ segment.name }}</h4>
                <p class="text-sm text-muted-foreground">
                  {{ segment.subscriberCount }} subscribers
                </p>
              </div>
              <Icon
                v-if="selectedSegments.includes(segment.id)"
                name="check-circle"
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
        v-model="excludedLists"
        multiple
        :options="lists.filter(list => !selectedLists.includes(list.id))"
        option-label="name"
        option-value="id"
      >
        <template #trigger>
          <Button variant="outline" class="w-full justify-start">
            {{ excludedLists.length ? `${excludedLists.length} lists excluded` : 'Select lists to exclude' }}
          </Button>
        </template>
      </Select>
    </div>
  </div>
</template>
