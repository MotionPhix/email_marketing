<script setup lang="ts">
import { ref } from 'vue'
import { Icon } from '@tabler/icons-vue'

interface Condition {
  variable: string
  operator: string
  value: string
}

const props = defineProps<{
  onInsert: (condition: string) => void
}>()

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
]

const variables = [
  { value: 'subscriber.first_name', label: 'First Name' },
  { value: 'subscriber.last_name', label: 'Last Name' },
  { value: 'subscriber.email', label: 'Email' },
  { value: 'subscriber.total_purchases', label: 'Total Purchases' },
  { value: 'subscriber.last_purchase_date', label: 'Last Purchase Date' },
  { value: 'subscriber.subscription_type', label: 'Subscription Type' },
]

const insertCondition = () => {
  const conditionText = `{% if {{${condition.value.variable}}} ${condition.value.operator} "${condition.value.value}" %}
    Content when condition is true
  {% else %}
    Content when condition is false
  {% endif %}`

  props.onInsert(conditionText)
}
</script>

<template>
  <div class="space-y-4 p-4">
    <h3 class="text-lg font-medium">Add Conditional Content</h3>

    <div class="grid gap-4">
      <div class="grid gap-2">
        <Label>If</Label>
        <Select v-model="condition.variable">
          <option value="">Select variable</option>
          <option
            v-for="variable in variables"
            :key="variable.value"
            :value="variable.value"
          >
            {{ variable.label }}
          </option>
        </Select>
      </div>

      <div class="grid gap-2">
        <Label>Operator</Label>
        <Select v-model="condition.operator">
          <option
            v-for="op in operators"
            :key="op.value"
            :value="op.value"
          >
            {{ op.label }}
          </option>
        </Select>
      </div>

      <div class="grid gap-2">
        <Label>Value</Label>
        <Input v-model="condition.value" placeholder="Compare with..." />
      </div>
    </div>

    <div class="pt-4">
      <Button
        class="w-full"
        :disabled="!condition.variable || !condition.value"
        @click="insertCondition"
      >
        Insert Conditional Block
      </Button>
    </div>

    <div class="rounded-lg border p-4">
      <h4 class="mb-2 font-medium">Preview</h4>
      <code class="block whitespace-pre-wrap text-sm">
        {% if {{{{ condition.variable }}}} {{ condition.operator }} "{{ condition.value }}" %}
        Content when condition is true
        {% else %}
        Content when condition is false
        {% endif %}
      </code>
    </div>
  </div>
</template>
