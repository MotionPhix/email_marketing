<script setup lang="ts">
import {ref, computed} from 'vue'
import {Head, router} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {Badge} from '@/Components/ui/badge'
import {format} from 'date-fns'
import {IconCheck, IconX} from '@tabler/icons-vue'
import {Separator} from "@/Components/ui/separator";
import {Loader2Icon} from "lucide-vue-next";
import {visitModal, Modal} from '@inertiaui/modal-vue'
import {Card, CardContent} from "@/Components/ui/card";

interface Plan {
  id: number
  name: string
  description: string
  price: number
  currency: string
  billing_period: string
  is_featured: boolean
  features: Record<string, any>
}

interface Subscription {
  id: number
  status: string
  starts_at: string
  ends_at: string | null
  plan: Plan
  renewals: Array<{
    id: number
    amount: number
    currency: string
    status: string
    created_at: string
  }>
  formatted_features: Record<string, string>
}

interface Props {
  plans: Plan[]
  currentPlan: Plan | null
  subscription: Subscription | null
  pendingSubscription: Subscription | null
  canChangeSubscription: boolean
}

const props = defineProps<Props>()

const billingPeriod = ref('monthly') // or 'yearly'
const showConfirmation = ref(false)
const selectedPlan = ref<Plan | null>(null)
const processing = ref(false)
const error = ref('')

const calculatePrice = (plan: Plan, period: string) => {
  const basePrice = plan.price
  if (period === 'yearly') {
    return (basePrice * 12 * 0.8) // 20% discount
  }
  return basePrice
}

const getPriceComparison = (plan: Plan) => {
  const monthlyPrice = plan.price
  const yearlyPrice = calculatePrice(plan, 'yearly') / 12 // Monthly equivalent
  const savings = monthlyPrice - yearlyPrice
  const savingsPercentage = (savings / monthlyPrice) * 100

  return {
    monthly: monthlyPrice,
    yearly: yearlyPrice,
    savings,
    savingsPercentage
  }
}

const annualDiscount = 20 // 20% discount for annual billing

const adjustedPlans = computed(() => {
  return props.plans.map(plan => ({
    ...plan,
    adjustedPrice: billingPeriod.value === 'yearly'
      ? (plan.price * 12 * (1 - annualDiscount / 100))
      : plan.price
  }))
})

const isCurrentPlan = (planId: number) => {
  return props.subscription?.plan.id === planId
}

const formatPrice = (price: number, currency: string) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0
  }).format(price)
}

const handlePlanSelect = (plan: Plan) => {
  selectedPlan.value = plan
  showConfirmation.value = true

  visitModal('#plan_modal')
}

const getFeatureStatus = (feature: string, value: any) => {
  if (typeof value === 'boolean') {
    return value
  }
  return value && value !== '0' && value.toLowerCase() !== 'no'
}

const handleSubscribe = async () => {
  if (!selectedPlan.value) return

  processing.value = true
  error.value = ''

  try {
    await router.post(route('billing.subscribe'), {
      plan_id: selectedPlan.value.id,
      billing_period: billingPeriod.value
    }, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        error.value = null
        console.log('close modal')
      },
      onError: (errors) => {
        error.value = errors.subscription
      }
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to process subscription'
  } finally {
    error.value = null
    processing.value = false
  }
}

const handleCancel = async () => {
  if (!confirm('Are you sure you want to cancel your subscription?')) return

  try {
    await router.delete(route('billing.cancel'))
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to cancel subscription'
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Billing"/>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Current Subscription Info -->
        <div v-if="subscription" class="mb-8">
          <Card>
            <CardHeader>
              <CardTitle class="text-xl">Current Subscription</CardTitle>
              <CardDescription>
                Your subscription details and billing history
              </CardDescription>
            </CardHeader>

            <CardContent>
              <div class="grid gap-6 md:grid-cols-2">
                <!-- Plan Info -->
                <div class="space-y-2">
                  <h3 class="text-sm font-medium text-muted-foreground">Plan</h3>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-semibold">{{ subscription.plan.name }}</span>
                    <Badge
                      :variant="subscription.status === 'active' ? 'default' : 'secondary'"
                    >
                      {{ subscription.status }}
                    </Badge>
                  </div>
                  <p class="text-sm text-muted-foreground">
                    {{ formatPrice(subscription.plan.price, subscription.plan.currency) }}/month
                  </p>
                </div>

                <!-- Period Info -->
                <div class="space-y-2">
                  <h3 class="text-sm font-medium text-muted-foreground">Period</h3>
                  <p class="text-lg">
                    {{ format(new Date(subscription.starts_at), 'MMM d, yyyy') }}
                    <span class="text-muted-foreground mx-2">→</span>
                    {{
                      subscription.ends_at
                        ? format(new Date(subscription.ends_at), 'MMM d, yyyy')
                        : 'Ongoing'
                    }}
                  </p>
                </div>

                <!-- Status & Actions -->
                <div class="space-y-2">
                  <h3 class="text-sm font-medium text-muted-foreground">Actions</h3>
                  <div class="flex gap-2">
                    <Button variant="outline">Manage Billing</Button>
                    <Button variant="destructive">Cancel</Button>
                  </div>
                </div>
              </div>

              <!-- Features -->
              <div class="mt-6">
                <h3 class="text-sm font-medium text-muted-foreground mb-3">Features</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="(value, key) in subscription.formatted_features"
                       :key="key"
                       class="flex items-start gap-2 p-2 rounded-lg bg-muted/50">
                    <IconCheck class="w-5 h-5 text-green-500 shrink-0"/>
                    <span class="text-sm">{{ value }}</span>
                  </div>
                </div>
              </div>

              <!-- Recent Renewals -->
              <div v-if="subscription.renewals?.length" class="mt-6">
                <h3 class="text-sm font-medium text-muted-foreground mb-3">Recent Renewals</h3>
                <div class="space-y-2">
                  <div v-for="renewal in subscription.renewals.slice(0, 3)"
                       :key="renewal.id"
                       class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                    <div class="flex items-center gap-4">
                      <span class="text-sm">
                        {{ format(new Date(renewal.created_at), 'MMM d, yyyy') }}
                      </span>
                      <Badge :variant="renewal.status === 'successful' ? 'default' : 'destructive'">
                        {{ renewal.status }}
                      </Badge>
                    </div>
                    <span class="font-medium">
                      {{ formatPrice(renewal.amount, renewal.currency) }}
                    </span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- In the template, add this after current subscription info -->
        <div v-if="pendingSubscription" class="mb-8">
          <Card>
            <CardHeader>
              <CardTitle class="text-xl">Pending Subscription Change</CardTitle>
              <CardDescription>
                Your subscription will change on {{ format(new Date(pendingSubscription.starts_at), 'PPP') }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium">{{ pendingSubscription.plan.name }}</h4>
                    <p class="text-sm text-muted-foreground">
                      {{ formatPrice(pendingSubscription.price, pendingSubscription.currency) }}/
                      {{ pendingSubscription.billing_period }}
                    </p>
                  </div>
                  <Badge>Pending</Badge>
                </div>

                <!-- Add features list if needed -->
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Available Plans -->
        <div>
          <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight">Available Plans</h2>
            <p class="text-muted-foreground">
              Choose the perfect plan for your needs
            </p>
          </div>

          <!-- Billing Period Toggle -->
          <div class="flex items-center justify-center mb-8">
            <div class="bg-muted p-1 rounded-lg inline-flex">
              <button
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="billingPeriod === 'monthly' ? 'bg-background shadow' : 'hover:bg-background/50'"
                @click="billingPeriod = 'monthly'">
                Monthly
              </button>

              <button
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="billingPeriod === 'yearly' ? 'bg-background shadow' : 'hover:bg-background/50'"
                @click="billingPeriod = 'yearly'">
                Yearly
                <span class="ml-1 text-xs text-green-500">
                  Save {{ annualDiscount }}%
                </span>
              </button>
            </div>
          </div>

          <!-- Plans Grid -->
          <div class="grid gap-6 md:grid-cols-2">
            <Card
              v-for="plan in adjustedPlans"
              :key="plan.id"
              :class="{ 'border-primary': plan.is_featured }">
              <CardHeader>
                <CardTitle>{{ plan.name }}</CardTitle>
                <CardDescription>{{ plan.description }}</CardDescription>
              </CardHeader>

              <CardContent>
                <div class="mb-4">
                  <span class="text-3xl font-bold">
                    {{ formatPrice(plan.adjustedPrice, plan.currency) }}
                  </span>
                  <span class="text-muted-foreground">
                    /{{ billingPeriod === 'monthly' ? 'mo' : 'yr' }}
                  </span>
                </div>

                <!-- Features -->
                <ul class="space-y-2 mb-6">
                  <li v-for="(value, key) in plan.features"
                      :key="key"
                      class="flex items-start gap-2">
                    <div class="mt-1 shrink-0">
                      <IconCheck v-if="getFeatureStatus(key, value)"
                                 class="w-4 h-4 text-green-500"/>
                      <IconX v-else
                             class="w-4 h-4 text-muted-foreground"/>
                    </div>
                    <span class="text-sm">
                      {{ typeof value === 'boolean' ? key.replace('_', ' ') : value }}
                    </span>
                  </li>
                </ul>
              </CardContent>

              <CardFooter>
<!--                <Button-->
<!--                  class="w-full"-->
<!--                  :variant="plan.is_featured ? 'default' : 'outline'"-->
<!--                  :disabled="isCurrentPlan(plan.id)"-->
<!--                  @click="handlePlanSelect(plan)">-->
<!--                  {{ isCurrentPlan(plan.id) ? 'Current Plan' : 'Select Plan' }}-->
<!--                </Button>-->

                <Button
                  class="w-full"
                  :variant="plan.is_featured ? 'default' : 'outline'"
                  :disabled="isCurrentPlan(plan.id) || !canChangeSubscription"
                  @click="handlePlanSelect(plan)" >
                  <span v-if="isCurrentPlan(plan.id)">Current Plan</span>
                  <span v-else-if="!canChangeSubscription">Change Pending</span>
                  <span v-else>Select Plan</span>
                </Button>
              </CardFooter>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <!-- Plan Selection Confirmation Dialog -->
    <Modal
      max-width="md"
      name="plan_modal"
      :close-explicitly="false"
      :close-button="false"
      paddingClasses="0"
      panel-classes="border-none dark:bg-gray-800 rounded-lg bg-gray-100 dark:text-muted-foreground max-h-[80svh] overflow-y-auto scrollbar-none scroll-smooth"
      @update:open="showConfirmation = false">
      <Card>
        <CardHeader>
          <CardTitle>Confirm Subscription</CardTitle>
          <CardDescription>
            Review your subscription details below
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="grid gap-6">
            <!-- Plan Details -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-medium">{{ selectedPlan?.name }}</h4>
                  <p class="text-sm text-muted-foreground">
                    {{ selectedPlan?.description }}
                  </p>
                </div>
                <Badge variant="secondary">
                  {{ billingPeriod === 'monthly' ? 'Monthly' : 'Annual' }}
                </Badge>
              </div>

              <!-- Price Breakdown -->
              <Card class="bg-muted">
                <CardContent class="p-4">
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-sm">Base price</span>
                      <span class="font-medium">
                  {{ selectedPlan && formatPrice(selectedPlan.price, selectedPlan.currency) }}/mo
                </span>
                    </div>

                    <div v-if="billingPeriod === 'yearly'" class="flex justify-between text-sm">
                      <span class="text-green-500">Annual discount (20%)</span>
                      <span class="text-green-500">
                  -{{ selectedPlan && formatPrice(selectedPlan.price * 0.2, selectedPlan.currency) }}/mo
                </span>
                    </div>

                    <Separator class="my-2"/>

                    <div class="flex justify-between">
                      <span class="font-medium">Total</span>
                      <div class="text-right">
                        <div class="font-bold">
                          {{
                            selectedPlan && formatPrice(
                              calculatePrice(selectedPlan, billingPeriod),
                              selectedPlan.currency
                            )
                          }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                          Billed {{ billingPeriod === 'monthly' ? 'monthly' : 'annually' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Features Summary -->
              <div class="space-y-2">
                <h4 class="text-sm font-medium">Included Features</h4>
                <ul class="space-y-2">
                  <li v-for="(value, key) in selectedPlan?.features"
                      :key="key"
                      class="flex items-start gap-2">
                    <IconCheck class="w-4 h-4 text-green-500 mt-0.5"/>
                    <span class="text-sm">
                {{ typeof value === 'boolean' ? key.replace('_', ' ') : value }}
              </span>
                  </li>
                </ul>
              </div>

              <!-- Error Message -->
              <p v-if="error" class="text-sm text-red-500">
                {{ error }}
              </p>
            </div>
          </div>
        </CardContent>

        <Separator class="my-1" />

        <CardFooter class="gap-2 justify-end pt-2">
          <Button variant="outline"
                  :disabled="processing"
                  @click="showConfirmation = false">
            Cancel
          </Button>

          <Button :disabled="processing"
                  @click="handleSubscribe">
            <Loader2Icon v-if="processing"
                         class="mr-2 h-4 w-4 animate-spin"/>
            Confirm Subscription
          </Button>
        </CardFooter>
      </Card>
    </Modal>
  </AppLayout>
</template>
