<script setup lang="ts">
import { computed } from 'vue'
import type { Setting } from '../../../types'

const props = defineProps<{
  setting: Setting
  modelValue: any
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: any): void
}>()

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const inputType = computed(() => {
  switch (props.setting.type) {
    case 'boolean':
      return 'switch'
    case 'integer':
    case 'float':
      return 'number'
    case 'json':
    case 'array':
      return 'textarea'
    case 'email':
      return 'email'
    case 'url':
      return 'url'
    default:
      return props.setting.options ? 'select' : 'text'
  }
})
</script>

<template>
  <div class="space-y-2">
    <Label :for="setting.key">
      {{ setting.label }}
      <span
        v-if="setting.is_system"
        class="ml-2 text-xs text-muted-foreground"
      >
        (System)
      </span>
    </Label>

    <p
      v-if="setting.description"
      class="text-sm text-muted-foreground"
    >
      {{ setting.description }}
    </p>

    <!-- Switch -->
    <Switch
      v-if="inputType === 'switch'"
      :id="setting.key"
      v-model="value"
      :disabled="setting.is_system"
    />

    <!-- Select -->
    <Select
      v-else-if="inputType === 'select'"
      v-model="value"
      :disabled="setting.is_system"
    >
      <SelectTrigger :id="setting.key">
        <SelectValue :placeholder="`Select ${setting.label.toLowerCase()}`" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem
          v-for="(label, value) in setting.options"
          :key="value"
          :value="value"
        >
          {{ label }}
        </SelectItem>
      </SelectContent>
    </Select>

    <!-- Textarea -->
    <Textarea
      v-else-if="inputType === 'textarea'"
      :id="setting.key"
      v-model="value"
      :disabled="setting.is_system"
      :placeholder="setting.label"
    />

    <!-- Input -->
    <Input
      v-else
      :id="setting.key"
      :type="inputType"
      v-model="value"
      :disabled="setting.is_system"
      :placeholder="setting.label"
    />
  </div>
</template>
