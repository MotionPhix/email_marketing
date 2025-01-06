<script setup lang="ts">
import {ref} from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import {SelectSeparator} from "@/Components/ui/select";
import {CardDescription} from "@/Components/ui/card";
import {useForm} from "@inertiajs/vue3";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import {Label} from "@/Components/ui/label";
import {Input} from "@/Components/ui/input";
import {Button} from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import {useToast} from "maz-ui";

const {settings, plans} = defineProps<{
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
  },
  plans: Array<{
    uuid: string
    name: string
    price: number
    features: Array<{}>
  }>
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
      onError: errors => console.log(errors),
      onSuccess: params => {
        console.log(params)
      }
    })
  } else {
    form.post(route('settings.store'), {
      preserveScroll: true,
      onError: errors => {
        console.log(errors)
      },
      onSuccess: params => {
        console.log(params)
      }
    })
  }
};

const upgradeSubscription = (planId) => {
  if (settings.uuid) {
    form.patch(route('settings.payment', { setting: settings.uuid, plan: planId }), {
      preserveScroll: true,
      onSuccess: () => {
        console.log(done)
      },
      onError: params => {
        console.log(params)
      }
    })
  } else {

    toast.error('Can\'t upgrade. Setup your account first')

  }
};
</script>

<template>
  <AppLayout :title="activeTab === 'appSettings' ? 'Application Settings' : 'Subscription Plan'">
    <template #header>
      <PageTitle :title="activeTab === 'appSettings' ? 'Application Settings' : 'Subscription Plan'" />
    </template>

    <div class="flex py-12">

      <!-- Sidebar Navigation -->
      <aside class="w-64 flex-shrink-0">
        <nav class="p-6 space-y-4 sticky top-20">
          <CardDescription>
            Quick Navigation
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
                @click="setActiveTab('plan')"
                :class="{'bg-gray-200': activeTab === 'plan'}"
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
            <div class="grid gap-2">
              <Label
                for="email_from_address">
                Email From Address
              </Label>

              <Input
                type="email"
                id="email_from_address"
                v-model="form.email_from_address"
                :disabled="!settings.plan || settings.plan.name === 'Free'"
                :placeholder="
                  !settings.plan || settings.plan?.name === 'Free'
                  ? 'Available to Pro and Enterprise users only'
                  : 'Enter an email address to be used in sent emails'
                " />

              <InputError :message="form.errors.email_from_address" />
            </div>

            <!-- Email From Name -->
            <div class="grid gap-2">
              <Label
                for="email_from_name">
                Email From Name
              </Label>

              <Input
                type="text"
                id="email_from_name"
                :disabled="!settings.plan || settings.plan.name === 'Free'"
                v-model="form.email_from_name"
                :placeholder="
                  !settings.plan || settings.plan?.name === 'Free'
                  ? 'Available to Pro and Enterprise users only'
                  : 'Enter a name to appear in \'From\' in sent emails'
                " />

              <InputError :message="form.errors.email_from_name" />
            </div>

            <!-- Sender Name -->
            <div class="grid gap-2">
              <Label
                for="sender_name">
                Sender Name
              </Label>

              <Input
                type="text"
                id="sender_name"
                v-model="form.sender_name"
                :disabled="!settings.plan || settings.plan.name === 'Free'"
                :placeholder="
                  !settings.plan || settings.plan?.name === 'Free'
                  ? 'Available to Pro and Enterprise users only'
                  : 'Enter a name to appear in the body of emails.'
                "  />

              <InputError :message="form.errors.sender_name" />
            </div>

            <!-- Timezone -->
            <div class="grid gap-2">
              <Label for="timezone">
                Timezone
              </Label>

              <Select v-model="form.timezone">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select a timezone" />
                </SelectTrigger>

                <SelectContent>

                  <SelectGroup>
                    <SelectLabel>North America</SelectLabel>

                    <SelectItem value="est">
                      Eastern Standard Time (EST)
                    </SelectItem>

                    <SelectItem value="cst">
                      Central Standard Time (CST)
                    </SelectItem>

                    <SelectItem value="mst">
                      Mountain Standard Time (MST)
                    </SelectItem>

                    <SelectItem value="pst">
                      Pacific Standard Time (PST)
                    </SelectItem>

                    <SelectItem value="akst">
                      Alaska Standard Time (AKST)
                    </SelectItem>

                    <SelectItem value="hst">
                      Hawaii Standard Time (HST)
                    </SelectItem>
                  </SelectGroup>

                  <SelectGroup>
                    <SelectLabel>Europe & Africa</SelectLabel>

                    <SelectItem value="gmt">
                      Greenwich Mean Time (GMT)
                    </SelectItem>

                    <SelectItem value="cet">
                      Central European Time (CET)
                    </SelectItem>

                    <SelectItem value="eet">
                      Eastern European Time (EET)
                    </SelectItem>

                    <SelectItem value="west">
                      Western European Summer Time (WEST)
                    </SelectItem>

                    <SelectItem value="cat">
                      Central Africa Time (CAT)
                    </SelectItem>

                    <SelectItem value="eat">
                      East Africa Time (EAT)
                    </SelectItem>
                  </SelectGroup>

                  <SelectGroup>
                    <SelectLabel>Asia</SelectLabel>

                    <SelectItem value="msk">
                      Moscow Time (MSK)
                    </SelectItem>

                    <SelectItem value="ist">
                      India Standard Time (IST)
                    </SelectItem>

                    <SelectItem value="cst_china">
                      China Standard Time (CST)
                    </SelectItem>

                    <SelectItem value="jst">
                      Japan Standard Time (JST)
                    </SelectItem>

                    <SelectItem value="kst">
                      Korea Standard Time (KST)
                    </SelectItem>

                    <SelectItem value="ist_indonesia">
                      Indonesia Central Standard Time (WITA)
                    </SelectItem>
                  </SelectGroup>

                  <SelectGroup>
                    <SelectLabel>Australia & Pacific</SelectLabel>
                    <SelectItem value="awst">
                      Australian Western Standard Time (AWST)
                    </SelectItem>

                    <SelectItem value="acst">
                      Australian Central Standard Time (ACST)
                    </SelectItem>

                    <SelectItem value="aest">
                      Australian Eastern Standard Time (AEST)
                    </SelectItem>

                    <SelectItem value="nzst">
                      New Zealand Standard Time (NZST)
                    </SelectItem>

                    <SelectItem value="fjt">
                      Fiji Time (FJT)
                    </SelectItem>
                  </SelectGroup>

                  <SelectGroup>
                    <SelectLabel>South America</SelectLabel>

                    <SelectItem value="art">
                      Argentina Time (ART)
                    </SelectItem>

                    <SelectItem value="bot">
                      Bolivia Time (BOT)
                    </SelectItem>

                    <SelectItem value="brt">
                      Brasilia Time (BRT)
                    </SelectItem>

                    <SelectItem value="clt">
                      Chile Standard Time (CLT)
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>

              <InputError :message="form.errors.timezone" />

            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
              <Button
                type="submit">
                Save Changes
              </Button>
            </div>
          </form>

        </section>

        <!-- Plan Settings -->
        <section v-if="activeTab === 'plan'">
          <h1 class="text-2xl font-bold">
            Current Plan
          </h1>

          <p class="mb-4" v-if="settings.plan">
            {{ `${settings.plan.name}, with` }}
          </p>

          <p class="mb-4" v-else>
            Setup your account first
          </p>

          <div class="p-4 bg-white rounded-lg border" v-if="settings?.plan">
            <ul class="list-disc pl-6">
              <li v-for="(feature, idx) in settings.plan.features" :key="idx">
                {{ feature }}
              </li>
            </ul>
          </div>

          <h2 class="text-xl font-bold mt-8">
            Suitable pricing plans
          </h2>

          <p class="mb-8">
            7 Days free trial. No credit card required.
          </p>

          <div class="grid gap-6">
            <div
              :key="plan.id"
              v-for="plan in plans"
              class="p-4 bg-white rounded-lg border" >
              <h3 class="text-2xl font-bold text-indigo-600">
                {{ plan.name }}
              </h3>

              <p class="text-gray-500" v-if="plan.name !== 'Free'">
                {{ plan.name === 'Basic' ? 'For small-scale users' : plan.name === 'Pro' ? 'For medium-scale users' : 'For large-scale users' }}
              </p>

              <div class="flex pb-4 mb-4">
                <span class="font-serif  text-4xl font-medium text-gray-900">
                  ${{ plan.price }}
                </span>

                <span class="text-xl text-gray-500">/Month</span>
              </div>

              <ul class="list-disc pl-4 mb-4">
                <li v-for="(feature, index) in plan.features" :key="index">
                  {{ feature }}
                </li>
              </ul>

              <div class="border-t pt-4">

                <button
                  @click="upgradeSubscription(plan.uuid)"
                  class="bg-green-600 text-white px-4 py-2 rounded-lg">
                  {{plan.name === 'Free' ? 'Pick' : 'Upgrade'}}
                </button>

              </div>
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
