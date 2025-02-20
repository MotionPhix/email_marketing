<script setup lang="ts">
import {ref} from 'vue'
import {Head, useForm, Link} from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import {Loader2Icon} from "lucide-vue-next"

const props = defineProps<{
  token: string
  email: string
  team: {
    name: string
  }
  inviter: {
    name: string
  }
}>()

const form = useForm({
  first_name: '',
  last_name: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const isLoading = ref(false)

const submit = () => {
  isLoading.value = true

  form.post(route('team-invitations.register', props.token), {
    onFinish: () => {
      isLoading.value = false
    },
  })
}
</script>

<template>
  <AuthLayout>
    <Head title="Accept Team Invitation"/>

    <div class="lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[450px]">
        <div class="flex flex-col space-y-2 text-center">
          <h1 class="text-2xl font-semibold tracking-tight">
            Join {{ team.name }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ inviter.name }} has invited you to join their team
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <FormField
              label="First name"
              v-model="form.first_name"
              :disabled="isLoading"
              required
              :error="form.errors.first_name"
            />

            <FormField
              label="Last name"
              v-model="form.last_name"
              :disabled="isLoading"
              required
              :error="form.errors.last_name"
            />
          </div>

          <FormField
            label="Email"
            v-model="email"
            type="email"
            disabled
          />

          <FormField
            label="Password"
            v-model="form.password"
            type="password"
            :disabled="isLoading"
            required
            :message="form.errors.password"
          />

          <FormField
            label="Confirm password"
            v-model="form.password_confirmation"
            type="password"
            :disabled="isLoading"
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

          <Button
            type="submit"
            class="w-full"
            :disabled="isLoading">
            <Loader2Icon
              v-if="isLoading"
              class="mr-2 h-4 w-4 animate-spin"
            />
            Join Team
          </Button>
        </form>
      </div>
    </div>
  </AuthLayout>
</template>
