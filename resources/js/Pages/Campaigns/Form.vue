<script setup lang="ts">
import {Head, Link, router, useForm} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import {Label} from "@/Components/ui/label";
import {Input} from "@/Components/ui/input";
import {Button} from '@/Components/ui/button'
import {PlusIcon, ArrowRightIcon, ArrowLeftIcon} from "@radix-icons/vue";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from "@/Components/ui/select";
import {onBeforeUnmount, onMounted, ref, watch} from "vue";
import {useCampaignStore} from "@/Stores/campaignStore";
import TemplateSelector from "@/Components/TemplateSelector.vue";
import MazTextarea from 'maz-ui/components/MazTextarea'
import { useToast } from 'maz-ui'

const {campaign} = defineProps<{
  campaign: object,
  audiences: []
  templates: []
}>();

const campaignStore = useCampaignStore()
const toast = useToast()

if (campaign.uuid) campaignStore.setCampaign(campaign)

const step = ref(1)

const form = useForm({
  title: campaignStore.campaign?.title || '',
  subject: campaignStore.campaign?.subject || '',
  description: campaignStore.campaign?.description || '',
  template_id: campaignStore.campaign?.template_id || null,
  audience_id: campaignStore.campaign?.audience_id || null,
  scheduled_at: campaignStore.campaign?.scheduled_at || null,
});

// Watch changes and sync with store
watch(() => form, (newForm) => {
  campaignStore.setCampaign(newForm);
}, { deep: true });

// Step Navigation
const nextStep = () => {
  if (step.value === 1) {

    if (! campaign.uuid) {

      form.post(route('campaigns.store'), {
        preserveScroll: true,
        onSuccess: () => {
          step.value = 2;
          localStorage.setItem('campaignStep', step.value);
        },
      });

    } else {

      form.put(route('campaigns.update', campaign.uuid), {
        preserveScroll: true,
        onSuccess: () => {
          step.value = 2;
          localStorage.setItem('campaignStep', step.value);
        },
      });

      step.value = 2
      localStorage.setItem('campaignStep', step.value);
    }

  } else if (step.value === 2) {

    if (! form.template_id) {
      return form.setError('template_id', 'Please select or create a template.');
    }

    if (campaignStore.campaign) {

      Object.keys(campaignStore.campaign).forEach((key) => {
        if (key in form) form[key] = campaignStore.campaign[key];
      });

    }

    step.value = 3;

    localStorage.setItem('campaignStep', step.value);

  }
};

const previousStep = () => {
  if (step.value > 1) {
    step.value--;
    localStorage.setItem('campaignStep', step.value);
  }
};

const show = () => {
  router.visit(route('campaigns.show', campaign.uuid));
};

const onSubmit = () => {
  if (campaign.uuid) {

    form.put(route('campaigns.update', campaign.uuid), {
      preserveScroll: true,

      onError: (errors) => {
        toast.error(errors.name ?? errors.design ?? errors.content)
      },

      onSuccess: () => {

        toast.success("Campaign created.", {
          action: {
            func: () => show(),
            text: 'Show',
            closeToast: true
          }
        })

        Object.keys(campaignStore.campaign).forEach((key) => {
          if (key in form) form[key] = campaignStore.campaign[key];
        });

      },
    })

    return
  }

  form.post(route('campaigns.store'), {
    preserveScroll: true,
  });
};

// Redirect after Template Creation
const redirectToTemplateBuilder = () => {
  campaignStore.setCampaign(form);
  router.visit(route('templates.create', campaign.uuid));
};

onMounted(() => {
  const savedStep = localStorage.getItem('campaignStep');

  if (savedStep) step.value = parseInt(savedStep, 10);

  if (campaignStore.campaign) {
    Object.keys(campaignStore.campaign).forEach((key) => {
      if (key in form) form[key] = campaignStore.campaign[key];
    });
  }
});

onBeforeUnmount(() => {
  localStorage.setItem('campaignStep', step.value);
});
</script>

<template>

  <Head>
    <title>
      Manage campaign
    </title>
  </Head>

  <AppLayout
    :title="`${campaign.uuid ? 'Edit' : 'Create'} campaign`">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ `${campaign.uuid ? 'Edit' : 'Create'} campaign` }}
      </h2>
    </template>

    <template #action>

      <div class="flex gap-2">

        <Button
          @click="redirectToTemplateBuilder"
          size="icon"
          v-if="step === 2">
          <PlusIcon class="size-5" />
        </Button>

        <Button
          as-child
          :disabled="! campaign.template_id">
          <Link
            as="button"
            method="post"
            v-if="campaign.uuid"
            :href="route('campaigns.send', campaign.uuid)">
            Send now
          </Link>
        </Button>
      </div>

    </template>

    <div class="py-12">

      <InputError
        v-if="step === 2" class="px-4"
        :message="form.errors.scheduled_at" />

      <form class="mt-4 px-4">

        <section v-if="step === 1">

          <div class="mb-4">
            <Label for="title">
              Name
            </Label>

            <Input
              v-model="form.title"
              type="text"
              id="title"
              placeholder="Enter campaign name"
            />

            <InputError :message="form.errors.title"/>
          </div>

          <div class="mb-4">
            <Label for="content">
              Subject
            </Label>

            <Input
              v-model="form.subject"
              type="text"
              id="subject"
              placeholder="Campaign subject to be used in email"/>

            <InputError :message="form.errors.subject"/>
          </div>

          <div class="mb-4">
            <Label for="description">
              Description
            </Label>

            <MazTextarea
              v-model="form.description"
              placeholder="Describe what the campaign is about"
              id="description"
            />

            <InputError :message="form.errors.description"/>
          </div>

        </section>

        <div class="mb-4 grid gap-4 py-6" v-if="step === 2">

          <Label>Template</Label>

          <TemplateSelector v-model="form.template_id" :templates="templates" />

          <InputError :message="form.errors.template_id"/>
        </div>

        <div class="mb-4" v-if="step === 3">
          <Label for="audience">
            Audience
          </Label>

          <Select v-model="form.audience_id" id="audience">

            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select an audience"/>
            </SelectTrigger>

            <SelectContent>

              <SelectItem
                v-for="audience in audiences"
                :value="audience.id"
                :key="audience.uuid">
                {{ audience.name }}
              </SelectItem>

            </SelectContent>

          </Select>

          <InputError
            :message="
              form.errors.audience_id ??
              form.errors.title ??
              form.errors.subject??
              form.errors.template_id??
              form.errors.scheduled_at
            "/>
        </div>

        <div class="flex gap-4">
          <Button
            type="button"
            :variant="step ===1 ? 'ghost' : 'secondary'"
            @click="previousStep"
            :disabled="step === 1">
            <ArrowLeftIcon v-if="step !== 1" />
            <span>Back</span>
          </Button>

          <Button
            type="button"
            @click="nextStep"
            v-if="step < 3"
            class="flex items-center gap-2"
            :disabled="form.processing || step === 3">
            <span>{{ step < 3 ? 'Next' : 'Save' }}</span>
            <ArrowRightIcon v-if="step < 3"/>
          </Button>

          <Button
            type="button"
            @click="onSubmit"
            v-else>
            Save
          </Button>
        </div>
      </form>
    </div>

  </AppLayout>
</template>
