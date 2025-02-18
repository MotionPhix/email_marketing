<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { toast } from 'vue-sonner'
import PhoneInput from "@/Components/PhoneInput.vue";

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
              <FormField
                label="Sender Name"
                placeholder="The name to appear in the sent email campaigns"
                v-model="form.sender_settings.default_sender_name"
                required
              />
            </div>

            <div>
              <FormField
                label="Sender Email"
                v-model="form.sender_settings.default_sender_email"
                placeholder="The email address to use when sending email campaigns"
                type="email"
                required
              />

              <p
                v-if="!settings?.sender_settings?.email_verified"
                class="text-sm text-red-600">
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
              <FormField
                label="Company Name"
                v-model="form.company_settings.company_name"
                placeholder="Your company name to appear in the footer of sent emails"
              />
            </div>

            <div>
              <FormField
                label="Industry"
                v-model="form.company_settings.industry"
                placeholder="The category your company falls in"
              />
            </div>

            <div>
              <FormField
                type="select"
                label="Company Size"
                placeholder="Specify your company size"
                v-model="form.company_settings.company_size"
                :options="[
                  { value: '1-10', label: '1-10 employees' },
                  { value: '11-50', label: '11-50 employees' },
                  { value: '51-200', label: '51-200 employees' },
                  { value: '201-500', label: '201-500 employees' },
                  { value: '501+', label: '501+ employees' }
                ]"
              />
            </div>

            <div>
              <FormField
                label="Website"
                v-model="form.company_settings.website"
                type="url"
                placeholder="Your company website if available"
              />
            </div>

            <div>
              <Label
                for="phone"
                class="block mb-2">
                Phone
              </Label>

              <PhoneInput
                id="phone"
                v-model="form.company_settings.phone"
              />
            </div>

            <div>
              <FormField
                label="Your Role"
                v-model="form.company_settings.role"
                placeholder="Your role in this system"
                readonly
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
                <Checkbox
                  id="email_updates"
                  v-model:checked="form.marketing_settings.email_updates"
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
                <Checkbox
                  id="product_news"
                  v-model:checked="form.marketing_settings.product_news"
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
                <Checkbox
                  id="marketing_communications"
                  v-model:checked="form.marketing_settings.marketing_communications"
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
          <Button
            type="submit">
            Save Settings
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
