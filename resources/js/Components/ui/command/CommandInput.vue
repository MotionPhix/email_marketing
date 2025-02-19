<script setup>
import { cn } from '@/lib/utils';
import { Search } from 'lucide-vue-next';
import { ComboboxInput, useForwardProps } from 'radix-vue';
import { computed } from 'vue';

defineOptions({
  inheritAttrs: false,
});

const props = defineProps({
  modelValue: { type: [String, Number, null], default: '' },
  type: { type: String, default: 'text' },
  disabled: { type: Boolean, default: false },
  autoFocus: { type: Boolean, default: false },
  asChild: { type: Boolean, default: false },
  as: { type: null, default: undefined },
  class: { type: null, default: undefined },
});

const emit = defineEmits(['update:modelValue']);

const delegatedProps = computed(() => {
  const { class: _, modelValue, ...delegated } = props;
  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <div class="relative" cmdk-input-wrapper>
    <Search class="mr-2 h-4 w-4 shrink-0 opacity-50 absolute top-3 left-3" />
    <ComboboxInput
      v-bind="{ ...forwardedProps, ...$attrs }"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="cn(
        'pl-8 flex h-11 w-full !focus:outline-none rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )"
    />
  </div>
</template>
