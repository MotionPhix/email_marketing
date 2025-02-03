<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import { PlusIcon, ArrowRightIcon, ArrowLeftIcon, CheckIcon } from "@radix-icons/vue";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from "@/Components/ui/select";
import { onMounted, ref, watch, computed } from "vue";
import { useCampaignStore } from "@/Stores/campaign";
import TemplateSelector from "@/Components/TemplateSelector.vue";
import { toast } from "vue-sonner";
import InputError from "@/Components/InputError.vue";
import { Card, CardContent } from "@/Components/ui/card";

interface Campaign {
  uuid?: string;
  id?: number;
  step: number;
  title?: string;
  subject?: string;
  description?: string;
  template_id?: number | null;
  audience_id?: number | null;
  scheduled_at?: string | null;
}

const props = defineProps<{
  campaign: Campaign;
  audiences: any[];
  templates: any[];
}>();

const campaignStore = useCampaignStore();
const step = ref(props.campaign.step || 1);

const steps = [
  { id: 1, title: 'Campaign Details', description: 'Basic information about your campaign' },
  { id: 2, title: 'Template Selection', description: 'Choose or create your email template' },
  { id: 3, title: 'Audience Selection', description: 'Select who will receive your campaign' }
];

// Initialize store only for existing campaigns
if (props.campaign.uuid) {
  campaignStore.setCampaign(props.campaign);
}

const form = useForm({
  title: props.campaign.title || '',
  subject: props.campaign.subject || '',
  description: props.campaign.description || '',
  template_id: props.campaign.template_id || null,
  audience_id: props.campaign.audience_id || null,
  scheduled_at: props.campaign.scheduled_at || null,
  step: props.campaign.step || 1,
});

// Computed property to check if current step is valid
const currentStepValid = computed(() => {
  switch (step.value) {
    case 1:
      return form.title && form.subject;
    case 2:
      return form.template_id;
    case 3:
      return form.audience_id;
    default:
      return false;
  }
});

// Progress calculation
const progress = computed(() => {
  return (step.value / steps.length) * 100;
});

watch(() => form, (newForm) => {
  campaignStore.setCampaign(newForm);
}, { deep: true });

const handleStepSubmit = async () => {
  if (!currentStepValid.value) {
    toast.error("Please fill in all required fields");
    return;
  }

  try {
    const formData = {
      ...form.data(),
      step: step.value + 1 // Include next step in the update
    };

    if (!props.campaign.uuid) {
      await form.post(route('campaigns.store'), {
        data: formData,
        onSuccess: () => {
          nextStep();
        }
      });
    } else {
      await form.put(route('campaigns.update', props.campaign.uuid), {
        data: formData,
        onSuccess: () => {
          nextStep();
        }
      });
    }
  } catch (error) {
    toast.error("Failed to save campaign details");
  }
};

const nextStep = () => {
  if (step.value < steps.length) {
    step.value++;
    updateCampaignStep(step.value);
  }
};

const previousStep = () => {
  if (step.value > 1) {
    step.value--;
    updateCampaignStep(step.value);
  }
};

const updateCampaignStep = async (newStep: number) => {
  form.step = newStep;

  console.log(form)

  if (props.campaign.uuid) {
    await form.put(route('campaigns.update', props.campaign.uuid), {
      preserveState: true,
      preserveScroll: true,
    });
  }
};

const finalSubmit = () => {
  if (!currentStepValid.value) {
    toast.error("Please complete all required fields");
    return;
  }

  const formData = {
    ...form.data(),
    step: step.value
  };

  if (props.campaign.uuid) {
    form.put(route('campaigns.update', props.campaign.uuid), {
      data: formData,
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Campaign updated successfully", {
          action: {
            label: 'View Campaign',
            onClick: () => router.visit(route('campaigns.show', props.campaign.uuid))
          }
        });
      }
    });
  } else {
    form.post(route('campaigns.store'), {
      data: formData,
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Campaign created successfully");
      }
    });
  }
};

const redirectToTemplateBuilder = () => {
  campaignStore.setCampaign(form);
  router.visit(route('templates.create', props.campaign.uuid));
};

onMounted(() => {
  if (props.campaign.uuid && campaignStore.campaign) {
    Object.keys(campaignStore.campaign).forEach((key) => {
      if (key in form) form[key] = campaignStore.campaign[key];
    });
  }
});
</script>

<template>
  <Head>
    <title>{{ campaign.uuid ? 'Edit' : 'Create' }} Campaign</title>
  </Head>

  <AppLayout :title="`${campaign.uuid ? 'Edit' : 'Create'} Campaign`">
    <template #header>
      <div class="flex flex-col gap-2 pb-2">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          {{ `${campaign.uuid ? 'Edit' : 'New'} Campaign` }}
        </h2>

        <!-- Progress bar -->
        <div class="w-full bg-gray-200 rounded-full h-1 dark:bg-gray-700">
          <div
            class="bg-primary h-1 rounded-full transition-all duration-300"
            :style="{ width: `${progress}%` }">
          </div>
        </div>

        <!-- Steps indicator -->
        <div class="gap-4 flex justify-between">
          <div
            v-for="stepItem in steps"
            class="gap-1 flex items-center"
            :key="stepItem.id">
            <div :class="`
              size-6 rounded-full flex items-center justify-center
                ${step >= stepItem.id ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'}
                ${step === stepItem.id ? 'ring-2 ring-offset-2 ring-primary' : ''}
              `">
              <CheckIcon v-if="step > stepItem.id" class="size-5" />
              <span v-else>{{ stepItem.id }}</span>
            </div>

            <span class="ml-1 text-sm hidden sm:block" :class="{
              'text-primary font-medium': step === stepItem.id,
              'text-gray-600': step !== stepItem.id
            }">
              {{ stepItem.title }}
            </span>
          </div>
        </div>
      </div>
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
          v-if="campaign.template_id && campaign.audience_id">
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

    <div class="py-12 max-w-4xl mx-auto">
      <Card class="mx-4">
        <CardContent class="pt-6">
          <form @submit.prevent="handleStepSubmit">
            <!-- Step 1: Campaign Details -->
            <div v-show="step === 1" class="space-y-6">
              <div class="space-y-4">
                <FormField
                  v-model="form.title"
                  label="Campaign Name"
                  :error="form.errors.title"
                  placeholder="Enter a memorable name for your campaign"
                  required
                />

                <FormField
                  v-model="form.subject"
                  label="Email Subject"
                  :error="form.errors.subject"
                  placeholder="Write a compelling subject line"
                  required
                />

                <FormField
                  type="textarea"
                  v-model="form.description"
                  label="Campaign Description"
                  :error="form.errors.description"
                  placeholder="Describe the purpose of this campaign"
                  rows="4"
                />
              </div>
            </div>

            <!-- Step 2: Template Selection -->
            <div v-if="step === 2" class="space-y-6">
              <div class="space-y-4">
                <Label>Email Template</Label>

                <TemplateSelector
                  v-model="form.template_id"
                  :templates="templates"
                />

                <InputError :message="form.errors.template_id"/>
              </div>
            </div>

            <!-- Step 3: Audience Selection -->
            <div v-if="step === 3" class="space-y-6">
              <div class="space-y-4">
                <Label>Target Audience</Label>
                <Select v-model="form.audience_id" required>
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Choose your audience"/>
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem
                      v-for="audience in audiences"
                      :key="audience.uuid"
                      :value="audience.id">
                      {{ audience.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>

                <InputError :message="form.errors.audience_id"/>
              </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
              <Button
                type="button"
                variant="outline"
                @click="previousStep"
                :disabled="step === 1">
                <ArrowLeftIcon class="mr-2 size-4" />
                Previous
              </Button>

              <Button
                type="submit"
                :disabled="!currentStepValid || form.processing"
                v-if="step < steps.length">
                Next
                <ArrowRightIcon class="ml-2 size-4" />
              </Button>

              <Button
                type="button"
                @click="finalSubmit"
                :disabled="!currentStepValid || form.processing"
                v-else>
                Complete
                <CheckIcon class="ml-2 size-4" />
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
