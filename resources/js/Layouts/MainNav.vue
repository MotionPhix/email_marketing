<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  IconHome,
  IconMail,
  IconUsers,
  IconFileAnalytics,
  IconChartLine,
  IconUsersGroup
} from '@tabler/icons-vue'

const navigation = [
  {
    name: 'Dashboard',
    href: route('dashboard'),
    icon: IconHome,
    active: route().current('dashboard')
  },
  {
    name: 'Campaigns',
    href: route('campaigns.index'),
    icon: IconMail,
    active: route().current('campaigns.*')
  },
  {
    name: 'Subscribers',
    href: route('subscribers.index'),
    icon: IconUsers,
    active: route().current('subscribers.*')
  },
  {
    name: 'Templates',
    href: route('templates.index'),
    icon: IconFileAnalytics,
    active: route().current('templates.*')
  },
  // {
  //   name: 'Analytics',
  //   href: route('analytics.index'),
  //   icon: IconChartLine,
  //   active: route().current('analytics.*')
  // },
  // {
  //   name: 'Teams',
  //   href: route('teams.index'),
  //   icon: IconUsersGroup,
  //   active: route().current('teams.*')
  // }
]
</script>

<template>
  <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <Link
      v-for="item in navigation"
      :key="item.name"
      :href="item.href"
      :class="[
        item.active
          ? 'border-primary-400 text-gray-900 dark:text-white'
          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200'
      ]">
      <component
        :is="item.icon"
        class="h-5 w-5 mr-2"
        :class="item.active ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500'"
        aria-hidden="true"
      />
      {{ item.name }}
    </Link>
  </div>
</template>
