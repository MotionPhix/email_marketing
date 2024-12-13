<script setup lang="ts">
import {ref} from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import {SelectSeparator} from "@/Components/ui/select";
import {CardDescription} from "@/Components/ui/card";
import {useForm} from "@inertiajs/vue3";

const {settings, subscriptions} = defineProps<{
  settings: {
    uuid: string
    subscription_id: number
    timezone: string
    email_from_address: string
    email_from_name: string
    sender_name: string
    subscription: {
      uuid: string
      name: string
      price: number
      features: Array<{}>
    }
  },
  subscriptions: Array<{
    uuid: string
    name: string
    price: number
    features: Array<{}>
  }>
}>();

const activeTab = ref(localStorage.getItem('activeTab') || 'appSettings');

const setActiveTab = (tab) => {
  activeTab.value = tab;
  localStorage.setItem('activeTab', tab);
};

const form = useForm({
  email_from_address: settings.email_from_address,
  email_from_name: settings.email_from_name,
  sender_name: settings.sender_name,
  timezone: settings.timezone
})

const timezones = [
  'UTC', 'GMT', 'EST', 'CST', 'MST', 'PST', 'WET', 'CET', 'EET', 'IST', 'CAT', 'EAT'
]

// const currentSubscription = ref({
//   name: 'Pro Plan',
//   features: ['50,000 emails per month', 'Priority support', 'Advanced analytics']
// });

// const subscriptions = ref([
//   {id: 1, name: 'Basic Plan', features: ['10,000 emails per month', 'Email support']},
//   {id: 2, name: 'Pro Plan', features: ['50,000 emails per month', 'Priority support', 'Advanced analytics']},
//   {id: 3, name: 'Enterprise Plan', features: ['Unlimited emails', 'Dedicated account manager', 'Custom features']}
// ]);

const updateSettings = () => {
  console.log('Settings updated:', form.value);
};

const upgradeSubscription = (subscriptionId) => {
  console.log('Upgrading to subscription ID:', subscriptionId);
};
</script>

<template>
  <AppLayout :title="activeTab === 'appSettings' ? 'Application Settings' : 'Subscription'">
    <template #header>
      <PageTitle :title="activeTab === 'appSettings' ? 'Application Settings' : 'Subscription'" />
    </template>

    <div class="flex py-12">

      <!-- Sidebar Navigation -->
      <aside class="w-64 flex-shrink-0">
        <nav class="p-6 space-y-4">
          <CardDescription>
            Quick Naviagtion
          </CardDescription>

          <SelectSeparator />

          <ul class="space-y-2">
            <li>
              <button
                @click="setActiveTab('appSettings')"
                :class="{'bg-gray-200': activeTab === 'appSettings'}"
                class="w-full text-left p-2 rounded-lg">
                App Settings
              </button>
            </li>

            <li>
              <button
                @click="setActiveTab('subscription')"
                :class="{'bg-gray-200': activeTab === 'subscription'}"
                class="w-full text-left p-2 rounded-lg">
                Subscription
              </button>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content -->
      <article class="flex-1 p-8">
        <!-- App Settings -->
        <section v-if="activeTab === 'appSettings'">

          <form @submit.prevent="updateSettings" class="space-y-6">
            <!-- Email From Address -->
            <div>
              <label for="email_from_address" class="block text-sm font-medium text-gray-700">Email From Address</label>
              <input
                id="email_from_address"
                type="email"
                v-model="form.email_from_address"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="example@domain.com"
                required
              >
            </div>

            <!-- Email From Name -->
            <div>
              <label for="email_from_name" class="block text-sm font-medium text-gray-700">Email From Name</label>
              <input
                id="email_from_name"
                type="text"
                v-model="form.email_from_name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Your Name"
                required
              >
            </div>

            <!-- Sender Name -->
            <div>
              <label for="sender_name" class="block text-sm font-medium text-gray-700">Sender Name</label>
              <input
                id="sender_name"
                type="text"
                v-model="form.sender_name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Sender Name">
            </div>

            <!-- Timezone -->
            <div>
              <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
              <select
                id="timezone"
                v-model="form.timezone"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option
                  v-for="timezone in timezones"
                  :key="timezone" :value="timezone">
                  {{ timezone }}
                </option>
              </select>
            </div>

            <!-- Save Button -->
            <div>
              <button
                type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Save Changes
              </button>
            </div>
          </form>

        </section>

        <!-- Subscription Settings -->
        <section v-if="activeTab === 'subscription'">
          <h1 class="text-2xl font-bold">
            Current Plan
          </h1>

          <p class="mb-4">
            {{ settings.subscription.name }}, with
          </p>

          <div class="p-4 bg-white rounded-lg border">
            <ul class="list-disc pl-6">
              <li v-for="(feature, idx) in settings.subscription.features" :key="idx">
                {{ feature }}
              </li>
            </ul>
          </div>

          <h2 class="text-xl font-bold mt-8 mb-4">
            Change Subscription
          </h2>

          <div class="grid gap-6">
            <div
              :key="subscription.id"
              v-for="subscription in subscriptions"
              class="p-4 bg-white rounded-lg border" >
              <h3 class="text-lg font-bold mb-2">
                {{ subscription.name }}
              </h3>

              <ul class="list-disc pl-4 mb-4">
                <li v-for="(feature, index) in subscription.features" :key="index">
                  {{ feature }}
                </li>
              </ul>

              <button
                @click="upgradeSubscription(subscription.id)"
                class="bg-green-600 text-white px-4 py-2 rounded-lg">
                Upgrade
              </button>
            </div>
          </div>

          <!-- Start -->
          <article class="py-24 relative">
            <div class="absolute h-[36.5rem] w-full top-0 bg-gradient-to-r from-indigo-600 to-violet-600 -z-10"></div>

            <div class="mx-auto w-full">

              <div class="mb-12">

                <h2 class="text-4xl text-center font-bold text-white mb-4">
                  Suitable pricing plans
                </h2>

                <p class="text-gray-300 text-lg text-center leading-6">
                  7 Days free trial. No credit card required.
                </p>

              </div>

              <!--Grid-->
              <div class="space-y-8 gap-6 grid lg:space-y-0 lg:items-center">

                <!--Pricing Card-->
                <div
                  v-for="subscription in subscriptions"
                  class="group relative flex flex-col mx-auto w-full max-w-sm bg-white rounded-2xl shadow-2xl transition-all duration-300  p-8 xl:p-12  ">
                  <div class="border-b border-solid border-gray-200 pb-9 mb-9">
                    <div class="w-16 h-16 rounded-full bg-indigo-50 mx-auto flex justify-center items-center transition-all duration-300 group-hover:bg-indigo-600">
                      <svg class="w-6 h-6 text-indigo-600 transition-all duration-300 group-hover:text-white" viewBox="0 0 31 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.42418 27.2608V12.0502C8.42418 11.8031 8.22388 11.6028 7.97681 11.6028V11.6028C5.55154 11.6028 4.3389 11.6028 3.58547 12.3562C2.83203 13.1097 2.83203 14.3223 2.83203 16.7476V22.116C2.83203 24.5413 2.83203 25.754 3.58547 26.5074C4.3389 27.2608 5.55154 27.2608 7.97681 27.2608H8.42418ZM8.42418 27.2608L8.42418 22.5246C8.42418 15.9141 9.90241 9.38734 12.7507 3.42199V3.42199C13.2066 2.46714 14.4408 2.19891 15.2519 2.87841C16.4455 3.87836 17.135 5.35554 17.135 6.91266V8.08463C17.135 9.40562 18.2059 10.4765 19.5269 10.4765H24.0982C25.1518 10.4765 25.6786 10.4765 26.0736 10.6078C27.0571 10.9346 27.7484 11.8197 27.8273 12.8531C27.859 13.2681 27.7314 13.7792 27.4762 14.8014L25.3389 23.3623C24.8715 25.2346 24.6377 26.1707 23.9399 26.7158C23.242 27.2609 22.2771 27.2609 20.3473 27.2609L8.42418 27.2608Z" stroke="currentColor" stroke-width="2"/>
                      </svg>
                    </div>

                    <h3 class="font-manrope text-2xl font-bold my-7 text-center text-indigo-600">
                      {{ subscription.name }} Plan
                    </h3>

                    <div class="flex items-center justify-center">
                      <span class="font-manrope text-4xl font-medium text-gray-900">
                        ${{ subscription.price }}
                      </span>

                      <span class="text-xl text-gray-500 ml-3">|&nbsp; Month</span>
                    </div>
                  </div>

                  <!--List-->
                  <ul class="mb-12 space-y-6 text-left text-lg text-gray-600 group-hover:text-gray-900">

                    <li
                      v-for="feature in subscription.features"
                      class="flex items-center space-x-3.5">
                      <!-- Icon -->
                      <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                      <span>{{ feature }}</span>
                    </li>

                  </ul>

                  <a
                    href="javascript:;"
                    class="py-2.5 px-5 bg-indigo-50 shadow-sm rounded-full transition-all duration-500 text-base text-indigo-600 font-semibold text-center w-fit mx-auto group-hover:bg-indigo-600 group-hover:text-white">
                    Purchase Plan
                  </a>
                  <!--List End-->
                </div>

              </div>
              <!--Grid End-->
            </div>
          </article>
          <!-- End -->

        </section>
      </article>
    </div>
  </AppLayout>
</template>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
</style>
