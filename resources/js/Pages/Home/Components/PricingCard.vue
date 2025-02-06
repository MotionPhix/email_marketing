<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { IconCheck } from '@tabler/icons-vue'

interface Feature {
  text: string
  icon: boolean
}

interface Plan {
  uuid: string
  name: string
  price: number
  features: Array<any>
}

interface Props {
  plan: Plan
  currentPlan: Plan | null
  features: Feature[]
  highlighted?: boolean
}

const props = defineProps<Props>()

const subscribe = () => {
  router.post(route('subscription.create', props.plan.uuid))
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-MW', {
    style: 'currency',
    currency: 'MWK'
  }).format(price)
}
</script>

<template>
  <Card :class="[
    'relative overflow-hidden',
    highlighted ? 'border-primary shadow-lg' : ''
  ]">
    <div
      v-if="highlighted"
      class="absolute top-0 right-0 px-3 py-1 bg-primary text-primary-foreground text-sm rounded-bl-lg"
    >
      Recommended
    </div>

    <CardHeader>
      <div
        v-if="currentPlan?.uuid === plan.uuid"
        class="absolute -top-4 left-0 right-0 text-center">
        <span class="bg-primary text-primary-foreground px-4 py-1 rounded-full text-sm">
          Current Plan
        </span>
      </div>

      <h3 class="font-headings text-2xl">{{ plan.name }}</h3>
      <div class="mt-2">
        <span class="text-4xl font-bold">{{ formatPrice(plan.price) }}</span>
        <span class="text-muted-foreground">/month</span>
      </div>
    </CardHeader>

    <CardContent>
      <ul class="space-y-3">
        <li
          v-for="(feature, index) in features"
          :key="index"
          class="flex items-center gap-x-3">
          <IconCheck
            v-if="feature.icon"
            class="h-5 w-5 text-primary flex-shrink-0"
          />
          <span
            v-else
            class="h-5 w-5 flex-shrink-0"
          />
          <span :class="!feature.icon && 'text-muted-foreground'">
            {{ feature.text }}
          </span>
        </li>
      </ul>
    </CardContent>

    <CardFooter>
      <Button
        class="w-full"
        :variant="highlighted ? 'default' : 'outline'"
        :disabled="currentPlan?.uuid === plan.uuid"
        @click="subscribe"
      >
        {{ currentPlan?.uuid === plan.uuid ? 'Current Plan' : 'Subscribe Now' }}
      </Button>
    </CardFooter>
  </Card>
</template>
