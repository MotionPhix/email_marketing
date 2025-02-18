<script setup lang="ts">
import {ref} from 'vue'
import {Link} from '@inertiajs/vue3'
import {
  Menu,
  Users,
  FileText,
  BarChart,
  Building2,
  User,
  Settings,
  Key,
  CreditCard,
  LifeBuoy,
  LogOut
} from 'lucide-vue-next'
import {
  IconChartLine,
  IconDeviceAnalytics,
  IconMail,
  IconReportAnalytics,
  IconTemplate,
  IconUsers
} from "@tabler/icons-vue";

const isOpen = ref(false)

const navigation = [
  {
    name: 'Dashboard',
    href: route('dashboard'),
    icon: IconChartLine,
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
    icon: IconTemplate,
    active: route().current('templates.*')
  },
  {
    name: 'Analytics',
    href: '#', // route('analytics.index'),
    icon: IconDeviceAnalytics,
    active: route().current('analytics.*')
  },
  {
    name: 'Teams',
    href: '#', // route('teams.index'),
    icon: Building2,
    active: route().current('teams.*')
  }
]

const userNavigation = [
  {name: 'My Profile', href: route('profile.show'), icon: User},
  {name: 'Settings', href: route('settings.index'), icon: Settings},
  {name: 'API Tokens', href: route('api-tokens.index'), icon: Key},
  {
    name: 'Billing',
    href: '#', // route('billing.index'
    icon: CreditCard
  },
  {
    name: 'Support',
    href: 'https://help.emailmarketing.com',
    icon: LifeBuoy,
    external: true
  }
]

const closeSheet = () => {
  isOpen.value = false
}
</script>

<template>
  <Sheet v-model:open="isOpen">
    <SheetTrigger asChild>
      <Button variant="ghost" size="icon" class="md:hidden">
        <svg
          stroke="currentColor"
          fill="none"
          viewBox="0 0 24 24">
          <path
            :class="{'hidden': isOpen, 'inline-flex': ! isOpen }"
            d="M4 5L16 5 M4 12L20 12 M4 19L12 19"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"/>
          <path
            :class="{'hidden': ! isOpen, 'inline-flex': isOpen }"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
        <span class="sr-only">Open menu</span>
      </Button>
    </SheetTrigger>

    <SheetContent side="left" class="w-[300px] sm:w-[400px]">
      <SheetHeader class="items-start">
        <SheetTitle>Main Navigation</SheetTitle>
      </SheetHeader>

      <div class="flex flex-col space-y-4 py-4">
        <div class="space-y-1">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            @click="closeSheet"
            :class="[
              item.active
                ? 'bg-gray-50 text-primary-600 dark:bg-gray-800 dark:text-primary-400'
                : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-primary-400',
              'group flex items-center px-3 py-2 text-base font-medium rounded-md'
            ]">
            <component
              :is="item.icon"
              :class="[
                item.active
                  ? 'text-primary-500'
                  : 'text-gray-400 group-hover:text-primary-500',
                'mr-4 h-6 w-6'
              ]"
              aria-hidden="true"
            />
            {{ item.name }}
          </Link>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700"/>

        <div class="space-y-1">
          <Link
            v-for="item in userNavigation"
            :key="item.name"
            :href="item.href"
            :target="item.external ? '_blank' : undefined"
            @click="closeSheet"
            class="group flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-primary-600 rounded-md dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-primary-400"
          >
            <component
              :is="item.icon"
              class="mr-4 h-6 w-6 text-gray-400 group-hover:text-primary-500"
              aria-hidden="true"
            />
            {{ item.name }}
          </Link>

          <Link
            :href="route('logout')"
            method="post"
            as="button"
            @click="closeSheet"
            class="group flex w-full items-center px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-primary-600 rounded-md dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-primary-400"
          >
            <LogOut
              class="mr-4 h-6 w-6 text-gray-400 group-hover:text-primary-500"
              aria-hidden="true"
            />
            Log out
          </Link>
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>
