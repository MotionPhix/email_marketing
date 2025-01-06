<script setup lang="ts">
defineProps<{
  title: string
  price: number
  description?: string
  features: {
    uuid: string
    analytics: string
    campaign_limit: string
    can_schedule_campaigns: string
    email_limit: string
    personalisation: string
    recipient_limit: string
    segment_limit: string
    support_type: string
  }
  cardClass: string
}>();
</script>

<template>
  <div :class="cardClass">
    <p class="mb-3" v-if="title === 'Basic'">
      <span
        class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg text-xs uppercase font-semibold bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white">
        Most popular
      </span>
    </p>

    <h4 class="font-medium text-lg text-gray-800 dark:text-neutral-200">
      {{ title }}
    </h4>

    <span class="mt-5 font-bold text-5xl text-gray-800 dark:text-neutral-200">
      <span class="font-bold text-2xl -me-2 relative -right-1 -top-3">$</span>
      {{ price }}
    </span>

    <p v-if="title === 'Free'" class="mt-2 text-sm text-gray-500 dark:text-neutral-500">
      Forever free
    </p>

    <p v-if="title === 'Basic'" class="mt-2 text-sm text-gray-500 dark:text-neutral-500">
      All the basics for startups
    </p>

    <p v-if="title === 'Pro'" class="mt-2 text-sm text-gray-500 dark:text-neutral-500">
      Everything you need for a growing business
    </p>

    <p v-if="title === 'Enterprise'" class="mt-2 text-sm text-gray-500 dark:text-neutral-500">
      Advanced features for scaling your business
    </p>

    <ul
      class="mt-7 gap-2 text-sm sm:grid-cols-2 lg:grid-cols-1 grid-cols-1 grid">
      <li
        class="text-left flex gap-1 text-gray-800 dark:text-neutral-400"
        v-for="feature in features"
        :key="feature">
        <slot name="icon" />
        <span>
          {{ feature }}
        </span>
      </li>
    </ul>
<!--    <a :href="ctaLink" class="btn">-->
<!--      {{ ctaText }}-->
<!--    </a>-->
    <slot />
  </div>
</template>
