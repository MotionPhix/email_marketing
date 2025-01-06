<script setup lang="ts">
import {onMounted} from 'vue';
import {gsap} from 'gsap';
import {Link} from '@inertiajs/vue3'
import PricingCard from "@/Components/PricingCard.vue";
import {CheckIcon} from "@radix-icons/vue";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import ThemeToggle from "@/Layouts/ThemeToggle.vue";

defineProps<{
  plans: Array<{
    uuid: string
    name: string
    price: number
    features: Array<{
      uuid: string
      analytics: string
      campaign_limit: string
      can_schedule_campaigns: string
      email_limit: string
      personalisation: string
      recipient_limit: string
      segment_limit: string
      support_type: string
    }>
  }>
}>()

onMounted(() => {
  gsap.from("header", {duration: 1, y: -100, opacity: 0});
  gsap.from("#hero", {duration: 1, scale: 0.5, opacity: 0});
  gsap.from(".grid > div", {
    duration: 0.5,
    opacity: 0,
    y: 20,
    stagger: 0.2,
    scrollTrigger: {
      trigger: ".grid",
      start: "top 80%",
    }
  });
})
</script>

<template>
  <div>
    <!-- ========== HEADER ========== -->
    <article class="sticky px-4 top-4 z-50">
      <header
        class="flex inset-x-0 flex-wrap md:justify-start md:flex-nowrap w-full before:absolute before:inset-0 before:max-w-[66rem] before:mx-2 before:lg:mx-auto before:rounded-[26px] before:bg-neutral-800/30 before:backdrop-blur-md">
        <nav
          class="relative max-w-[66rem] w-full py-2.5 ps-2 pe-2 md:flex md:items-center md:justify-between md:py-0 mx-2 lg:mx-auto">
          <div class="flex items-center justify-between">
            <!-- Logo -->
            <Link
              as="button"
              class="inline-flex gap-2 items-center rounded-md text-xl font-semibold focus:outline-none focus:opacity-80"
              :href="route('home')" :aria-label="$page.props.appName">
              <ApplicationMark class="h-10 rounded-full" /> <span class="font-serif text-2xl">{{ $page.props.appName }}</span>
            </Link>
            <!-- End Logo -->

            <div class="md:hidden">
              <button type="button"
                      class="hs-collapse-toggle size-8 flex justify-center items-center text-sm font-semibold rounded-full bg-neutral-800 text-white disabled:opacity-50 disabled:pointer-events-none"
                      id="hs-navbar-floating-dark-collapse" aria-expanded="false"
                      aria-controls="hs-navbar-floating-dark" aria-label="Toggle navigation"
                      data-hs-collapse="#hs-navbar-floating-dark">
                <svg class="hs-collapse-open:hidden shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <line x1="3" x2="21" y1="6" y2="6"/>
                  <line x1="3" x2="21" y1="12" y2="12"/>
                  <line x1="3" x2="21" y1="18" y2="18"/>
                </svg>
                <svg class="hs-collapse-open:block hidden shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 6 6 18"/>
                  <path d="m6 6 12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Collapse -->
          <div id="hs-navbar-floating-dark"
               class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow md:block"
               aria-labelledby="hs-navbar-floating-dark-collapse">
            <div class="flex flex-col md:flex-row md:items-center md:justify-end py-2 md:py-0 md:ps-7">
              <Link
                as="button"
                class="p-3 ps-px sm:px-3 md:py-4 text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                :href="route('home')" aria-current="page">Home</Link>
              <a
                class="p-3 ps-px sm:px-3 md:py-4 text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                href="#">Stories</a>
              <a
                class="p-3 ps-px sm:px-3 md:py-4 text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                href="#">Reviews</a>
              <a
                class="p-3 ps-px sm:px-3 md:py-4 text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                href="#">Approach</a>

              <div
                class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false] p-3 ps-px sm:px-3 md:py-4">
                <button id="hs-dropdown-floating-dark" type="button"
                        class="hs-dropdown-toggle flex items-center w-full text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                  About
                  <svg
                    class="hs-dropdown-open:-rotate-180 md:hs-dropdown-open:rotate-0 duration-300 shrink-0 ms-auto md:ms-1 size-4"
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                  </svg>
                </button>

                <div
                  class="hs-dropdown-menu transition-[opacity,margin] duration-50 md:duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-48 hidden z-10 bg-neutral-800 md:shadow-md rounded-lg before:absolute top-full before:-top-5 before:start-0 before:w-full before:h-5"
                  role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-floating-dark">
                  <div class="py-1 md:px-1 space-y-1">
                    <a
                      class="flex items-center gap-x-3.5 py-2 md:px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                      href="#">
                      About
                    </a>
                    <div
                      class="hs-dropdown [--strategy:static] md:[--strategy:absolute] [--adaptive:none] md:[--trigger:hover] [--is-collapse:true] md:[--is-collapse:false] relative">
                      <button id="hs-dropdown-floating-dark-sub" type="button"
                              class="hs-dropdown-toggle w-full flex justify-between items-center py-2 md:px-3 text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                              aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Sub Menu
                        <svg
                          class="hs-dropdown-open:-rotate-180 md:hs-dropdown-open:-rotate-90 md:-rotate-90 duration-300 shrink-0 ms-2 size-4"
                          xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="m6 9 6 6 6-6"/>
                        </svg>
                      </button>

                      <div
                        class="hs-dropdown-menu transition-[opacity,margin] duration-50 md:duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-48 hidden z-10 bg-neutral-800 md:shadow-md rounded-lg before:absolute before:-end-5 before:top-0 before:h-full before:w-5 !mx-[10px] top-0 end-full"
                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-floating-dark-sub">
                        <div class="py-1 md:px-1 space-y-1">
                          <a
                            class="flex items-center gap-x-3.5 py-2 md:px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                            href="#">
                            About
                          </a>
                          <a
                            class="flex items-center gap-x-3.5 py-2 md:px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                            href="#">
                            Downloads
                          </a>
                          <a
                            class="flex items-center gap-x-3.5 py-2 md:px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                            href="#">
                            Team Account
                          </a>
                        </div>
                      </div>
                    </div>

                    <a
                      class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                      href="#">
                      Downloads
                    </a>
                    <a
                      class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-white hover:text-neutral-300 focus:outline-none focus:text-neutral-300"
                      href="#">
                      Team Account
                    </a>
                  </div>
                </div>
              </div>

              <div>
                <a
                  class="group inline-flex items-center gap-x-2 py-2 px-3 bg-[#ff0] font-medium text-sm text-neutral-800 rounded-full focus:outline-none"
                  href="../templates/agency/index.html#contact">
                  Contact us
                </a>
              </div>
            </div>
          </div>
          <!-- End Collapse -->
        </nav>
      </header>
    </article>
    <!-- ========== END HEADER ========== -->

    <!-- Hero -->
    <div class="relative overflow-hidden">
      <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Header Text -->
        <div class="max-w-2xl text-center mx-auto">
          <h1 class="font-serif capitalize block text-3xl font-bold text-gray-800 sm:text-4xl md:text-5xl dark:text-white">
            power up your <br/> <span class="text-blue-600">email marketing</span>
          </h1>

          <p class="mt-3 text-lg text-gray-800 dark:text-neutral-400">
            Simplify your campaigns, automate workflows, and track results — all in one place.
          </p>
        </div>

        <!-- Hero Image with Call-to-Action -->
        <div class="mt-10 relative max-w-5xl mx-auto">
          <div class="w-full object-cover h-96 sm:h-[480px] bg-[url('https://images.unsplash.com/photo-1603791440384-56cd371ee9a7?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1020&q=80')] bg-no-repeat bg-center bg-cover rounded-xl"></div>

          <!-- Call-to-Action Button -->
          <div class="absolute inset-0 size-full">
            <div class="flex flex-col justify-center items-center size-full">
              <a
                class="py-3 px-6 inline-flex items-center gap-x-2 text-base font-medium rounded-full border border-gray-200 bg-blue-600 text-white shadow-md hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:bg-blue-800"
                href="#">
                <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                Start Your First Campaign
              </a>
            </div>
          </div>

          <!-- Decorative Elements -->
          <div class="absolute bottom-12 -start-20 -z-[1] size-48 bg-gradient-to-b from-orange-500 to-white p-px rounded-lg dark:to-neutral-900">
            <div class="bg-white size-48 rounded-lg dark:bg-neutral-900"></div>
          </div>

          <div class="absolute -top-12 -end-20 -z-[1] size-48 bg-gradient-to-t from-blue-600 to-cyan-400 p-px rounded-full">
            <div class="bg-white size-48 rounded-full dark:bg-neutral-900"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Hero -->

    <!-- Features -->
    <div class="max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
      <!-- Grid -->
      <div class="mt-5 lg:mt-16 grid lg:grid-cols-3 gap-8 lg:gap-12">
        <div class="lg:col-span-1">
          <h2 class="font-serif font-bold text-2xl md:text-3xl text-gray-800 dark:text-neutral-200">
            Empower your email marketing strategy
          </h2>
          <p class="mt-2 md:mt-4 text-gray-500 dark:text-neutral-500">
            Our email marketing platform simplifies campaign creation, automates workflows, and provides actionable insights for growth.
          </p>
        </div>
        <!-- End Col -->

        <div class="lg:col-span-2">
          <div class="grid sm:grid-cols-2 gap-8 md:gap-12">

            <!-- Icon Block -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 mt-1 size-6 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                   width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="10" x="3" y="11" rx="2"/>
                <circle cx="12" cy="5" r="2"/>
                <path d="M12 7v4"/>
                <line x1="8" x2="8" y1="16" y2="16"/>
                <line x1="16" x2="16" y1="16" y2="16"/>
              </svg>
              <div class="grow">
                <h3 class="font-serif text-lg font-semibold text-gray-800 dark:text-white">
                  Automated Campaigns
                </h3>
                <p class="mt-1 text-gray-600 dark:text-neutral-400">
                  Launch campaigns with ease using automation tools that schedule, send, and track messages for you.
                </p>
              </div>
            </div>
            <!-- End Icon Block -->

            <!-- Icon Block -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 mt-1 size-6 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                   width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M7 10v12"/>
                <path
                  d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/>
              </svg>
              <div class="grow">
                <h3 class="font-serif text-lg font-semibold text-gray-800 dark:text-white">
                  Advanced Reporting
                </h3>
                <p class="mt-1 text-gray-600 dark:text-neutral-400">
                  Track email performance with real-time metrics, subscriber engagement, and custom reporting tools.
                </p>
              </div>
            </div>
            <!-- End Icon Block -->

            <!-- Icon Block -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 mt-1 size-6 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                   width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
              </svg>
              <div class="grow">
                <h3 class="font-serif text-lg font-semibold text-gray-800 dark:text-white">
                  Scalable Infrastructure
                </h3>
                <p class="mt-1 text-gray-600 dark:text-neutral-400">
                  Designed to support high-volume email sends and large-scale subscriber databases without downtime.
                </p>
              </div>
            </div>
            <!-- End Icon Block -->

            <!-- Icon Block -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 mt-1 size-6 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                   width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <div class="grow">
                <h3 class="font-serif text-lg font-semibold text-gray-800 dark:text-white">
                  Subscriber Management
                </h3>
                <p class="mt-1 text-gray-600 dark:text-neutral-400">
                  Manage your subscribers with segmentation, import/export, and GDPR-compliant data handling.
                </p>
              </div>
            </div>
            <!-- End Icon Block -->

          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Grid -->
    </div>
    <!-- End Features -->

    <!-- Pricing -->
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
      <!-- Title -->
      <div class="max-w-2xl mx-auto text-center mb-10 lg:mb-14">
        <h2 class="text-2xl font-bold md:text-4xl md:leading-tight dark:text-white">Pricing</h2>
        <p class="mt-1 text-gray-600 dark:text-neutral-400">
          Whatever your status, our offers evolve according to your needs.
        </p>
      </div>
      <!-- End Title -->

      <!-- Grid -->
      <div class="max-w-4xl lg:grid-cols-4 mx-auto mt-6 md:mt-12 grid sm:grid-cols-2 gap-3 md:gap-6 lg:items-center">

        <!-- Card -->
        <PricingCard
          :title="plan.name"
          :features="plan.features"
          :price="plan.price"
          :card-class="plan.name !== 'Basic' ? 'flex flex-col bg-white border border-gray-200 text-center rounded-2xl p-4 dark:bg-neutral-900 dark:border-neutral-800' : 'flex flex-col bg-white border-2 border-violet-900 text-center shadow-xl rounded-2xl p-4 dark:bg-neutral-900 dark:border-violet-700'"
          v-for="plan in plans">
          <template #icon>
            <CheckIcon />
          </template>
          <Link
            class="mt-5 py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
            href="#">
            Sign up
          </Link>
        </PricingCard>
        <!-- End Card -->

      </div>
      <!-- End Grid -->

      <!-- Comparison table -->
      <div class="mt-20 lg:mt-32 max-w-4xl mx-auto rounded-xl overflow-hidden">
        <div class="lg:text-center mb-10 lg:mb-20">
          <h3 class="text-2xl font-semibold dark:text-white">Compare plans</h3>
        </div>

        <!-- xs to lg -->
        <div class="space-y-24 lg:hidden">
          <section>
            <div class="px-4 mb-4">
              <h2 class="text-lg leading-6 font-medium text-gray-800">Free</h2>
            </div>
            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Financial data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Open/High/Low/Close
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Price-volume difference indicator
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                On-chain data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Network growth
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Average token age consumed
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Exchange flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total ERC20 exchange funds flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Transaction volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total circulation (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Velocity of tokens (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">ETH gas used
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Social data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Dev activity
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Topic search
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Relative social dominance
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total social volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>
              </tbody>
            </table>
          </section>

          <section>
            <div class="px-4 mb-4">
              <h2 class="text-lg leading-6 font-medium text-gray-800">Basic</h2>
            </div>
            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Financial data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Open/High/Low/Close
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Price-volume difference indicator
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                On-chain data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Network growth
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Average token age consumed
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Exchange flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total ERC20 exchange funds flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Transaction volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total circulation (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Velocity of tokens (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">ETH gas used
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Social data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Dev activity
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Topic search
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Relative social dominance
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total social volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>
          </section>

          <section>
            <div class="px-4 mb-4">
              <h2 class="text-lg leading-6 font-medium text-gray-800">Pro</h2>
            </div>
            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Financial data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Open/High/Low/Close
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Price-volume difference indicator
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                On-chain data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Network growth
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Average token age consumed
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Exchange flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total ERC20 exchange funds flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Transaction volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total circulation (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Velocity of tokens (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">ETH gas used
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Social data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Dev activity
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Topic search
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Relative social dominance
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total social volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Minus -->
                  <svg class="shrink-0 ms-auto size-5 text-gray-400 dark:text-neutral-600"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                  </svg>
                  <!-- Minus -->
                  <span class="sr-only">No</span>
                </td>
              </tr>
              </tbody>
            </table>
          </section>

          <section>
            <div class="px-4 mb-4">
              <h2 class="text-lg leading-6 font-medium text-gray-800">Enterprise</h2>
            </div>
            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Financial data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Open/High/Low/Close
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Price-volume difference indicator
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                On-chain data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Network growth
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Average token age consumed
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Exchange flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total ERC20 exchange funds flow
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Transaction volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total circulation (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Velocity of tokens (beta)
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">ETH gas used
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>

            <table class="w-full">
              <caption
                class="bg-gray-50 border-t border-gray-200 py-3 px-4 text-sm font-bold text-gray-800 text-start dark:bg-neutral-700 dark:border-neutral-700 dark:text-white">
                Social data
              </caption>
              <thead>
              <tr>
                <th class="sr-only" scope="col">Feature</th>
                <th class="sr-only" scope="col">Included</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Dev activity
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Topic search
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Relative social dominance
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>

              <tr class="border-t border-gray-200 dark:border-neutral-700">
                <th
                  class="py-5 px-4 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                  scope="row">Total social volume
                </th>
                <td class="py-5 pe-4">
                  <!-- Check -->
                  <svg class="shrink-0 ms-auto size-5 text-blue-600 dark:text-blue-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <!-- End Solid Check -->
                  <span class="sr-only">Yes</span>
                </td>
              </tr>
              </tbody>
            </table>
          </section>
        </div>
        <!-- End xs to lg -->

        <!-- lg+ -->
        <div class="hidden lg:block">
          <table class="w-full h-px">
            <caption class="sr-only">
              Pricing plan comparison
            </caption>
            <thead class="sticky top-0 inset-x-0 bg-white dark:bg-neutral-900">
            <tr>
              <th class="py-4 ps-6 pe-6 text-sm font-medium text-gray-800 text-start" scope="col">
                <span class="sr-only">Feature by</span>
                <span class="dark:text-white">Plans</span>
              </th>

              <th class="w-1/4 py-4 px-6 text-lg leading-6 font-medium text-gray-800 text-center dark:text-white"
                  scope="col">Free
              </th>
              <th class="w-1/4 py-4 px-6 text-lg leading-6 font-medium text-gray-800 text-center dark:text-white"
                  scope="col">Startup
              </th>
              <th class="w-1/4 py-4 px-6 text-lg leading-6 font-medium text-gray-800 text-center dark:text-white"
                  scope="col">Team
              </th>
              <th class="w-1/4 py-4 px-6 text-lg leading-6 font-medium text-gray-800 text-center dark:text-white"
                  scope="col">Enterprise
              </th>
            </tr>
            </thead>
            <tbody
              class="border-t border-gray-200 divide-y divide-gray-200 dark:border-neutral-700 dark:divide-neutral-700">
            <tr>
              <th class="py-3 ps-6 bg-gray-50 font-bold text-gray-800 text-start dark:bg-neutral-800 dark:text-white"
                  colspan="5" scope="colgroup">Financial data
              </th>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Open/High/Low/Close
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Price-volume difference indicator
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th class="py-3 ps-6 bg-gray-50 font-bold text-gray-800 text-start dark:bg-neutral-800 dark:text-white"
                  colspan="5" scope="colgroup">On-chain data
              </th>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Network growth
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Average token age consumed
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Exchange flow
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Total ERC20 exchange funds flow
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Transaction volume
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Total circulation (beta)
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Velocity of tokens (beta)
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">ETH gas used
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Not included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th class="py-3 ps-6 bg-gray-50 font-bold text-gray-800 text-start dark:bg-neutral-800 dark:text-white"
                  colspan="5" scope="colgroup">Social data
              </th>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Dev activity
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Topic search
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Relative social dominance
              </th>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Minus -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- Minus -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>

            <tr>
              <th
                class="py-5 ps-6 pe-6 text-sm font-normal text-gray-600 text-start whitespace-nowrap dark:text-neutral-400"
                scope="row">Relative social dominance
              </th>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Free</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Startup</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-gray-400 dark:text-neutral-600"
                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Team</span>
              </td>

              <td class="py-5 px-6">
                <!-- Check -->
                <svg class="mx-auto shrink-0 size-5 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- End Solid Check -->
                <span class="sr-only">Included in Enterprise</span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <!-- End lg+ -->
      </div>
      <!-- End Comparison table -->
    </div>
    <!-- End Pricing -->

    <!-- Contact -->
    <div class="bg-neutral-900">
      <div class="max-w-4xl px-4 xl:px-0 py-10 lg:py-20 mx-auto">
        <!-- Title -->
        <div class="max-w-3xl mb-10 lg:mb-14">
          <h2 class="text-white font-semibold text-2xl md:text-4xl md:leading-tight">Contact us</h2>
          <p class="mt-1 text-neutral-400">Whatever your goal - we will get you there.</p>
        </div>
        <!-- End Title -->

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 lg:gap-x-16">
          <div class="md:order-2 border-b border-neutral-800 pb-10 mb-10 md:border-b-0 md:pb-0 md:mb-0">
            <form>
              <div class="space-y-4">
                <!-- Input -->
                <div class="relative">
                  <input type="text" id="hs-tac-input-name" class="peer p-4 block w-full bg-neutral-800 border-transparent rounded-lg text-sm text-white placeholder:text-transparent focus:outline-none focus:ring-0 focus:border-transparent disabled:opacity-50 disabled:pointer-events-none
              focus:pt-6
              focus:pb-2
              [&:not(:placeholder-shown)]:pt-6
              [&:not(:placeholder-shown)]:pb-2
              autofill:pt-6
              autofill:pb-2" placeholder="Name">
                  <label for="hs-tac-input-name" class="absolute top-0 start-0 p-4 h-full text-neutral-400 text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:text-xs
                peer-focus:-translate-y-1.5
                peer-focus:text-neutral-400
                peer-[:not(:placeholder-shown)]:text-xs
                peer-[:not(:placeholder-shown)]:-translate-y-1.5
                peer-[:not(:placeholder-shown)]:text-neutral-400">Name</label>
                </div>
                <!-- End Input -->

                <!-- Input -->
                <div class="relative">
                  <input type="email" id="hs-tac-input-email" class="peer p-4 block w-full bg-neutral-800 border-transparent rounded-lg text-sm text-white placeholder:text-transparent focus:outline-none focus:ring-0 focus:border-transparent disabled:opacity-50 disabled:pointer-events-none
              focus:pt-6
              focus:pb-2
              [&:not(:placeholder-shown)]:pt-6
              [&:not(:placeholder-shown)]:pb-2
              autofill:pt-6
              autofill:pb-2" placeholder="Email">
                  <label for="hs-tac-input-email" class="absolute top-0 start-0 p-4 h-full text-neutral-400 text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:text-xs
                peer-focus:-translate-y-1.5
                peer-focus:text-neutral-400
                peer-[:not(:placeholder-shown)]:text-xs
                peer-[:not(:placeholder-shown)]:-translate-y-1.5
                peer-[:not(:placeholder-shown)]:text-neutral-400">Email</label>
                </div>
                <!-- End Input -->

                <!-- Input -->
                <div class="relative">
                  <input type="text" id="hs-tac-input-company" class="peer p-4 block w-full bg-neutral-800 border-transparent rounded-lg text-sm text-white placeholder:text-transparent focus:outline-none focus:ring-0 focus:border-transparent disabled:opacity-50 disabled:pointer-events-none
              focus:pt-6
              focus:pb-2
              [&:not(:placeholder-shown)]:pt-6
              [&:not(:placeholder-shown)]:pb-2
              autofill:pt-6
              autofill:pb-2" placeholder="Company">
                  <label for="hs-tac-input-company" class="absolute top-0 start-0 p-4 h-full text-neutral-400 text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:text-xs
                peer-focus:-translate-y-1.5
                peer-focus:text-neutral-400
                peer-[:not(:placeholder-shown)]:text-xs
                peer-[:not(:placeholder-shown)]:-translate-y-1.5
                peer-[:not(:placeholder-shown)]:text-neutral-400">Company</label>
                </div>
                <!-- End Input -->

                <!-- Input -->
                <div class="relative">
                  <input type="text" id="hs-tac-input-phone" class="peer p-4 block w-full bg-neutral-800 border-transparent rounded-lg text-sm text-white placeholder:text-transparent focus:outline-none focus:ring-0 focus:border-transparent disabled:opacity-50 disabled:pointer-events-none
              focus:pt-6
              focus:pb-2
              [&:not(:placeholder-shown)]:pt-6
              [&:not(:placeholder-shown)]:pb-2
              autofill:pt-6
              autofill:pb-2" placeholder="Phone">
                  <label for="hs-tac-input-phone" class="absolute top-0 start-0 p-4 h-full text-neutral-400 text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:text-xs
                peer-focus:-translate-y-1.5
                peer-focus:text-neutral-400
                peer-[:not(:placeholder-shown)]:text-xs
                peer-[:not(:placeholder-shown)]:-translate-y-1.5
                peer-[:not(:placeholder-shown)]:text-neutral-400">Phone</label>
                </div>
                <!-- End Input -->

                <!-- Textarea -->
                <div class="relative">
              <textarea id="hs-tac-message" class="peer p-4 block w-full bg-neutral-800 border-transparent rounded-lg text-sm text-white placeholder:text-transparent focus:outline-none focus:ring-0 focus:border-transparent disabled:opacity-50 disabled:pointer-events-none
              focus:pt-6
              focus:pb-2
              [&:not(:placeholder-shown)]:pt-6
              [&:not(:placeholder-shown)]:pb-2
              autofill:pt-6
              autofill:pb-2" placeholder="This is a textarea placeholder" data-hs-textarea-auto-height></textarea>
                  <label for="hs-tac-message" class="absolute top-0 start-0 p-4 h-full text-neutral-400 text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                peer-focus:text-xs
                peer-focus:-translate-y-1.5
                peer-focus:text-neutral-400
                peer-[:not(:placeholder-shown)]:text-xs
                peer-[:not(:placeholder-shown)]:-translate-y-1.5
                peer-[:not(:placeholder-shown)]:text-neutral-400">Tell us about your project</label>
                </div>
                <!-- End Textarea -->
              </div>

              <div class="mt-2">
                <p class="text-xs text-neutral-500">
                  All fields are required
                </p>

                <p class="mt-5">
                  <a
                    class="group inline-flex items-center gap-x-2 py-2 px-3 bg-[#ff0] font-medium text-sm text-neutral-800 rounded-full focus:outline-none"
                    href="#">
                    Submit
                    <svg
                      class="shrink-0 size-4 transition group-hover:translate-x-0.5 group-hover:translate-x-0 group-focus:translate-x-0.5 group-focus:translate-x-0"
                      xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M5 12h14"/>
                      <path d="m12 5 7 7-7 7"/>
                    </svg>
                  </a>
                </p>
              </div>
            </form>
          </div>
          <!-- End Col -->

          <div class="space-y-14">
            <!-- Item -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 size-6 text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                   stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              <div class="grow">
                <h4 class="text-white font-semibold">Our address:</h4>

                <address class="mt-1 text-neutral-400 text-sm not-italic">
                  300 Bath Street, Tay House<br>
                  Glasgow G2 4JR, United Kingdom
                </address>
              </div>
            </div>
            <!-- End Item -->

            <!-- Item -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 size-6 text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                   stroke-linejoin="round">
                <path
                  d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/>
                <path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/>
              </svg>
              <div class="grow">
                <h4 class="text-white font-semibold">Email us:</h4>

                <a
                  class="mt-1 text-neutral-400 text-sm hover:text-neutral-200 focus:outline-none focus:text-neutral-200"
                  href="#mailto:example@site.co" target="_blank">
                  hello@example.so
                </a>
              </div>
            </div>
            <!-- End Item -->

            <!-- Item -->
            <div class="flex gap-x-5">
              <svg class="shrink-0 size-6 text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                   stroke-linejoin="round">
                <path d="m3 11 18-5v12L3 14v-3z"/>
                <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
              </svg>
              <div class="grow">
                <h4 class="text-white font-semibold">We're hiring</h4>
                <p class="mt-1 text-neutral-400">We're thrilled to announce that we're expanding our team and looking
                  for talented individuals like you to join us.</p>
                <p class="mt-2">
                  <a
                    class="group inline-flex items-center gap-x-2 font-medium text-sm text-[#ff0] decoration-2 hover:underline focus:outline-none focus:underline"
                    href="#">
                    Job openings
                    <svg
                      class="shrink-0 size-4 transition group-hover:translate-x-0.5 group-hover:translate-x-0 group-focus:translate-x-0.5 group-focus:translate-x-0"
                      xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M5 12h14"/>
                      <path d="m12 5 7 7-7 7"/>
                    </svg>
                  </a>
                </p>
              </div>
            </div>
            <!-- End Item -->
          </div>
          <!-- End Col -->
        </div>
        <!-- End Grid -->
      </div>
    </div>
    <!-- End Contact -->

    <!-- ========== FOOTER ========== -->
    <footer class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="py-6 border-t border-gray-200 dark:border-neutral-700">
        <div class="flex flex-wrap justify-between items-center gap-2">
          <div>
            <p class="text-xs text-gray-600 dark:text-neutral-400">
              © 2024 {{ $page.props.appName }}.
            </p>
          </div>
          <!-- End Col -->

          <!-- List -->
          <ul class="flex flex-wrap items-center">
            <li
              class="inline-block relative pe-4 text-xs last:pe-0 last-of-type:before:hidden before:absolute before:top-1/2 before:end-1.5 before:-translate-y-1/2 before:size-[3px] before:rounded-full before:bg-gray-400 dark:text-neutral-500 dark:before:bg-neutral-600">
              <a
                class="text-xs text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-none focus:decoration-2 dark:text-neutral-500 dark:hover:text-neutral-400"
                href="#">
                X (Twitter)
              </a>
            </li>
            <li
              class="inline-block relative pe-4 text-xs last:pe-0 last-of-type:before:hidden before:absolute before:top-1/2 before:end-1.5 before:-translate-y-1/2 before:size-[3px] before:rounded-full before:bg-gray-400 dark:text-neutral-500 dark:before:bg-neutral-600">
              <a
                class="text-xs text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-none focus:decoration-2 dark:text-neutral-500 dark:hover:text-neutral-400"
                href="#">
                Dribbble
              </a>
            </li>
            <li class="inline-block pe-4 text-xs">
              <a
                class="text-xs text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-none focus:decoration-2 dark:text-neutral-500 dark:hover:text-neutral-400"
                href="#">
                Github
              </a>
            </li>

            <li class="inline-block">
              <!-- Dark Mode -->
              <ThemeToggle is-rounded />
              <!-- End Dark Mode -->
            </li>
          </ul>
          <!-- End List -->
        </div>
      </div>
    </footer>
    <!-- ========== END FOOTER ========== -->
  </div>
</template>
