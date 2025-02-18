<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  settings: {
    preferences: {
      language: string
      timezone: string
      track_opens: boolean
      track_clicks: boolean
    }
    notification_settings: {
      email_notifications: boolean
      in_app_notifications: boolean
    }
  }
}>()

const form = useForm({
  preferences: {
    language: props.settings?.preferences?.language ?? 'en',
    timezone: props.settings?.preferences?.timezone ?? 'UTC',
    track_opens: props.settings?.preferences?.track_opens ?? true,
    track_clicks: props.settings?.preferences?.track_clicks ?? true
  },
  notification_settings: {
    email_notifications: props.settings?.notification_settings?.email_notifications ?? true,
    in_app_notifications: props.settings?.notification_settings?.in_app_notifications ?? true
  },
})

const submit = () => {
  form.post(route('settings.preferences.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Preferences updated successfully')
    }
  })
}
</script>

<template>
  <AppLayout title="Email & Notification Preferences">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="space-y-8">
        <!-- General Preferences -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            General Preferences
          </h3>

          <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
            <div>
              <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Language
              </label>
              <select
                id="language"
                v-model="form.preferences.language"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              >
                <option value="en">English</option>
                <option value="es">Español</option>
                <option value="fr">Français</option>
                <!-- Add more languages as needed -->
              </select>
            </div>

            <div>
              <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Timezone
              </label>
              <select
                id="timezone"
                v-model="form.preferences.timezone"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700"
              >
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <!-- Add more timezones -->
              </select>
            </div>
          </div>
        </div>

        <!-- Notification Settings -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Notification Settings
          </h3>

          <div class="space-y-4">
            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="email_notifications"
                  v-model="form.notification_settings.email_notifications"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="email_notifications" class="font-medium text-gray-700 dark:text-gray-300">
                  Email Notifications
                </label>
                <p class="text-gray-500 dark:text-gray-400">Receive important notifications via email</p>
              </div>
            </div>

            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="in_app_notifications"
                  v-model="form.notification_settings.in_app_notifications"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="in_app_notifications" class="font-medium text-gray-700 dark:text-gray-300">
                  In-App Notifications
                </label>
                <p class="text-gray-500 dark:text-gray-400">Show notifications within the application</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Email Tracking Settings -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Email Tracking
          </h3>

          <div class="space-y-4">
            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="track_opens"
                  v-model="form.preferences.track_opens"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="track_opens" class="font-medium text-gray-700 dark:text-gray-300">
                  Track Opens
                </label>
                <p class="text-gray-500 dark:text-gray-400">Track when recipients open your emails</p>
              </div>
            </div>

            <div class="relative flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="track_clicks"
                  v-model="form.preferences.track_clicks"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="track_clicks" class="font-medium text-gray-700 dark:text-gray-300">
                  Track Clicks
                </label>
                <p class="text-gray-500 dark:text-gray-400">Track when recipients click links in your emails</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900"
          >
            Save Preferences
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
