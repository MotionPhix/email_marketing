<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Icon } from '@tabler/icons-vue'

interface Variable {
  key: string
  label: string
  description?: string
  example?: string
  category: string
}

const props = defineProps<{
  onInsert: (variable: string) => void
}>()

const variables = ref<Variable[]>([
  // Subscriber variables
  {
    key: 'subscriber.first_name',
    label: 'First Name',
    description: 'Subscriber\'s first name',
    example: 'John',
    category: 'subscriber'
  },
  {
    key: 'subscriber.last_name',
    label: 'Last Name',
    description: 'Subscriber\'s last name',
    example: 'Doe',
    category: 'subscriber'
  },
  {
    key: 'subscriber.email',
    label: 'Email',
    description: 'Subscriber\'s email address',
    example: 'john.doe@example.com',
    category: 'subscriber'
  },

  // Index variables
  {
    key: 'campaign.name',
    label: 'Index Name',
    description: 'Name of the current campaign',
    category: 'campaign'
  },
  {
    key: 'campaign.subject',
    label: 'Subject Line',
    description: 'Email subject line',
    category: 'campaign'
  },

  // System variables
  {
    key: 'system.date',
    label: 'Current Date',
    description: 'Today\'s date',
    example: 'February 14, 2025',
    category: 'system'
  },
  {
    key: 'system.year',
    label: 'Current Year',
    description: 'Current year',
    example: '2025',
    category: 'system'
  },
  {
    key: 'system.unsubscribe_url',
    label: 'Unsubscribe URL',
    description: 'Link to unsubscribe from emails',
    category: 'system'
  },
  {
    key: 'system.web_version_url',
    label: 'Web Version URL',
    description: 'Link to view email in browser',
    category: 'system'
  },
])

const selectedCategory = ref('all')
const searchQuery = ref('')

const categories = [
  { id: 'all', label: 'All Variables' },
  { id: 'subscriber', label: 'Subscriber' },
  { id: 'campaign', label: 'Index' },
  { id: 'system', label: 'System' },
]

const filteredVariables = computed(() => {
  return variables.value.filter(variable => {
    const matchesCategory = selectedCategory.value === 'all' ||
      variable.category === selectedCategory.value
    const matchesSearch = variable.label.toLowerCase()
        .includes(searchQuery.value.toLowerCase()) ||
      variable.key.toLowerCase()
        .includes(searchQuery.value.toLowerCase())
    return matchesCategory && matchesSearch
  })
})

const insertVariable = (variable: Variable) => {
  props.onInsert(`{{${variable.key}}}`)
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Search variables..."
        class="w-full"
      >
        <template #prefix>
          <Icon name="search" class="h-4 w-4 text-muted-foreground" />
        </template>
      </Input>

      <Select v-model="selectedCategory">
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
        >
          {{ category.label }}
        </option>
      </Select>
    </div>

    <div class="grid gap-2">
      <div
        v-for="variable in filteredVariables"
        :key="variable.key"
        class="group relative rounded-lg border p-4 hover:bg-muted/50"
      >
        <div class="flex items-start justify-between">
          <div>
            <h4 class="font-medium">{{ variable.label }}</h4>
            <p class="text-sm text-muted-foreground">
              {{ variable.description }}
            </p>
            <code class="mt-1 text-xs text-muted-foreground">
              {{`{{${variable.key}}}`}}
            </code>
            <div
              v-if="variable.example"
              class="mt-2 text-xs text-muted-foreground"
            >
              Example: {{ variable.example }}
            </div>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="opacity-0 group-hover:opacity-100"
            @click="insertVariable(variable)"
          >
            <Icon name="plus" class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
