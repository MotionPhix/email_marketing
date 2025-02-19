<script setup>
import { cn } from '@/lib/utils';
import { ComboboxRoot, useForwardPropsEmits } from 'radix-vue';
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: ''
  },
  defaultValue: { type: [String, Number, null], default: null },
  open: { type: Boolean, default: true },
  defaultOpen: { type: Boolean, default: false },
  searchTerm: { type: String, default: '' },
  selectedValue: { type: [String, Number, null], default: null },
  multiple: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  name: { type: String, default: undefined },
  dir: { type: String, default: undefined },
  filterFunction: { type: Function, default: undefined },
  displayValue: { type: Function, default: undefined },
  resetSearchTermOnBlur: { type: Boolean, default: true },
  asChild: { type: Boolean, default: false },
  as: { type: null, default: undefined },
  class: { type: null, default: undefined },
});

const emits = defineEmits([
  'update:modelValue',
  'update:open',
  'update:searchTerm',
  'update:selectedValue',
]);

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <ComboboxRoot
    v-bind="forwarded"
    :class="cn(
      'flex h-full w-full flex-col overflow-hidden rounded-md bg-popover text-popover-foreground',
      props.class,
    )"
  >
    <slot />
  </ComboboxRoot>
</template>
