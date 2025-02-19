<script setup lang="ts">
import {type Component, computed, ref, watch} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  UsersIcon,
  GlobeIcon,
  LayoutTemplateIcon,
  SendIcon,
  CheckIcon,
  HandshakeIcon,
  Settings2Icon
} from "lucide-vue-next";
import OnboardingStep1 from "@/Pages/OnBoarding/Partials/OnboardingStep1.vue";
import OnboardingStep2 from "@/Pages/OnBoarding/Partials/OnboardingStep2.vue";
import OnboardingStep3 from "@/Pages/OnBoarding/Partials/OnboardingStep3.vue";
import OnboardingStep4 from "@/Pages/OnBoarding/Partials/OnboardingStep4.vue";
import OnboardingStep5 from "@/Pages/OnBoarding/Partials/OnboardingStep5.vue";
import OnboardingStep6 from "@/Pages/OnBoarding/Partials/OnboardingStep6.vue";
import {toast} from "vue-sonner";

const props = defineProps<{
  progress: {
    current_step: number;
    completed_steps: number[];
    skipped_steps: number[];
    form_data: Record<string, any>;
    is_completed: boolean;
  };
  userSettings: object;
  required_steps: number[];
}>()

const currentStep = ref(props.progress.current_step)
let currentComponent: Component = OnboardingStep1

console.log(props.progress)

const steps = [
  {
    id: 1,
    title: 'Welcome',
    description: 'Get started with your email marketing journey',
    icon: HandshakeIcon,
  },
  {
    id: 2,
    title: 'Account Setup',
    description: 'Configure your email sender settings',
    icon: Settings2Icon,
    required: true
  },
  {
    id: 3,
    title: 'Import Contacts',
    description: 'Import your existing email subscribers',
    icon: UsersIcon,
  },
  {
    id: 4,
    title: 'Setup Sending Domain',
    description: 'Configure your sending domain for better deliverability',
    icon: GlobeIcon,
  },
  {
    id: 5,
    title: 'Create Template',
    description: 'Design your first email template',
    icon: LayoutTemplateIcon,
  },
  {
    id: 6,
    title: 'Test Index',
    description: 'Send a test campaign to yourself',
    icon: SendIcon,
  }
]

const progress = computed(() => {
  const completedCount = props.progress.completed_steps.length
  const skippedCount = props.progress.skipped_steps.length
  return ((completedCount + skippedCount) / steps.length) * 100
})

const isStepCompleted = (stepId: number) => {
  return props.progress.completed_steps.includes(stepId)
}

const isStepSkipped = (stepId: number) => {
  return props.progress.skipped_steps.includes(stepId)
}

const canAccessStep = (stepId: number) => {
  if (stepId === 1) return true

  // Check if all previous required steps are completed
  const previousRequiredSteps = steps
    .filter(s => s.id < stepId && s.required)
    .map(s => s.id)

  return previousRequiredSteps.every(step =>
    isStepCompleted(step) || isStepSkipped(step)
  )
}

const skipOnboarding = () => {
  router.post(route('onboarding.skip', currentStep.value), {}, {
    preserveScroll: true,
    onSuccess: () => router.visit(route('dashboard')),
    onError: (err) => {
      toast.error(err.step)
    }
  })
}

// Handle back/next navigation
const handleBack = () => {
  const previousStep = steps
    .filter(s => s.id < currentStep.value)
    .reverse()
    .find(s => !isStepCompleted(s.id))?.id

  if (previousStep) {
    currentStep.value = previousStep
  }
}

const handleNext = () => {
  const nextStep = steps
    .filter(s => s.id > currentStep.value)
    .find(s => !isStepCompleted(s.id) && !isStepSkipped(s.id))?.id

  if (nextStep) {
    currentStep.value = nextStep
  }
}

watch(currentStep, (newStep) => {
  if (!canAccessStep(newStep)) {
    toast.error('Please complete previous required steps first')
    return
  }

  switch (newStep) {
    case 1:
      return currentComponent = OnboardingStep1

    case 2:
      return currentComponent = OnboardingStep2

    case 3:
      return currentComponent = OnboardingStep3

    case 4:
      return currentComponent = OnboardingStep4

    case 5:
      return currentComponent = OnboardingStep5

    case 6:
      return currentComponent = OnboardingStep6
  }
}, { immediate: true })
</script>

<template>
  <AppLayout>
    <Head title="Welcome" />

    <div class="container max-w-5xl py-8">
      <!-- Progress Header -->
      <div class="mb-8 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-3xl font-bold tracking-tight">
            {{ steps[currentStep - 1].title }}
          </h2>

          <Button
            variant="ghost"
            @click="skipOnboarding">
            Skip onboarding
          </Button>
        </div>

        <Progress v-model="progress" class="h-2" />
      </div>

      <!-- Step Content -->
      <div class="grid gap-4 lg:grid-cols-3">
        <!-- Steps Sidebar -->
        <Card class="lg:col-span-1">
          <CardContent class="p-6">
            <nav class="space-y-2">
              <button
                v-for="step in steps"
                :key="step.id"
                class="flex w-full gap-4 rounded-lg px-3 py-2 text-left transition-colors"
                :class="[
                  step.id === currentStep
                    ? 'bg-primary text-primary-foreground'
                    : 'hover:bg-muted',
                  step.id < currentStep && 'text-muted-foreground'
                ]"
                @click="canAccessStep(step.id) && (currentStep = step.id)"
                :disabled="!canAccessStep(step.id)">
                <component
                  :is="step.icon"
                  class="h-5 w-5 shrink-0"
                />
                <div>
                  <div class="text-sm font-medium">
                    {{ step.title }}
                  </div>
                  <div class="text-xs">
                    {{ step.description }}
                  </div>
                </div>
                <CheckIcon
                  v-if="step.id < currentStep"
                  class="ml-auto h-4 w-4"
                />
              </button>
            </nav>
          </CardContent>
        </Card>

        <!-- Step Components -->
        <div class="lg:col-span-2">
          <KeepAlive>
            <component
              :is="currentComponent"
              @next="handleNext"
              @back="handleBack"
              :disabled-back="isStepCompleted(currentStep)"
              :form-data="props.progress?.form_data[`step_${currentStep}`]"
            />
          </KeepAlive>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
