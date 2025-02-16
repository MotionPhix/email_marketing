<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import RegistrationStepper from '@/Pages/Auth/Partials/RegistrationStepper.vue'
import InputError from "@/Components/InputError.vue";
import {TrashIcon, Loader2Icon, PlusIcon} from "lucide-vue-next";

const currentStep = ref(1)
const isLoading = ref(false)

const steps = [
  {
    title: 'Account',
    description: 'Create your personal account'
  },
  {
    title: 'Organization',
    description: 'Set up your organization'
  },
  {
    title: 'Team',
    description: 'Invite your team members'
  },
  {
    title: 'Verify',
    description: 'Verify your email'
  }
]

const form = useForm({
  // Personal Information
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',

  // Organization Information
  organization_name: '',
  organization_size: '',
  industry: '',
  website: '',

  // Team Setup
  team_members: [{ email: '', role: 'member' }],

  terms: false
})

const organizationSizes = [
  {label: '1-10 employees', value: '1-10'},
  {label: '11-50 employees', value: '11-50'},
  {label: '51-200 employees', value: '51-200'},
  {label: '201-500 employees', value: '201-500'},
  {label: '500+ employees', value: '500+'}
]

const industries = [
  {label: 'Technology', value: 'technology'},
  {label: 'E-commerce', value: 'e-commerce'},
  {label: 'Healthcare', value: 'healthcare'},
  {label: 'Education', value: 'education'},
  {label: 'Finance', value: 'finance'},
  {label: 'Marketing', value: 'marketing'},
  {label: 'Retail', value: 'retail'},
  {label: 'Other', value: 'other'}
]

const roles = [
  { value: 'admin', label: 'Administrator' },
  { value: 'editor', label: 'Editor' },
  { value: 'member', label: 'Team Member' }
]

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return form.first_name &&
        form.last_name &&
        form.email &&
        form.password &&
        form.password_confirmation &&
        form.terms
    case 2:
      return form.organization_name &&
        form.organization_size &&
        form.industry
    case 3:
      return form.team_members.every(member => member.email && member.role)
    default:
      return true
  }
})

const addTeamMember = () => {
  form.team_members.push({ email: '', role: 'member' })
}

const removeTeamMember = (index: number) => {
  form.team_members.splice(index, 1)
}

const next = () => {
  if (currentStep.value < steps.length) {
    currentStep.value++
  }
}

const back = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const submit = () => {
  isLoading.value = true

  form.post(route('register'), {
    onSuccess: () => {
      next() // Move to verification step
    },
    onFinish: () => {
      isLoading.value = false
    },
  })
}
</script>

<template>
  <AuthLayout>
    <Head title="Create an account" />

    <div class="lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[450px]">
        <RegistrationStepper
          :current-step="currentStep"
          :steps="steps"
        />

        <form
          @submit.prevent="currentStep === 3 ? submit() : next()"
          class="space-y-6">
          <!-- Step 1: Personal Information -->
          <div v-show="currentStep === 1" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <FormField
                label="First name"
                v-model="form.first_name"
                :error="form.errors.first_name"
                placeholder="Enter your first name"
                :disabled="isLoading"
                required
              />

              <FormField
                label="Last name"
                v-model="form.last_name"
                placeholder="Enter your last name"
                :error="form.errors.last_name"
                :disabled="isLoading"
                required
              />
            </div>

            <FormField
              label="Email"
              v-model="form.email"
              placeholder="m@example.com"
              :message="form.errors.email"
              :disabled="isLoading"
              required
            />

            <FormField
              label="Password"
              v-model="form.password"
              placeholder="Type a strong password"
              :message="form.errors.password"
              :disabled="isLoading"
              type="password"
              required
            />

            <FormField
              label="Confirm password"
              v-model="form.password_confirmation"
              placeholder="Confirm your password"
              :disabled="isLoading"
              type="password"
              required
            />

            <div class="flex items-center space-x-2">
              <Checkbox
                id="terms"
                v-model:checked="form.terms"
                :disabled="isLoading"
                required
              />
              <label
                for="terms"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                I agree to the
                <Link
                  :href="route('terms.show')"
                  class="text-primary hover:underline"
                  target="_blank">
                  terms of service
                </Link>
                and
                <Link
                  :href="route('policy.show')"
                  class="text-primary hover:underline"
                  target="_blank">
                  privacy policy
                </Link>
              </label>
            </div>
          </div>

          <!-- Step 2: Organization Setup -->
          <div v-show="currentStep === 2" class="space-y-4">
            <FormField
              name="organisation_name"
              label="Organization name"
              v-model="form.organization_name"
              :error="form.errors.organization_name"
              :disabled="isLoading"
              required
            />

            <FormField
              label="Organization size"
              v-model="form.organization_size"
              placeholder="Select organization size"
              :error="form.errors.organization_size"
              :options="organizationSizes"
              :disabled="isLoading"
              type="select"
            />

            <FormField
              label="Industry"
              v-model="form.industry"
              placeholder="Select your industry"
              :error="form.errors.industry"
              :disabled="isLoading"
              :options="industries"
            />

            <FormField
              v-model="form.website"
              label="Website (optional)"
              :error="form.errors.website"
              placeholder="https://"
              :disabled="isLoading"
              type="url"
            />
          </div>

          <!-- Step 3: Team Setup -->
          <div v-show="currentStep === 3" class="space-y-4">
            <div class="space-y-4">
              <div
                v-for="(member, index) in form.team_members"
                :key="index"
                class="flex items-end gap-2">
                <FormField
                  class="flex-1"
                  label="Team member email"
                  v-model="member.email"
                  type="email"
                  :disabled="isLoading"
                  required
                />

                <FormField
                  class="w-[150px]"
                  label="Member Role"
                  v-model="member.role"
                  placeholder="Assign a role to this member"
                  :disabled="isLoading"
                  :options="roles"
                  type="select"
                />

                <Button
                  v-if="index > 0"
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="mb-[2px]"
                  @click="removeTeamMember(index)">
                  <TrashIcon class="h-4 w-4" />
                </Button>
              </div>
            </div>

            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="addTeamMember">
              <PlusIcon class="mr-2 h-4 w-4" />
              Add team member
            </Button>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between">
            <Button
              v-if="currentStep > 1"
              type="button"
              variant="outline"
              @click="back"
              :disabled="isLoading">
              Back
            </Button>

            <Button
              type="submit"
              class="ml-auto"
              :disabled="isLoading || !canProceed">
              <Loader2Icon
                v-if="isLoading"
                class="mr-2 h-4 w-4 animate-spin"
              />
              {{ currentStep === 3 ? 'Complete Setup' : 'Continue' }}
            </Button>
          </div>
        </form>

        <p class="px-8 text-center text-sm text-muted-foreground">
          Already have an account?
          <Link
            :href="route('login')"
            class="text-primary hover:underline">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  </AuthLayout>
</template>
