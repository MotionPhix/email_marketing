<script setup lang="ts">
import { computed } from 'vue'
import { PlusIcon, XIcon } from 'lucide-vue-next'

const props = defineProps<{
  modelValue: array
  availableConditions: {}
}>()

const emit = defineEmits(['update:modelValue'])

const conditions = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const addCondition = () => {
  conditions.value.push({
    field: '',
    operator: '',
    value: ''
  })
}

const removeCondition = (index) => {
  conditions.value.splice(index, 1)
}

const getOperatorsForField = (field) => {
  const fieldConfig = props.availableConditions.fields.find(f => f.value === field)
  return fieldConfig ? fieldConfig.operators : []
}

const getValuesForField = (field) => {
  const fieldConfig = props.availableConditions.fields.find(f => f.value === field)
  return fieldConfig?.values || []
}
</script>

<template>
  <div class="space-y-4">
    <div
      v-for="(condition, index) in conditions"
      :key="index"
      class="flex items-start space-x-4">
      <div class="grid grid-cols-3 gap-4 flex-1">
        <!-- Field -->
        <div>
          <Select
            v-model="condition.field"
            :disabled="false">
            <SelectTrigger>
              <SelectValue placeholder="Select field" />
            </SelectTrigger>

            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="field in availableConditions.fields"
                  :key="field.value"
                  :value="field.value">
                  {{ field.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>

        <!-- Operator -->
        <div>
          <Select
            v-model="condition.operator"
            :disabled="!condition.field">
            <SelectTrigger>
              <SelectValue placeholder="Select operator" />
            </SelectTrigger>

            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="operator in getOperatorsForField(condition.field)"
                  :key="operator"
                  :value="operator">
                  {{ operator }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>

        <!-- Value -->
        <div>
          <template v-if="getValuesForField(condition.field).length">
            <Select v-model="condition.value">
              <SelectTrigger>
                <SelectValue placeholder="Select value" />
              </SelectTrigger>

              <SelectContent>
                <SelectGroup>
                  <SelectItem
                    v-for="value in getValuesForField(condition.field)"
                    :key="value.value"
                    :value="value.value">
                    {{ value.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </template>

          <template v-else>
            <Input
              v-model="condition.value"
              :type="condition.field === 'created_at' ? 'date' : 'text'"
              placeholder="Enter value"
            />
          </template>
        </div>
      </div>

      <Button
        type="button"
        variant="ghost"
        size="icon"
        class="text-destructive"
        @click="removeCondition(index)">
        <XIcon class="h-4 w-4" />
      </Button>
    </div>

    <Button
      type="button"
      variant="outline"
      size="sm"
      @click="addCondition">
      <PlusIcon class="h-4 w-4" />
      Add Condition
    </Button>
  </div>
</template>
