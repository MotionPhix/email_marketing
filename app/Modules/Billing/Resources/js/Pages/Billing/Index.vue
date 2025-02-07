<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import type { BillingPageProps, Plan, Subscription } from '../../types'
import PlanCard from './Components/PlanCard.vue'
import SubscriptionInfo from './Components/SubscriptionInfo.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps<BillingPageProps>()

const confirmingPlanChange = ref(false)
const selectedPlan = ref<Plan | null>(null)

const form = useForm({
  plan_uuid: ''
})

const currentPlan = computed(() => {
  return props.currentSubscription?.plan
})

const isCurrentPlan = (plan: Plan) => {
  return currentPlan.value?.uuid === plan.uuid
}

const confirmPlanChange = (plan: Plan) => {
  selectedPlan.value = plan
  confirmingPlanChange.value = true
}

const subscribeToPlan = () => {
  if (!selectedPlan.value) return

  form.plan_uuid = selectedPlan.value.uuid

  form.post(route('subscriptions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      confirmingPlanChange.value = false
      selectedPlan.value = null
    },
  })
}

const cancelSubscription = () => {
  if (!props.currentSubscription) return

  if (confirm('Are you sure you want to cancel your subscription?')) {
    form.delete(route('subscriptions.destroy', props.currentSubscription.uuid), {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout title="Billing">
    <Head title="Billing" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Current Subscription Info -->
        <SubscriptionInfo
          v-if="currentSubscription"
          :subscription="currentSubscription"
          @cancel="cancelSubscription"
        />

        <!-- Plans Grid -->
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
          <PlanCard
            v-for="plan in plans"
            :key="plan.uuid"
            :plan="plan"
            :is-current="isCurrentPlan(plan)"
            :disabled="isCurrentPlan(plan)"
            @select="confirmPlanChange"
          />
        </div>

        <!-- Confirmation Modal -->
        <Modal v-model="confirmingPlanChange">
          <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
              Subscribe to {{ selectedPlan?.name }}
            </h2>

            <p class="mt-3 text-sm text-gray-600">
              Are you sure you want to subscribe to the {{ selectedPlan?.name }} plan?
              {{ currentSubscription ? "Your current subscription will be cancelled." : "" }}
            </p>

            <div class="mt-6 flex justify-end">
              <SecondaryButton @click="confirmingPlanChange = false">
                Cancel
              </SecondaryButton>

              <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="subscribeToPlan"
              >
                Confirm
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>
