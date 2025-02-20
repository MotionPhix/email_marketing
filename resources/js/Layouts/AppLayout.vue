<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import UserNav from "@/Layouts/UserNav.vue";
import MainNav from "@/Layouts/MainNav.vue";
import ThemeToggle from "@/Layouts/ThemeToggle.vue"
import MobileNav from "@/Layouts/MobileNav.vue";
import {Toaster} from "vue-sonner";
import Notifications from "@/Components/Notifications.vue";
import PageTitle from "@/Layouts/PageTitle.vue";

interface Props {
  title: string
  containerSize?: 'sm' | 'md' | 'lg' | 'xl'
}

// Default to medium size container which is optimal for most content
const props = withDefaults(defineProps<Props>(), {
  containerSize: 'md'
})

const containerSizes = {
  sm: 'max-w-3xl', // 768px
  md: 'max-w-4xl', // 896px
  lg: 'max-w-5xl', // 1024px
  xl: 'max-w-6xl'  // 1152px
} as const
</script>

<template>
  <div>
    <Head :title="title"/>

    <Toaster rich-colors expand />

    <Banner/>

    <div class="min-h-screen bg-white dark:bg-gray-900">
      <nav class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">

        <!-- Primary Navigation Menu -->
        <div :class="['px-4 mx-auto sm:px-6 lg:px-8', containerSizes.lg]">
          <div class="flex justify-between h-16">

            <div class="flex">

              <!-- Logo -->
              <div class="flex items-center shrink-0">
                <Link :href="route('dashboard')">
                  <ApplicationMark class="block w-auto h-9"/>
                </Link>
              </div>

              <!-- Main Navigation Links -->
              <MainNav />

            </div>

            <!-- Hamburger -->
            <div class="hidden sm:flex sm:items-center gap-x-2 sm:ms-6">
              <Notifications
                :notifications="$page.props.notifications"
              />

              <ThemeToggle />

              <UserNav />
            </div>

            <!-- Hamburger -->
            <div class="flex gap-2 items-center -me-2 sm:hidden">

              <ThemeToggle />

              <MobileNav />

            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
<!--      <header-->
<!--        v-if="$slots.header"-->
<!--        class="sticky top-0 z-10 bg-white shadow dark:bg-gray-800">-->

<!--        <div-->
<!--          class="flex items-center justify-between px-4 mx-auto max-w-7xl sm:px-6 lg:px-8"-->
<!--          :class="$slots.action ? 'py-1' : 'py-2'">-->

<!--          <slot name="header"/>-->

<!--          <slot name="action"/>-->

<!--        </div>-->

<!--      </header>-->

      <header
        v-if="title || $slots.action"
        class="sticky top-0 z-10 bg-white shadow dark:bg-gray-800">
        <div :class="['px-4 py-4 mx-auto sm:px-6 lg:px-8', containerSizes[containerSize]]">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <!-- Title Section -->
            <div class="flex-1 min-w-0">
              <slot name="header">
                <PageTitle>{{ title }}</PageTitle>
              </slot>
            </div>

            <!-- Action Section -->
            <div
              v-if="$slots.action"
              class="flex justify-start sm:justify-end shrink-0">
              <slot name="action" />
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
<!--      <main class="max-w-5xl mx-auto">-->
<!--        <slot/>-->
<!--      </main>-->
      <main class="py-6">
        <div :class="['px-4 mx-auto sm:px-6 lg:px-8', containerSizes[containerSize]]">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
