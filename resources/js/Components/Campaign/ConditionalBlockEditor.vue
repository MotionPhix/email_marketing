<script setup lang="ts">
import { ref, computed } from 'vue'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Card } from '@/Components/ui/card'

interface Condition {
  variable: string
  operator: string
  value: string
}

interface Props {
  modelValue?: string
  onInsert?: (content: string) => void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  onInsert: undefined
})

const emit = defineEmits(['update:modelValue', 'insert'])

const condition = ref<Condition>({
  variable: '',
  operator: '==',
  value: '',
})

const operators = [
  { value: '==', label: 'Equals' },
  { value: '!=', label: 'Not equals' },
  { value: '>', label: 'Greater than' },
  { value: '<', label: 'Less than' },
  { value: 'contains', label: 'Contains' },
  { value: 'startsWith', label: 'Starts with' },
  { value: 'endsWith', label: 'Ends with' }
]

const variables = [
  { value: 'subscriber.first_name', label: 'First Name' },
  { value: 'subscriber.last_name', label: 'Last Name' },
  { value: 'subscriber.email', label: 'Email' },
  { value: 'subscriber.total_purchases', label: 'Total Purchases' },
  { value: 'subscriber.last_purchase_date', label: 'Last Purchase Date' },
  { value: 'subscriber.subscription_type', label: 'Subscription Type' },
  { value: 'subscriber.tags', label: 'Tags' },
  { value: 'subscriber.custom_fields', label: 'Custom Fields' }
]

const previewContent = computed(() => {
  if (!condition.value.variable || !condition.value.value) {
    return ''
  }

  return `{% if ${condition.value.variable} ${condition.value.operator} "${condition.value.value}" %}
  <div class="conditional-content true">
    Content when condition is true
  </div>
{% else %}
  <div class="conditional-content false">
    Content when condition is false
  </div>
{% endif %}`
})

const insertCondition = () => {
  if (!condition.value.variable || !condition.value.value) {
    return
  }

  const content = previewContent.value

  if (props.onInsert) {
    props.onInsert(content)
  } else {
    emit('insert', content)
  }

  // Reset form after insertion
  condition.value = {
    variable: '',
    operator: '==',
    value: ''
  }
}

const isValid = computed(() => {
  return condition.value.variable &&
    condition.value.operator &&
    condition.value.value.length > 0
})
</script>

<template>
  <Card class="p-4">
    <div class="space-y-4">
      <h3 class="text-lg font-medium">Add Conditional Content</h3>
      <p class="text-sm text-muted-foreground">
        Create dynamic content that changes based on subscriber data
      </p>

      <div class="grid gap-4">
        <div class="grid gap-2">
          <Label>If</Label>
          <Select
            v-model="condition.variable"
            placeholder="Select variable"
          >
            <SelectTrigger>
              <SelectValue :placeholder="condition.variable || 'Select a variable'" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="variable in variables"
                :key="variable.value"
                :value="variable.value"
              >
                {{ variable.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label>Operator</Label>
          <Select
            v-model="condition.operator"
            placeholder="Select operator"
          >
            <SelectTrigger>
              <SelectValue :placeholder="condition.operator || 'Select an operator'" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="op in operators"
                :key="op.value"
                :value="op.value"
              >
                {{ op.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label>Value</Label>
          <Input
            v-model="condition.value"
            placeholder="Compare with..."
            :disabled="!condition.variable"
          />
          <p class="text-xs text-muted-foreground">
            {{
              condition.operator === 'contains' ? 'Enter text to search for' :
                condition.operator === 'startsWith' ? 'Enter beginning text' :
                  condition.operator === 'endsWith' ? 'Enter ending text' :
                    'Enter value to compare against'
            }}
          </p>
        </div>
      </div>

      <Button
        class="w-full"
        :disabled="!isValid"
        @click="insertCondition"
      >
        Insert Conditional Block
      </Button>

      <div v-if="previewContent" class="rounded-lg border p-4 bg-muted/50">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-medium">Preview</h4>
          <span class="text-xs text-muted-foreground">Template syntax</span>
        </div>
        <pre class="text-sm overflow-x-auto whitespace-pre-wrap">{{ previewContent }}</pre>
      </div>
    </div>
  </Card>
</template>

<style scoped>
.conditional-content {
  padding: 1rem;
  margin: 0.5rem 0;
  border-radius: 0.375rem;
}

.conditional-content.true {
  background-color: rgba(var(--primary), 0.1);
  border: 1px solid rgb(var(--primary));
}

.conditional-content.false {
  background-color: rgba(var(--muted), 0.1);
  border: 1px dashed rgb(var(--muted));
}
</style>
