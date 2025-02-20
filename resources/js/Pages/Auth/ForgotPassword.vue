<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps<{
  status?: string
}>()

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <Head title="Forgot Password" />

  <AuthenticationCard>
    <template #logo>
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
    </template>

    <div class="mb-4 text-sm text-muted-foreground">
      Type in your email address and we will email you a password reset link.
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <FormField
          label="Email"
          v-model="form.email"
          type="email"
          required
          autofocus
          placeholder="Enter your email address"
          :error="form.errors.email"
        />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Email Password Reset Link
        </Button>
      </div>
    </form>
  </AuthenticationCard>
</template>
