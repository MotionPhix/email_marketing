<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, router} from '@inertiajs/vue3';
import {Card, CardContent, CardHeader, CardTitle} from '@/Components/ui/card'
import {PencilIcon, TrashIcon,} from 'lucide-vue-next'
import {computed, ref} from 'vue';
import {Button} from '@/Components/ui/button'
import Chart from "@/Components/Campaign/Chart.vue";
import PageTitle from "@/Components/PageTitle.vue";

const {campaign, statistics, startDate, endDate} = defineProps<{
  campaign: {
    id?: number
    uuid: string
    title: string
    status: string
    subject?: string
    frequency?: string
    description?: string
    formatted_scheduled_at?: string
    formatted_end_date?: string
    template?: {
      id?: number
      uuid: string
      name: string
    }
    audience?: {
      id?: number
      uuid: string
      name: string
      recipients?: Array<{
        recipient_id?: number
        uuid: string
        name: string
        email: string
      }>
    }
  }
  statistics: {
    stats: {
      bounced: number
      clicked: number
      unique_clicked: number
      unique_opened: number
      spam_report: number
      delivered: number
      opened: number
    }
    chart: object
  }
  startDate: string
  endDate: string
}>();

const displayedRecipients = computed(() =>
  campaign.audience.recipients.slice(0, 5)
);

// Calculate remaining recipients
const remainingRecipients = computed(() =>
  Math.max(0, campaign.audience.recipients.length - 5)
)

const deleteRecipient = (recipient) => {
  router.delete(route('audiences.remove_recipient', {
    audience: campaign.audience.uuid,
    recipient: recipient.uuid
  }), {
    preserveScroll: true,
  })
};

const onStartDate = ref(startDate || '');
const onEndDate = ref(endDate || '');

const updateDateRange = (newStartDate, newEndDate) => {
  onStartDate.value = newStartDate;
  onEndDate.value = newEndDate;

  // Trigger Inertia request with new date range
  router.get(route('campaign.show', {
    campaign: campaign.uuid, start_date: newStartDate, end_date: newEndDate
  }), {
    start_date: newStartDate,
    end_date: newEndDate,
  }, {replace: true});
};
</script>

<template>
  <AppLayout :title="`${campaign.uuid ? 'Edit' : 'Create'} campaign`">

    <template #header>
      <PageTitle :title="campaign.title"/>
    </template>

    <template #action>
      <div class="flex items-center gap-2">
        <Button
          as-child
          max-width="md"
          :close-button="false"
          v-if="campaign?.template?.id && campaign?.audience?.id && !campaign?.formatted_scheduled_at"
          :href="route('campaigns.schedule', campaign.uuid)">
          <GlobalLink
            as="button">
            Schedule
          </GlobalLink>
        </Button>

        <Button
          as-child
          method="post"
          variant="secondary"
          v-if="campaign?.template?.id && campaign?.audience?.id && ! campaign?.formatted_scheduled_at"
          :href="route('campaigns.send', campaign.uuid)">
          <Link
            as="button">
            Send
          </Link>
        </Button>

        <Button
          as-child
          as="button"
          size="icon"
          variant="ghost"
          :href="route('campaigns.edit', campaign.uuid)">
          <Link
            as="button">
            <PencilIcon/>
          </Link>
        </Button>
      </div>

    </template>

    <div class="p-6">
      <div v-if="!campaign"
           class="text-center">
        <p>Loading campaign details...</p>
      </div>

      <div v-else>
        <!-- Campaign Header -->
        <div class="mb-6 capitalize">

          <h1 class="text-2xl font-bold">{{ campaign.title }}</h1>

          <p class="text-gray-500">{{ campaign.subject }}</p>

          <p class="text-sm text-gray-400 border-t py-2">
            {{ campaign.status }}
          </p>

        </div>

        <!-- Campaign Details -->
        <div class="bg-white border p-6 rounded-lg grid gap-2 divide-y">

          <p class="grid">

            <strong>
              Description
            </strong>

            <span class="text-muted-foreground">
              {{ campaign.description || 'No description provided.' }}
            </span>

          </p>

          <div class="grid pt-3 gap-2">
            <div class="flex justify-between items-center">
              <div class="grid">
                <strong>
                  Template
                </strong>

                <i class="text-muted-foreground text-sm">
                  The html content that will be sent as an email to recipients
                </i>
              </div>

              <div class="flex gap-2">
                <Button
                  size="icon"
                  v-if="campaign?.template?.id"
                  :href="route('templates.preview', campaign?.template?.id)" as-child>
                  <GlobalLink as="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                      <path d="M4.75 2.75L7 5L7 8M4.75 3.5C5.16421 3.5 5.5 3.16421 5.5 2.75C5.5 2.33579 5.16421 2 4.75 2C4.33579 2 4 2.33579 4 2.75C4 3.16421 4.33579 3.5 4.75 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M19.25 2.75L17 5L17 8M19.25 3.5C18.8358 3.5 18.5 3.16421 18.5 2.75C18.5 2.33579 18.8358 2 19.25 2C19.6642 2 20 2.33579 20 2.75C20 3.16421 19.6642 3.5 19.25 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M12 2.75L12 7M12 3.5C12.4142 3.5 12.75 3.16421 12.75 2.75C12.75 2.33579 12.4142 2 12 2C11.5858 2 11.25 2.33579 11.25 2.75C11.25 3.16421 11.5858 3.5 12 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M15 16C15 14.3431 13.6569 13 12 13C10.3431 13 9 14.3431 9 16C9 17.6569 10.3431 19 12 19C13.6569 19 15 17.6569 15 16Z" stroke="currentColor" stroke-width="1.5" />
                      <path d="M12 10C18 10 22 16 22 16C22 16 18 22 12 22C6 22 2 16 2 16C2 16 6 10 12 10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                    </svg>
                  </GlobalLink>
                </Button>

                <Button
                  v-if="! campaign?.template?.id"
                  :href="route('campaigns.edit', campaign.uuid)" as-child>
                  <Link as="button">
                    Add template
                  </Link>
                </Button>

                <Button
                  size="icon"
                  v-if="campaign?.template?.id"
                  :href="route('templates.edit', campaign.template.uuid)" as-child>
                  <Link as="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                      <path d="M21 11.5C21 7.02166 21 4.78249 19.6088 3.39124C18.2175 2 15.9783 2 11.5 2C7.02166 2 4.78249 2 3.39124 3.39124C2 4.78249 2 7.02166 2 11.5C2 15.9783 2 18.2175 3.39124 19.6088C4.72972 20.9472 6.85301 20.998 11 20.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                      <path d="M2 7H21" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                      <path d="M10 16H11.5M6 16H7M10 12H16M6 12H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M21.2633 14.8717C20.3622 13.8651 19.8215 13.925 19.2208 14.1048C18.8003 14.1647 17.3585 15.8422 16.7578 16.3765C15.7714 17.3478 14.7806 18.3479 14.7153 18.4784C14.5285 18.781 14.3548 19.3172 14.2707 19.9163C14.1145 20.815 13.8041 21.7815 14.1746 21.9133C14.3548 22.153 15.2559 21.8335 16.157 21.7017C16.7578 21.5938 17.1783 21.474 17.4787 21.2943C17.8992 21.0426 18.6801 20.1559 20.0258 18.8379C20.8697 17.9521 21.6838 17.34 21.9241 16.7409C22.1644 15.8422 21.804 15.3629 21.2633 14.8717Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                  </Link>
                </Button>
              </div>
            </div>

            <span class="text-muted-foreground">
              {{ campaign.template?.name || 'No template assigned.' }}
            </span>
          </div>

          <p class="grid pt-3">
            <strong>Audience</strong>

            <span class="text-muted-foreground">
              {{ campaign.audience?.name || 'No audience assigned.' }}
            </span>
          </p>

          <p class="grid pt-3">
            <strong>Scheduled</strong>

            <span class="text-muted-foreground py-2">
              {{ campaign.formatted_end_date || 'Not scheduled.' }}

              <span v-if="campaign.formatted_scheduled_at">
                 | Until {{ campaign.formatted_scheduled_at }}
              </span>
            </span>

            <span class="text-muted-foreground" v-if="campaign.frequency">
               Running {{ campaign.frequency }}
            </span>
          </p>

        </div>

        <!-- Recipients List -->
        <div
          v-if="campaign.audience.recipients && campaign.audience.recipients.length"
          class="mt-6 px-6">

          <div class="mb-4 flex gap-2">
            <h3 class="grid">
              <span class="text-xl font-semibold">Recipients</span>
              <span class="text-muted-foreground">
                {{ campaign.audience?.name }} audience
              </span>
            </h3>

            <span class="flex-1"></span>

            <Button
              as-child
              variant="secondary"
              :close-button="false"
              padding-classes="p-0"
              panel-classes="rounded-lg overflow-hidden"
              :href="route('audiences.add_recipient', campaign.audience.uuid)"
              max-width="md"
              class="w-8 h-8"
              size="icon">
              <GlobalLink
                as="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                  <path
                    d="M4 12.9955L4 14.5404C4 17.7871 4 19.4104 4.88607 20.5099C5.06508 20.732 5.26731 20.9344 5.48933 21.1135C6.58831 22 8.21082 22 11.4558 22C12.1614 22 12.5141 22 12.8372 21.8859C12.9044 21.8622 12.9702 21.8349 13.0345 21.8042C13.3436 21.6563 13.593 21.4067 14.0919 20.9076L18.8284 16.1686C19.4065 15.5903 19.6955 15.3011 19.8478 14.9334C20 14.5656 20 14.1567 20 13.3388V9.99394C20 6.2208 20 4.33423 18.8284 3.16206C17.8971 2.23022 16.5144 2.03917 14.0919 2M13 21.4997V20.9995C13 18.1696 13 16.7547 13.8787 15.8756C14.7574 14.9965 16.1716 14.9965 19 14.9965H19.5"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path
                    d="M8 9.77273C9.76731 9.77273 11.2 8.30748 11.2 6.5C11.2 4.69252 9.76731 3.22727 8 3.22727M8 9.77273C6.23269 9.77273 4.8 8.30748 4.8 6.5C4.8 4.69252 6.23269 3.22727 8 3.22727M8 9.77273V11M8 3.22727V2M5.0913 4.71488L4.00045 4.04545M12 8.95455L10.9092 8.28512M10.9087 4.71488L11.9996 4.04545M4 8.95455L5.09085 8.28512"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              </GlobalLink>
            </Button>

            <Button
              as-child
              class="h-8"
              :href="route('audiences.show', campaign.audience.uuid)">
              <Link
                as="button">
                All Recipients
              </Link>
            </Button>
          </div>

          <div
            v-for="recipient in displayedRecipients" :key="recipient.uuid"
            class="group grid grid-cols-[25px_minmax(0,1fr)] items-start last:mb-0 last:pb-0">

            <span class="flex size-3 translate-y-6 rounded-full bg-sky-500"/>

            <div class="py-4">

              <section class="font-medium leading-none flex items-center gap-4">

                <h3 class="py-2">
                  {{ recipient.name }}
                </h3>

                <!-- Quick Edit Actions -->
                <div class="gap-2 hidden group-hover:flex">
                  <Button
                    max-width="sm"
                    :close-button="false"
                    padding-classes="p-0"
                    :close-explicitly="true"
                    class="p-1 w-[1.5rem] h-[1.5rem]"
                    size="icon" as-child>
                    <GlobalLink
                      as="button"
                      :data="{ modal: true }"
                      :href="route('recipients.edit', recipient.uuid)">
                      <PencilIcon/>
                    </GlobalLink>
                  </Button>

                  <Button
                    class="p-1 w-[1.5rem] h-[1.5rem]"
                    @click="deleteRecipient(recipient)"
                    size="icon">
                    <TrashIcon/>
                  </Button>
                </div>

              </section>

              <p class="text-sm text-muted-foreground">
                {{ recipient.email }}
              </p>

            </div>

          </div>

        </div>

        <div
          v-else
          class="mt-6 text-gray-500 p-6">

          <p>No recipients assigned to this campaign.</p>

        </div>

      </div>

    </div>

    <div class="p-6 rounded-lg bg-zinc-100 my-12">

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Opens
            </CardTitle>

            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path
                d="M5.00035 7L3.78154 7.81253C2.90783 8.39501 2.47097 8.68625 2.23422 9.13041C1.99747 9.57457 1.99923 10.0966 2.00273 11.1406C2.00696 12.3975 2.01864 13.6782 2.05099 14.9741C2.12773 18.0487 2.16611 19.586 3.29651 20.7164C4.42691 21.8469 5.98497 21.8858 9.10108 21.9637C11.0397 22.0121 12.9611 22.0121 14.8996 21.9637C18.0158 21.8858 19.5738 21.8469 20.7042 20.7164C21.8346 19.586 21.873 18.0487 21.9497 14.9741C21.9821 13.6782 21.9937 12.3975 21.998 11.1406C22.0015 10.0966 22.0032 9.57456 21.7665 9.13041C21.5297 8.68625 21.0929 8.39501 20.2191 7.81253L19.0003 7"
                stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
              <path
                d="M2 10L8.91302 14.1478C10.417 15.0502 11.169 15.5014 12 15.5014C12.831 15.5014 13.583 15.0502 15.087 14.1478L22 10"
                stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
              <path
                d="M4.99998 12V6C4.99998 4.11438 4.99998 3.17157 5.58577 2.58579C6.17156 2 7.11437 2 8.99998 2H15C16.8856 2 17.8284 2 18.4142 2.58579C19 3.17157 19 4.11438 19 6V12"
                stroke="currentColor" stroke-width="1.5"/>
              <path d="M10 10H14M10 6H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
            </svg>

          </CardHeader>

          <CardContent>
            <div class="gap-2 flex items-center">
              <span class="text-2xl text-bold">{{ statistics.stats.opened }}</span>
              |
              <span class="text-sm text-muted-foreground">
                Unique Opens — {{ statistics.stats.unique_opened }}
              </span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Clicks
            </CardTitle>

            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M13.5 5.5V2M13.5 12V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path
                d="M13.5 22C19.5 22 21 17.49 21 12C21 6.50998 19.5 2 13.5 2C7.49993 2 6 6.50996 6 12C6 17.49 7.49993 22 13.5 22Z"
                stroke="currentColor" stroke-width="1.5"/>
              <path
                d="M15 7C15 6.53406 15 6.30109 14.9239 6.11732C14.8224 5.87229 14.6277 5.67761 14.3827 5.57612C14.1989 5.5 13.9659 5.5 13.5 5.5C13.0341 5.5 12.8011 5.5 12.6173 5.57612C12.3723 5.67761 12.1776 5.87229 12.0761 6.11732C12 6.30109 12 6.53406 12 7V7.5C12 7.96594 12 8.19891 12.0761 8.38268C12.1776 8.62771 12.3723 8.82239 12.6173 8.92388C12.8011 9 13.0341 9 13.5 9C13.9659 9 14.1989 9 14.3827 8.92388C14.6277 8.82239 14.8224 8.62771 14.9239 8.38268C15 8.19891 15 7.96594 15 7.5V7Z"
                stroke="currentColor" stroke-width="1.5"/>
              <path d="M6 12L21 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path d="M5 3.16746L4.61888 2M4.02867 5.56746L3 6" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round"/>
            </svg>

          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ statistics.stats.clicked }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Bounces
            </CardTitle>

            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M22 12.999H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path
                d="M20 13.001V10.6578C20 9.84033 20 9.43158 19.8478 9.06404C19.6955 8.69649 19.4065 8.40746 18.8284 7.8294L14.0919 3.09286C13.593 2.59397 13.3436 2.34453 13.0345 2.19672C12.9702 2.16598 12.9044 2.1387 12.8372 2.11499C12.5141 2.00098 12.1614 2.00098 11.4558 2.00098C8.21082 2.00098 6.58831 2.00098 5.48933 2.88705C5.26731 3.06606 5.06508 3.26829 4.88607 3.49031C4 4.58928 4 6.2118 4 9.45682V13.001M13 2.50098V3.00098C13 5.8294 13 7.24362 13.8787 8.1223C14.7574 9.00098 16.1716 9.00098 19 9.00098H19.5"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6 15.999V16.999M10 15.999V21.999M14 15.999V17.999M18 15.999V19.999" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ statistics.stats.bounced }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Delivered
            </CardTitle>
            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path
                d="M22 12.5001C22 12.0087 21.9947 11.0172 21.9842 10.5244C21.9189 7.45886 21.8862 5.92609 20.7551 4.79066C19.6239 3.65523 18.0497 3.61568 14.9012 3.53657C12.9607 3.48781 11.0393 3.48781 9.09882 3.53656C5.95033 3.61566 4.37608 3.65521 3.24495 4.79065C2.11382 5.92608 2.08114 7.45885 2.01576 10.5244C1.99474 11.5101 1.99475 12.4899 2.01577 13.4756C2.08114 16.5412 2.11383 18.0739 3.24496 19.2094C4.37608 20.3448 5.95033 20.3843 9.09883 20.4634C9.90159 20.4836 10.7011 20.4954 11.5 20.4989"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7 8.5L9.94202 10.2394C11.6572 11.2535 12.3428 11.2535 14.058 10.2394L17 8.5"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path
                d="M14 17.5L22 17.5M14 17.5C14 16.7998 15.9943 15.4915 16.5 15M14 17.5C14 18.2002 15.9943 19.5085 16.5 20"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ statistics.stats.delivered }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Spam Reports
            </CardTitle>

            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path
                d="M12.3107 3H11.6893C9.25367 3 8.03584 3 7.03946 3.55252C6.04307 4.10503 5.45164 5.10831 4.26878 7.11486L3.67928 8.11486C2.55976 10.0139 2 10.9635 2 12C2 13.0365 2.55976 13.9861 3.67928 15.8851L4.26878 16.8851C5.45164 18.8917 6.04307 19.895 7.03946 20.4475C8.03584 21 9.25367 21 11.6893 21H12.3107C14.7463 21 15.9642 21 16.9605 20.4475C17.9569 19.895 18.5484 18.8917 19.7312 16.8851L20.3207 15.8851C21.4402 13.9861 22 13.0365 22 12C22 10.9635 21.4402 10.0139 20.3207 8.11485L19.7312 7.11486C18.5484 5.10831 17.9569 4.10503 16.9605 3.55252C15.9642 3 14.7463 3 12.3107 3Z"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M11.992 16H12.001" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path d="M11.9922 13L11.9922 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
            </svg>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ statistics.stats.spam_report }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Unique Clicks
            </CardTitle>

            <svg
              class="h-5 w-5 text-muted-foreground"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path
                d="M10 2.31844C10.7306 2.11067 11.5601 2 12.5 2C18.5 2 20 6.50998 20 12C20 17.49 18.5 22 12.5 22C6.49993 22 5 17.49 5 12C5 11.4906 5.01291 10.9897 5.04113 10.5"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path
                d="M14 7C14 6.53406 14 6.30109 13.9239 6.11732C13.8224 5.87229 13.6277 5.67761 13.3827 5.57612C13.1989 5.5 12.9659 5.5 12.5 5.5C12.0341 5.5 11.8011 5.5 11.6173 5.57612C11.3723 5.67761 11.1776 5.87229 11.0761 6.11732C11 6.30109 11 6.53406 11 7V7.5C11 7.96594 11 8.19891 11.0761 8.38268C11.1776 8.62771 11.3723 8.82239 11.6173 8.92388C11.8011 9 12.0341 9 12.5 9C12.9659 9 13.1989 9 13.3827 8.92388C13.6277 8.82239 13.8224 8.62771 13.9239 8.38268C14 8.19891 14 7.96594 14 7.5V7Z"
                stroke="currentColor" stroke-width="1.5"/>
              <path d="M12.5 5.5V2M12.5 12V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path d="M5.5 12H19.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <circle cx="5.5" cy="5.5" r="2.5" stroke="currentColor" stroke-width="1.5"/>
            </svg>

          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ statistics.stats.unique_clicked }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Chart Section -->
      <!--      :options="options" :series="series"-->
      <div class="mt-12">
        <h2 class="text-xl font-bold">Statistics Overview</h2>
        <Chart :data="statistics.chart"/>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
p {
  margin-bottom: 0.5rem;
}
</style>
