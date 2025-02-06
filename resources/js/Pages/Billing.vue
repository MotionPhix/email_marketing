<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { formatDistance, format } from 'date-fns'
import {
  IconCreditCard,
  IconRefresh,
  IconCheck,
  IconX,
  IconAlertCircle,
  IconClock,
  IconReceipt,
  IconArrowUpRight
} from '@tabler/icons-vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import { Switch } from '@/Components/ui/switch'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import { Badge } from '@/Components/ui/badge'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/Components/ui/table"

interface Feature {
  campaign_limit: string
  recipient_limit: string
  email_limit: string
  segment_limit: string
  can_schedule_campaigns?: string
  support_type: string
  analytics?: string
  personalisation?: string
}

interface Plan {
  id: number
  uuid: string
  name: string
  price: number
  features: Feature
  created_at: string
  updated_at: string
}

interface Renewal {
  id: number
  uuid: string
  paychangu_reference: string
  amount: number
  status: 'pending' | 'completed' | 'failed'
  completed_at?: string
  failed_at?: string
  created_at: string
}

interface Subscription {
  id: number
  uuid: string
  status: 'active' | 'cancelled' | 'expired' | 'trial' | 'pending'
  starts_at: string
  ends_at: string
  trial_ends_at?: string
  cancelled_at?: string
  last_payment_at?: string
  auto_renew: boolean
  plan: Plan
  renewals: Renewal[]
}

interface Props {
  currentPlan: Plan
  plans: Plan[]
  subscription?: Subscription
}

const props = defineProps<Props>()

const autoRenew = ref(props.subscription?.auto_renew ?? false)

const toggleAutoRenew = () => {
  router.patch(route('subscription.auto-renew'), {
    auto_renew: autoRenew.value
  }, {
    preserveScroll: true
  })
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-MW', {
    style: 'currency',
    currency: 'MWK'
  }).format(price)
}

const getStatusColor = (status: string) => {
  const colors = {
    active: 'bg-green-500/10 text-green-500',
    trial: 'bg-blue-500/10 text-blue-500',
    cancelled: 'bg-yellow-500/10 text-yellow-500',
    expired: 'bg-red-500/10 text-red-500',
    pending: 'bg-orange-500/10 text-orange-500'
  }
  return colors[status] || 'bg-gray-500/10 text-gray-500'
}

const getStatusIcon = (status: string) => {
  const icons = {
    active: IconCheck,
    trial: IconClock,
    cancelled: IconX,
    expired: IconAlertCircle,
    pending: IconRefresh
  }
  return icons[status] || IconAlertCircle
}

const changePlan = (planUuid: string) => {
  router.post(route('subscription.change-plan'), {
    plan: planUuid
  })
}

// Check if current plan has more features than target plan
const isDowngrade = (targetPlan: Plan) => {
  const currentPlanPrice = props.subscription?.plan.price ?? 0
  return targetPlan.price < currentPlanPrice
}
</script>

<template>
  <AppLayout title="Billing">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Billing & Subscription
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Current Subscription Status -->
        <Card v-if="subscription">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span>Current Subscription</span>
              <Badge :class="getStatusColor(subscription.status)">
                <component
                  :is="getStatusIcon(subscription.status)"
                  class="h-4 w-4 mr-1"
                />
                {{ subscription.status.charAt(0).toUpperCase() + subscription.status.slice(1) }}
              </Badge>
            </CardTitle>

            <CardDescription>
              Your {{ subscription.plan.name }} plan subscription details
            </CardDescription>
          </CardHeader>

          <CardContent class="grid gap-6">
            <div class="grid gap-2">
              <div class="text-2xl font-bold">
                {{ formatPrice(subscription.plan.price) }}<span class="text-sm font-normal text-muted-foreground">/month</span>
              </div>

              <div class="grid gap-1">
                <div class="text-sm text-muted-foreground">
                  <span class="font-medium text-foreground">Started:</span>
                  {{ format(new Date(subscription.starts_at), 'PPP') }}
                </div>
                <div class="text-sm text-muted-foreground">
                  <span class="font-medium text-foreground">Next billing date:</span>
                  {{ format(new Date(subscription.ends_at), 'PPP') }}
                  ({{ formatDistance(new Date(subscription.ends_at), new Date(), { addSuffix: true }) }})
                </div>
              </div>
            </div>

            <div class="grid gap-2">
              <h4 class="font-medium">Features included</h4>
              <ul class="grid gap-2 text-sm">
                <li v-for="(value, key) in subscription.plan.features" :key="key" class="flex items-center">
                  <IconCheck class="h-4 w-4 mr-2 text-green-500" />
                  {{ value }}
                </li>
              </ul>
            </div>

            <!-- Auto-renewal toggle -->
            <div class="flex items-center space-x-2">
              <Switch
                id="auto-renew"
                v-model="autoRenew"
                @update:model-value="toggleAutoRenew"
              />
              <label for="auto-renew" class="text-sm">
                Auto-renew subscription
              </label>
            </div>
          </CardContent>
          <CardFooter>
            <Button variant="outline" class="w-full" @click="router.get(route('billing.invoice'))">
              <IconReceipt class="h-4 w-4 mr-2" />
              Download Invoice
            </Button>
          </CardFooter>
        </Card>

        <!-- Available Plans -->
        <div>
          <h3 class="text-lg font-medium mb-4">Available Plans</h3>
          <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card
              v-for="plan in plans"
              :key="plan.uuid"
              :class="[
                subscription?.plan.uuid === plan.uuid && 'border-primary',
                'relative'
              ]"
            >
              <CardHeader>
                <CardTitle>{{ plan.name }}</CardTitle>
                <CardDescription>
                  {{ formatPrice(plan.price) }}/month
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul class="grid gap-2 text-sm">
                  <li v-for="(value, key) in plan.features" :key="key" class="flex items-center">
                    <IconCheck class="h-4 w-4 mr-2 text-green-500" />
                    {{ value }}
                  </li>
                </ul>
              </CardContent>
              <CardFooter>
                <Button
                  v-if="subscription?.plan.uuid !== plan.uuid"
                  class="w-full"
                  :variant="isDowngrade(plan) ? 'outline' : 'default'"
                  @click="changePlan(plan.uuid)"
                >
                  {{ isDowngrade(plan) ? 'Downgrade' : 'Upgrade' }}
                  <IconArrowUpRight v-if="!isDowngrade(plan)" class="h-4 w-4 ml-2" />
                </Button>
                <Button
                  v-else
                  disabled
                  class="w-full"
                >
                  Current Plan
                </Button>
              </CardFooter>
            </Card>
          </div>
        </div>

        <!-- Payment History -->
        <div v-if="subscription?.renewals.length">
          <h3 class="text-lg font-medium mb-4">Payment History</h3>
          <Card>
            <Table>
              <TableCaption>A list of your recent payments</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Reference</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="renewal in subscription.renewals" :key="renewal.uuid">
                  <TableCell>
                    {{ format(new Date(renewal.created_at), 'PPP') }}
                  </TableCell>
                  <TableCell>{{ renewal.paychangu_reference }}</TableCell>
                  <TableCell>{{ formatPrice(renewal.amount) }}</TableCell>
                  <TableCell>
                    <Badge :class="getStatusColor(renewal.status)">
                      {{ renewal.status.charAt(0).toUpperCase() + renewal.status.slice(1) }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </Card>
        </div>

        <!-- Subscription Alerts -->
        <Alert v-if="subscription?.status === 'trial'" variant="info">
          <IconClock class="h-4 w-4" />
          <AlertTitle>Trial Period</AlertTitle>
          <AlertDescription>
            Your trial will end on {{ format(new Date(subscription.trial_ends_at), 'PPP') }}.
            Add your payment details to continue your subscription after the trial.
          </AlertDescription>
        </Alert>

        <Alert v-if="subscription?.status === 'expired'" variant="destructive">
          <IconAlertCircle class="h-4 w-4" />
          <AlertTitle>Subscription Expired</AlertTitle>
          <AlertDescription>
            Your subscription has expired. Please renew to continue using all features.
          </AlertDescription>
        </Alert>
      </div>
    </div>
  </AppLayout>
</template>
