<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  is: any // Component
}>()

// Forward all other props and slots to the dynamic component
const componentProps = computed(() => {
  const { is, ...rest } = props
  return rest
})
</script>

<template>
  <component
    :is="is"
    v-bind="componentProps">
    <template
      v-for="(_, name) in $slots"
      :key="name"
      #[name]="slotData">
      <slot
        :name="name"
        v-bind="slotData"
      />
    </template>
  </component>
</template>
