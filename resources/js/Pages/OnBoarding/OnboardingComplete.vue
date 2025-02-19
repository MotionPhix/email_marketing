<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import confetti from 'canvas-confetti'
import {onMounted} from "vue";
import {
  BarChartIcon,
  BookOpenIcon,
  PenToolIcon,
  PlayCircleIcon,
  UsersIcon,
  BadgeCheckIcon,
  CheckCircleIcon,
  ArrowRightIcon,
  ZapIcon
} from "lucide-vue-next";

// Trigger confetti animation on mount
onMounted(() => {
  confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 }
  })
})

const nextSteps = [
  {
    title: 'Create Your First Index',
    description: 'Start crafting your first email campaign',
    icon: PenToolIcon,
    href: route('campaigns.create')
  },
  {
    title: 'Import More Contacts',
    description: 'Add more subscribers to your lists',
    icon: UsersIcon,
    href: route('subscribers.import')
  },
  {
    title: 'Setup Automation',
    description: 'Create automated email sequences',
    icon: ZapIcon,
    href: route('automations.index')
  },
  {
    title: 'Explore Analytics',
    description: 'View your campaign performance',
    icon: BarChartIcon,
    href: route('analytics.index')
  }
]

const resources = [
  {
    title: 'Documentation',
    description: 'Learn how to use all features',
    icon: BookOpenIcon,
    href: '/docs'
  },
  {
    title: 'Video Tutorials',
    description: 'Watch step-by-step guides',
    icon: PlayCircleIcon,
    href: '/tutorials'
  }
]
</script>

<template>
  <Card>
    <CardHeader class="text-center">
      <CardTitle class="text-2xl">
        🎉 You're All Set!
      </CardTitle>
      <CardDescription>
        Your email marketing platform is ready to go
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <!-- Success Stats -->
      <div class="grid gap-4 sm:grid-cols-3">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Contacts Imported
            </CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ subscriberCount }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Domain Status
            </CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">Verified</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Setup Complete
            </CardTitle>
            <BadgeCheckIcon class="h-4 w-4 text-primary" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">100%</div>
          </CardContent>
        </Card>
      </div>

      <!-- Next Steps -->
      <div class="space-y-4">
        <h3 class="text-lg font-medium">Next Steps</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <Link
            v-for="step in nextSteps"
            :key="step.title"
            :href="step.href"
            class="group rounded-lg border p-4 hover:bg-muted"
          >
            <div class="flex items-center gap-3">
              <component
                :is="step.icon"
                class="h-5 w-5 text-primary"
              />
              <div>
                <h4 class="font-medium group-hover:text-primary">
                  {{ step.title }}
                </h4>
                <p class="text-sm text-muted-foreground">
                  {{ step.description }}
                </p>
              </div>
              <ArrowRightIcon class="ml-auto h-4 w-4 opacity-0 transition-opacity group-hover:opacity-100" />
            </div>
          </Link>
        </div>
      </div>

      <!-- Helpful Resources -->
      <div class="space-y-4">
        <h3 class="text-lg font-medium">Helpful Resources</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <Link
            v-for="resource in resources"
            :key="resource.title"
            :href="resource.href"
            class="flex items-center gap-3 rounded-lg border p-4 hover:bg-muted"
          >
            <component
              :is="resource.icon"
              class="h-5 w-5 text-muted-foreground"
            />
            <div>
              <h4 class="font-medium">{{ resource.title }}</h4>
              <p class="text-sm text-muted-foreground">
                {{ resource.description }}
              </p>
            </div>
          </Link>
        </div>
      </div>

      <div class="flex justify-center">
        <Button
          size="lg"
          as="Link"
          :href="route('dashboard')">
          Go to Dashboard
          <ArrowRightIcon class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
