<script setup lang="ts">
import { ref, computed } from 'vue'
import { Icon } from '@tabler/icons-vue'

interface Rule {
  id: string
  type: string
  field: string
  operator: string
  value: string | number
}

interface Segment {
  id: string
  name: string
  description: string
  rules: Rule[]
  matchType: 'all' | 'any'
}

const props = defineProps<{
  modelValue: Segment
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: Segment): void
}>()

const ruleTypes = [
  { value: 'profile', label: 'Profile Data' },
  { value: 'behavior', label: 'Behavior' },
  { value: 'engagement', label: 'Engagement' },
  { value: 'purchase', label: 'Purchase History' },
  { value: 'custom', label: 'Custom Fields' },
]

const fieldOptions = {
  profile: [
    { value: 'first_name', label: 'First Name' },
    { value: 'last_name', label: 'Last Name' },
    { value: 'email', label: 'Email' },
    { value: 'country', label: 'Country' },
    { value: 'city', label: 'City' },
  ],
  behavior: [
    { value: 'last_login', label: 'Last Login' },
    { value: 'signup_date', label: 'Signup Date' },
    { value: 'email_opened', label: 'Email Opens' },
    { value: 'email_clicked', label: 'Email Clicks' },
  ],
  engagement: [
    { value: 'campaign_engagement', label: 'Index Engagement' },
    { value: 'website_visits', label: 'Website Visits' },
  ],
  purchase: [
    { value: 'total_spent', label: 'Total Spent' },
    { value: 'purchase_count', label: 'Purchase Count' },
    { value: 'last_purchase', label: 'Last Purchase' },
    { value: 'product_category', label: 'Product Category' },
  ],
}

const operators = {
  string: [
    { value: 'equals', label: 'Equals' },
    { value: 'not_equals', label: 'Does not equal' },
    { value: 'contains', label: 'Contains' },
    { value: 'starts_with', label: 'Starts with' },
    { value: 'ends_with', label: 'Ends with' },
  ],
  number: [
    { value: 'equals', label: 'Equals' },
    { value: 'not_equals', label: 'Does not equal' },
    { value: 'greater_than', label: 'Greater than' },
    { value: 'less_than', label: 'Less than' },
    { value: 'greater_than_or_equal', label: 'Greater than or equal' },
    { value: 'less_than_or_equal', label: 'Less than or equal' },
  ],
  date: [
    { value: 'equals', label: 'On' },
    { value: 'not_equals', label: 'Not on' },
    { value: 'greater_than', label: 'After' },
    { value: 'less_than', label: 'Before' },
    { value: 'greater_than_or_equal', label: 'On or after' },
    { value: 'less_than_or_equal', label: 'On or before' },
  ],
}

const segment = ref({ ...props.modelValue })

const addRule = () => {
  segment.value.rules.push({
    id: crypto.randomUUID(),
    type: 'profile',
    field: '',
    operator: 'equals',
    value: '',
  })
  updateSegment()
}

const removeRule = (index: number) => {
  segment.value.rules.splice(index, 1)
  updateSegment()
}

const updateSegment = () => {
  emit('update:modelValue', segment.value)
}

const getOperatorOptions = (type: string, field: string) => {
  if (field.includes('date') || field.includes('login')) {
    return operators.date
  }
  if (field.includes('count') || field.includes('spent')) {
    return operators.number
  }
  return operators.string
}
</script>

<template>
  <div class="space-y-6">
    <!-- Basic Info -->
    <div class="grid gap-4">
      <div class="grid gap-2">
        <Label>Segment Name</Label>
        <Input
          v-model="segment.name"
          placeholder="e.g., Active Customers"
          @input="updateSegment"
        />
      </div>

      <div class="grid gap-2">
        <Label>Description</Label>
        <Textarea
          v-model="segment.description"
          placeholder="Describe this segment..."
          @input="updateSegment"
        />
      </div>
    </div>

    <!-- Match Type -->
    <div class="space-y-2">
      <Label>Match Type</Label>
      <Select
        v-model="segment.matchType"
        @change="updateSegment"
      >
        <option value="all">Match ALL conditions</option>
        <option value="any">Match ANY condition</option>
      </Select>
    </div>

    <!-- Rules -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Conditions</h3>
        <Button
          variant="outline"
          @click="addRule"
        >
          <Icon name="plus" class="mr-2 h-4 w-4" />
          Add Condition
        </Button>
      </div>

      <div class="space-y-4">
        <div
          v-for="(rule, index) in segment.rules"
          :key="rule.id"
          class="relative grid gap-4 rounded-lg border p-4"
        >
          <Button
            v-if="segment.rules.length > 1"
            variant="ghost"
            size="icon"
            class="absolute right-2 top-2"
            @click="removeRule(index)"
          >
            <Icon name="x" class="h-4 w-4" />
          </Button>

          <div class="grid gap-4 sm:grid-cols-4">
            <!-- Rule Type -->
            <div class="grid gap-2">
              <Label>Type</Label>
              <Select
                v-model="rule.type"
                @change="updateSegment"
              >
                <option
                  v-for="type in ruleTypes"
                  :key="type.value"
                  :value="type.value"
                >
                  {{ type.label }}
                </option>
              </Select>
            </div>

            <!-- Field -->
            <div class="grid gap-2">
              <Label>Field</Label>
              <Select
                v-model="rule.field"
                @change="updateSegment"
              >
                <option value="">Select field</option>
                <option
                  v-for="field in fieldOptions[rule.type]"
                  :key="field.value"
                  :value="field.value"
                >
                  {{ field.label }}
                </option>
              </Select>
            </div>

            <!-- Operator -->
            <div class="grid gap-2">
              <Label>Operator</Label>
              <Select
                v-model="rule.operator"
                @change="updateSegment"
              >
                <option
                  v-for="op in getOperatorOptions(rule.type, rule.field)"
                  :key="op.value"
                  :value="op.value"
                >
                  {{ op.label }}
                </option>
              </Select>
            </div>

            <!-- Value -->
            <div class="grid gap-2">
              <Label>Value</Label>
              <Input
                v-model="rule.value"
                :type="rule.field.includes('date') ? 'date' : 'text'"
                @input="updateSegment"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
