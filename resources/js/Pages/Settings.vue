<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import { useForm } from '@inertiajs/vue3'

const {settings} = defineProps<{
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
  }
}>();

const form = useForm({
  email_from_address: settings.email_from_address,
  email_from_name: settings.email_from_name,
  sender_name: settings.sender_name,
  timezone: settings.timezone
})

const timezones = [
  'UTC', 'GMT', 'EST', 'CST', 'MST', 'PST', 'WET', 'CET', 'EET', 'IST', 'CAT', 'EAT'
]

const updateSettings = () => {
  form.put(route('settings.update', settings.uuid), {
    onSuccess: () => {
      alert('Settings updated successfully!')
    }
  })
}
</script>

<template>
  <AppLayout title="Settings">
    <template #header>
      <PageTitle title="Settings" />
    </template>

    <div class="py-12">

      <h1 class="text-2xl font-bold mb-6">Settings</h1>

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

    </div>
  </AppLayout>
</template>
