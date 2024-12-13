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
    }
  },
  subscriptions: Array<{
    uuid: string
    name: string
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
              <li v-for="(feature, index) in currentSubscription.features" :key="index">
                {{ feature }}
              </li>
            </ul>
          </div>

          <h2 class="text-xl font-bold mt-8 mb-4">
            Change Subscription
          </h2>

          <div class="grid grid-cols-2 gap-6">
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
