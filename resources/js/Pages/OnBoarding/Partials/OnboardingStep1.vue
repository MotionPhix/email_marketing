<script setup lang="ts">
import {BarChartIcon, MailIcon, UsersIcon, ZapIcon, ArrowRightIcon} from "lucide-vue-next";
import {router} from "@inertiajs/vue3";

defineEmits(['next'])

const features = [
  {
    title: 'Email Campaigns',
    description: 'Create and send beautiful email campaigns',
    icon: MailIcon,
  },
  {
    title: 'Subscriber Management',
    description: 'Organize and segment your subscribers',
    icon: UsersIcon,
  },
  {
    title: 'Analytics',
    description: 'Track engagement and campaign performance',
    icon: BarChartIcon,
  },
  {
    title: 'Automation',
    description: 'Set up automated email sequences',
    icon: ZapIcon,
  }
]
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Welcome to EmailMarketing</CardTitle>
      <CardDescription>
        Let's get you set up with everything you need to start sending amazing emails
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2">
        <Card
          v-for="feature in features"
          :key="feature.title"
          class="border-2">
          <CardHeader>
            <div class="flex items-center gap-2">
              <component
                :is="feature.icon"
                class="h-5 w-5 text-primary"
              />
              <CardTitle class="text-base">
                {{ feature.title }}
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">
              {{ feature.description }}
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="router.visit(route('dashboard'), { replace: true })"
        >
          Go to dashboard
        </Button>
        <Button @click="$emit('next')">
          Start setup
          <ArrowRightIcon class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
