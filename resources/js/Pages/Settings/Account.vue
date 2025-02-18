<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  settings: {
    sender_settings: {
      default_sender_name: string | null
      default_sender_email: string | null
      email_verified: boolean
      verification_token: string | null
    }
    company_settings: {
      company_name: string | null
      industry: string | null
      company_size: string | null
      website: string | null
      phone: string | null
      role: string | null
    }
    marketing_settings: {
      email_updates: boolean
      product_news: boolean
      marketing_communications: boolean
    }
  }
}>()

const form = useForm({
  sender_settings: {
    default_sender_name: props.settings?.sender_settings?.default_sender_name ?? '',
    default_sender_email: props.settings?.sender_settings?.default_sender_email ?? ''
  },
  company_settings: {
    company_name: props.settings?.company_settings?.company_name ?? '',
    industry: props.settings?.company_settings?.industry ?? '',
    company_size: props.settings?.company_settings?.company_size ?? '',
    website: props.settings?.company_settings?.website ?? '',
    phone: props.settings?.company_settings?.phone ?? '',
    role: props.settings?.company_settings?.role ?? ''
  },
  marketing_settings: {
    email_updates: props.settings?.marketing_settings?.email_updates ?? true,
    product_news: props.settings?.marketing_settings?.product_news ?? true,
    marketing_communications: props.settings?.marketing_settings?.marketing_communications ?? true
  }
})

const submit = () => {
  form.post(route('settings.account.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Settings updated successfully')
    }
  })
}
</script>

<template>
  <AppLayout title="Account Settings">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Sender Settings Section -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Sender Settings
            <span class="text-red-500" v-if="!settings?.sender_settings?.email_verified">*</span>
          </h3>

          <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
            <div>
              <label for="default_sender_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Sender Name
              </label>
              <input
                id="default_sender_name"
                v-model="form.sender_settings.default_sender_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>

            <div>
              <label for="default_sender_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Sender Email
              </label>
              <input
                id="default_sender_email"
                v-model="form.sender_settings.default_sender_email"
                type="email"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
              <p v-if="!settings?.sender_settings?.email_verified" class="mt-2 text-sm text-red-600">
                Email needs to be verified
              </p>
            </div>
          </div>
        </div>

        <!-- Company Settings Section -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Company Information
          </h3>

          <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
            <div>
              <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Company Name
              </label>
              <input
                id="company_name"
                v-model="form.company_settings.company_name"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>

            <div>
              <label for="industry" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Industry
              </label>
              <input
                id="industry"
                v-model="form.company_settings.industry"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>

            <div>
              <label for="company_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Company Size
              </label>
              <select
                id="company_size"
                v-model="form.company_settings.company_size"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              >
                <option value="">Select size</option>
                <option value="1-10">1-10 employees</option>
                <option value="11-50">11-50 employees</option>
                <option value="51-200">51-200 employees</option>
                <option value="201-500">201-500 employees</option>
                <option value="501+">501+ employees</option>
              </select>
            </div>

            <div>
              <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Website
              </label>
              <input
                id="website"
                v-model="form.company_settings.website"
                type="url"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Phone
              </label>
              <input
                id="phone"
                v-model="form.company_settings.phone"
                type="tel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>

            <div>
              <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Your Role
              </label>
              <input
                id="role"
                v-model="form.company_settings.role"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              />
            </div>
          </div>
        </div>

        <!-- Marketing Settings Section -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Marketing Preferences
          </h3>

          <div class="space-y-4">
            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="email_updates"
                  v-model="form.marketing_settings.email_updates"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="email_updates" class="font-medium text-gray-700 dark:text-gray-300">
                  Email Updates
                </label>
                <p class="text-gray-500 dark:text-gray-400">Get notified about important updates</p>
              </div>
            </div>

            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="product_news"
                  v-model="form.marketing_settings.product_news"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="product_news" class="font-medium text-gray-700 dark:text-gray-300">
                  Product News
                </label>
                <p class="text-gray-500 dark:text-gray-400">Stay updated with new features</p>
              </div>
            </div>

            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="marketing_communications"
                  v-model="form.marketing_settings.marketing_communications"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="marketing_communications" class="font-medium text-gray-700 dark:text-gray-300">
                  Marketing Communications
                </label>
                <p class="text-gray-500 dark:text-gray-400">Receive marketing related communications</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900"
          >
            Save Settings
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
