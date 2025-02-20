<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
  status: {
    type: String,
    default: '',
  },
})

const form = useForm({})

const submit = () => {
  form.post(route('verification.send'))
}
</script>

<template>
  <Head title="Email Verification" />

  <AuthenticationCard>
    <template #logo>
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
    </template>

    <div class="mb-4 text-sm text-muted-foreground">
      Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
    </div>

    <div
      v-if="status === 'verification-link-sent'"
      class="mb-4 text-sm font-medium text-green-600" >
      A new verification link has been sent to the email address you provided during registration.
    </div>

    <form @submit.prevent="submit">
      <div class="mt-4 flex items-center justify-between">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Resend Verification Email
        </Button>

        <div>
          <Link
            :href="route('profile.show')"
            class="rounded-md text-sm text-muted-foreground underline hover:text-gray-900">
            Edit Profile
          </Link>

          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="ml-2 rounded-md text-sm text-muted-foreground underline hover:text-gray-900">
            Log Out
          </Link>
        </div>
      </div>
    </form>
  </AuthenticationCard>
</template>
