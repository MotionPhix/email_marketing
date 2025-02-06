<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import { SelectSeparator } from "@/Components/ui/select";
import { CardDescription } from "@/Components/ui/card";
import { useForm } from "@inertiajs/vue3";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import { useToast } from "maz-ui";
import { Link } from '@inertiajs/vue3';

const { settings } = defineProps<{
  settings: {
    uuid: string
    plan_id: number
    timezone: string
    email_from_address: string
    email_from_name: string
    sender_name: string
    plan: {
      uuid: string
      name: string
      price: number
      features: Array<{}>
    }
  }
}>();

const toast = useToast()
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

const updateSettings = () => {
  if (settings.uuid) {
    form.transform(data => {
      const transformedData = {}

      if (data.sender_name) transformedData.sender_name = data.sender_name
      if (data.email_from_name) transformedData.email_from_name = data.email_from_name
      if (data.email_from_address) transformedData.email_from_address = data.email_from_address
      if (data.timezone) transformedData.timezone = data.timezone

      return transformedData
    }).put(route('settings.update', settings.uuid), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Settings updated successfully')
      }
    })
  } else {
    form.post(route('settings.store'), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Settings saved successfully')
      }
    })
  }
};

const cancelSubscription = () => {
  if (settings.uuid) {
    if (confirm('Are you sure you want to cancel your subscription? This action cannot be undone.')) {
      form.delete(route('subscription.cancel', settings.uuid), {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Subscription cancelled successfully')
        }
      })
    }
  }
};
</script>

<template>
  <AppLayout title="Application Settings">
    <template #header>
      <PageTitle title="Application Settings" />
    </template>

    <div class="flex flex-col lg:flex-row gap-6 py-6 px-4 sm:px-6 lg:px-8">
      <!-- Sidebar Navigation -->
      <aside class="w-full lg:w-64 lg:flex-shrink-0">
        <nav class="bg-white rounded-lg shadow-sm p-4 lg:sticky lg:top-20">
          <CardDescription class="mb-4 text-sm font-medium">
            Quick Navigation
          </CardDescription>

          <SelectSeparator class="my-4" />

          <ul class="space-y-2">
            <li>
              <button
                @click="setActiveTab('appSettings')"
                :class="{
                  'bg-indigo-50 text-indigo-600': activeTab === 'appSettings',
                  'hover:bg-gray-50': activeTab !== 'appSettings'
                }"
                class="w-full text-left px-4 py-2 rounded-lg transition-colors duration-200">
                <span class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                  </svg>
                  App Settings
                </span>
              </button>
            </li>

            <li>
              <button
                @click="setActiveTab('subscription')"
                :class="{
                  'bg-indigo-50 text-indigo-600': activeTab === 'subscription',
                  'hover:bg-gray-50': activeTab !== 'subscription'
                }"
                class="w-full text-left px-4 py-2 rounded-lg transition-colors duration-200">
                <span class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                  </svg>
                  Subscription
                </span>
              </button>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content -->
      <article class="flex-1">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <!-- App Settings -->
          <section v-if="activeTab === 'appSettings'" class="max-w-2xl">
            <form @submit.prevent="updateSettings" class="space-y-6">
              <!-- Email From Address -->
              <div class="space-y-2">
                <Label for="email_from_address" class="text-sm font-medium">
                  Email From Address
                </Label>

                <Input
                  type="email"
                  id="email_from_address"
                  v-model="form.email_from_address"
                  :disabled="!settings.plan || settings.plan.name === 'Free'"
                  :class="{'opacity-50': !settings.plan || settings.plan.name === 'Free'}"
                  :placeholder="
                    !settings.plan || settings.plan?.name === 'Free'
                    ? 'Available to Pro and Enterprise users only'
                    : 'Enter an email address to be used in sent emails'
                  " />

                <InputError :message="form.errors.email_from_address" class="text-sm" />
              </div>

              <!-- Email From Name -->
              <div class="space-y-2">
                <Label for="email_from_name" class="text-sm font-medium">
                  Email From Name
                </Label>

                <Input
                  type="text"
                  id="email_from_name"
                  :disabled="!settings.plan || settings.plan.name === 'Free'"
                  :class="{'opacity-50': !settings.plan || settings.plan.name === 'Free'}"
                  v-model="form.email_from_name"
                  :placeholder="
                    !settings.plan || settings.plan?.name === 'Free'
                    ? 'Available to Pro and Enterprise users only'
                    : 'Enter a name to appear in \'From\' in sent emails'
                  " />

                <InputError :message="form.errors.email_from_name" class="text-sm" />
              </div>

              <!-- Sender Name -->
              <div class="space-y-2">
                <Label for="sender_name" class="text-sm font-medium">
                  Sender Name
                </Label>

                <Input
                  type="text"
                  id="sender_name"
                  v-model="form.sender_name"
                  :disabled="!settings.plan || settings.plan.name === 'Free'"
                  :class="{'opacity-50': !settings.plan || settings.plan.name === 'Free'}"
                  :placeholder="
                    !settings.plan || settings.plan?.name === 'Free'
                    ? 'Available to Pro and Enterprise users only'
                    : 'Enter a name to appear in the body of emails.'
                  "  />

                <InputError :message="form.errors.sender_name" class="text-sm" />
              </div>

              <!-- Save Button -->
              <div class="flex justify-end pt-4">
                <Button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-indigo-600 hover:bg-indigo-700">
                  <span v-if="form.processing">Saving...</span>
                  <span v-else>Save Changes</span>
                </Button>
              </div>
            </form>
          </section>

          <!-- Subscription Details -->
          <section v-if="activeTab === 'subscription'" class="max-w-2xl">
            <div class="rounded-lg border border-gray-200">
              <!-- Current Plan Header -->
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Current Subscription</h3>
              </div>

              <div class="p-6">
                <div v-if="settings.plan" class="space-y-6">
                  <!-- Plan Details -->
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="text-xl font-bold text-indigo-600">{{ settings.plan.name }}</h4>
                      <p class="text-sm text-gray-500 mt-1">${{ settings.plan.price }}/month</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-full">
                      Active
                    </span>
                  </div>

                  <!-- Features List -->
                  <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">Included Features:</h5>
                    <ul class="space-y-2">
                      <li
                        v-for="(feature, idx) in settings.plan.features"
                        :key="idx"
                        class="flex items-center gap-2 text-gray-600">
                        <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ feature }}
                      </li>
                    </ul>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <Link
                      :href="route('billing')"
                      class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                      Change Plan
                    </Link>

                    <Button
                      v-if="settings.plan.name !== 'Free'"
                      @click="cancelSubscription"
                      variant="destructive"
                      :disabled="form.processing">
                      Cancel Subscription
                    </Button>
                  </div>
                </div>

                <div v-else class="text-center py-6">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No Active Subscription</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by selecting a subscription plan.</p>
                  <div class="mt-6">
                    <Link
                      :href="route('billing')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                      View Plans
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </article>
    </div>
  </AppLayout>
</template>
