<script setup>
import {Head, Link} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import UserNav from "@/Layouts/UserNav.vue";
import MainNav from "@/Layouts/MainNav.vue";
import ThemeToggle from "@/Layouts/ThemeToggle.vue"
import MobileNav from "@/Layouts/MobileNav.vue";
import {Toaster} from "vue-sonner";

defineProps({
  title: String,
});
</script>

<template>
  <div>
    <Head :title="title"/>

    <Toaster rich-colors />

    <Banner/>

    <div class="min-h-screen bg-white dark:bg-gray-900">
      <nav class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">

        <!-- Primary Navigation Menu -->
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
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

            <div class="hidden sm:flex sm:items-center gap-2 sm:ms-6">
              <ThemeToggle />
              <!-- Settings Dropdown -->
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
      <header
        v-if="$slots.header"
        class="sticky top-0 z-10 bg-white shadow dark:bg-gray-800">

        <div
          class="flex items-center justify-between px-4 mx-auto max-w-7xl sm:px-6 lg:px-8"
          :class="$slots.action ? 'py-1' : 'py-2'">

          <slot name="header"/>

          <slot name="action"/>

        </div>

      </header>

      <!-- Page Content -->
      <main class="max-w-3xl mx-auto">
        <slot/>
      </main>
    </div>
  </div>
</template>
