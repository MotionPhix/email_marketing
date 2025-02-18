<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  IconUserCircle,
  IconBell,
  IconPaint,
  IconBuildingArch,
  IconMail,
  IconGlobe
} from '@tabler/icons-vue'

const props = defineProps<{
  settings: {
    sender_settings: {
      default_sender_name: string | null
      default_sender_email: string | null
      email_verified: boolean
    }
    company_settings: {
      company_name: string | null
    }
    branding_settings: {
      logo_url: string | null
      primary_color: string
      accent_color: string
    }
    notification_settings: {
      email_notifications: boolean
      in_app_notifications: boolean
    }
    preferences: {
      language: string
      timezone: string
    }
    email_settings: {
      track_opens: boolean
      track_clicks: boolean
    }
  }
}>()

const settingsSections = [
  {
    name: 'Email Settings',
    description: 'Configure your sender details and email tracking preferences',
    href: route('settings.account'),
    icon: IconMail,
    status: props.settings.sender_settings.email_verified ? 'Verified' : 'Needs verification',
    statusColor: props.settings.sender_settings.email_verified ? 'text-green-500' : 'text-yellow-500',
    current: props.settings.sender_settings.default_sender_email ?? 'Not configured'
  },
  {
    name: 'Company Profile',
    description: 'Manage your company information and business details',
    href: route('settings.account'),
    icon: IconBuildingArch,
    status: props.settings.company_settings.company_name ? 'Configured' : 'Not configured',
    statusColor: props.settings.company_settings.company_name ? 'text-green-500' : 'text-gray-500',
    current: props.settings.company_settings.company_name ?? 'Not set'
  },
  {
    name: 'Branding',
    description: 'Customize your brand colors and logo',
    href: route('settings.account'),
    icon: IconPaint,
    status: props.settings.branding_settings.logo_url ? 'Custom branding' : 'Default branding',
    statusColor: props.settings.branding_settings.logo_url ? 'text-green-500' : 'text-gray-500',
    current: `Primary: ${props.settings.branding_settings.primary_color}`
  },
  {
    name: 'Notifications',
    description: 'Set up your notification preferences',
    href: route('settings.preferences'),
    icon: IconBell,
    status: props.settings.notification_settings.email_notifications ||
    props.settings.notification_settings.in_app_notifications ? 'Active' : 'Disabled',
    statusColor: props.settings.notification_settings.email_notifications ||
    props.settings.notification_settings.in_app_notifications ? 'text-green-500' : 'text-gray-500',
    current: `Email: ${props.settings.notification_settings.email_notifications ? 'On' : 'Off'},
              In-app: ${props.settings.notification_settings.in_app_notifications ? 'On' : 'Off'}`
  },
  {
    name: 'Preferences',
    description: 'Configure your language and timezone settings',
    href: route('settings.preferences'),
    icon: IconGlobe,
    status: 'Active',
    statusColor: 'text-green-500',
    current: `${props.settings.preferences.language.toUpperCase()} - ${props.settings.preferences.timezone}`
  }
]
</script>

<template>
  <AppLayout title="Settings">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Warning for unverified email -->
            <div v-if="!settings.sender_settings.email_verified"
                 class="rounded-md bg-yellow-50 dark:bg-yellow-900/50 p-4 mb-6">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-100">
                    Action Required
                  </h3>
                  <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-200">
                    <p>
                      Your sender email needs to be verified before you can send campaigns.
                      <Link
                        :href="route('settings.account')"
                        class="font-medium text-yellow-700 underline dark:text-yellow-200 hover:text-yellow-600 dark:hover:text-yellow-300"
                      >
                        Verify now
                      </Link>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="section in settingsSections"
                :key="section.name"
                class="relative group py-6 first:pt-0 last:pb-0"
              >
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <component
                      :is="section.icon"
                      class="h-6 w-6 text-gray-600 dark:text-gray-400"
                    />
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      <Link :href="section.href">
                        <span class="absolute inset-0" aria-hidden="true" />
                        {{ section.name }}
                      </Link>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ section.description }}
                    </p>
                    <div class="mt-2 flex items-center space-x-4">
                      <span :class="[section.statusColor, 'text-xs font-medium']">
                        {{ section.status }}
                      </span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ section.current }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-shrink-0 self-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
