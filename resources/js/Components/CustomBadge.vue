<template>
  <span :class="badgeClass">
    {{ percentage }}%
  </span>
</template>

<script setup>
import { cva } from 'class-variance-authority';
import { computed } from 'vue';
import { toRef } from 'vue';

// Props
const props = defineProps({
  percentage: {
    type: Number,
    required: true,
  },
  type: {
    type: String,
    required: true,
    validator: (value) => ['open', 'click', 'bounce', 'delivered'].includes(value), // Only accept 'open', 'click', or 'bounce'
  }
});

// Dynamic class logic using cva
const badgeClass = computed(() => {
  const type = toRef(props, 'type');
  const percentage = toRef(props, 'percentage');

  const badgeCVA = cva('inline-block px-3 py-1 text-sm font-medium rounded-full', {
    variants: {
      type: {
        open: '',
        click: '',
        bounce: ''
      },
      percentage: {
        high: 'bg-green-100 text-green-800',
        low: 'bg-red-100 text-red-800',
      }
    },
    compoundVariants: [
      { type: 'open', percentage: 'high', class: 'bg-green-100 text-green-800' },  // Opens above 30% = good (green)
      { type: 'open', percentage: 'low', class: 'bg-red-100 text-red-800' },       // Opens below 30% = bad (red)
      { type: 'click', percentage: 'high', class: 'bg-green-100 text-green-800' }, // Clicks above 30% = good (green)
      { type: 'click', percentage: 'low', class: 'bg-red-100 text-red-800' },      // Clicks below 30% = bad (red)
      { type: 'bounce', percentage: 'high', class: 'bg-red-100 text-red-800' },    // Bounces above 15% = bad (red)
      { type: 'bounce', percentage: 'low', class: 'bg-green-100 text-green-800' }, // Bounces below 15% = good (green)
      { type: 'delivered', percentage: 'high', class: 'bg-green-100 text-green-800' },    // Bounces above 15% = bad (red)
      { type: 'delivered', percentage: 'low', class: 'bg-red-100 text-red-800' }, // Bounces below 15% = good (green)
    ],
    defaultVariants: {
      type: 'open',
      percentage: 'low',
    }
  });

  const percentageCategory = () => {
    if (type.value === 'open' || type.value === 'click' || type.value === 'delivered') {
      return percentage.value >= 30 ? 'high' : 'low';
    } else if (type.value === 'bounce') {
      return percentage.value > 15 ? 'high' : 'low';
    }
    return 'low';
  };

  return badgeCVA({ type: type.value, percentage: percentageCategory() });
});
</script>
