<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {WavesIcon, UsersIcon, GlobeIcon, LayoutTemplateIcon, SendIcon, CheckIcon} from "lucide-vue-next";

const currentStep = ref(1)

const steps = [
  {
    id: 1,
    title: 'Welcome',
    description: 'Get started with your email marketing journey',
    icon: WavesIcon,
  },
  {
    id: 2,
    title: 'Import Contacts',
    description: 'Import your existing email subscribers',
    icon: UsersIcon,
  },
  {
    id: 3,
    title: 'Setup Sending Domain',
    description: 'Configure your sending domain for better deliverability',
    icon: GlobeIcon,
  },
  {
    id: 4,
    title: 'Create Template',
    description: 'Design your first email template',
    icon: LayoutTemplateIcon,
  },
  {
    id: 5,
    title: 'Test Campaign',
    description: 'Send a test campaign to yourself',
    icon: SendIcon,
  }
]

const progress = computed(() => {
  return (currentStep.value / steps.length) * 100
})

const skipOnboarding = () => {
  router.post(route('onboarding.skip'), {}, {
    preserveScroll: true,
    onSuccess: () => router.visit(route('dashboard'))
  })
}
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
            @click="skipOnboarding"
          >
            Skip onboarding
          </Button>
        </div>
        <Progress :value="progress" class="h-2" />
      </div>

      <!-- Step Content -->
      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Steps Sidebar -->
        <Card class="lg:col-span-1">
          <CardContent class="p-6">
            <nav class="space-y-2">
              <button
                v-for="step in steps"
                :key="step.id"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-colors"
                :class="[
                  step.id === currentStep
                    ? 'bg-primary text-primary-foreground'
                    : 'hover:bg-muted',
                  step.id < currentStep && 'text-muted-foreground'
                ]"
                @click="currentStep = step.id"
              >
                <component
                  :is="step.icon"
                  class="h-5 w-5"
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
              :is="`OnboardingStep${currentStep}`"
              @next="currentStep++"
              @back="currentStep--"
            />
          </KeepAlive>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
