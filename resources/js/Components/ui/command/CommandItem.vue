<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ComboboxItem, useForwardPropsEmits } from 'radix-vue'
import { computed } from 'vue'

interface CommandItemProps {
  value: string | number
  disabled?: boolean
  asChild?: boolean
  as?: any
  class?: any
}

interface CommandItemEmits {
  select: [value: string | number]
}

const props = withDefaults(defineProps<CommandItemProps>(), {
  disabled: false,
  asChild: false,
  as: undefined,
  class: undefined,
})

const emit = defineEmits<CommandItemEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emit)
</script>

<template>
  <ComboboxItem
    v-bind="forwarded"
    :class="cn(
      'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      props.class,
    )">
    <slot />
  </ComboboxItem>
</template>
