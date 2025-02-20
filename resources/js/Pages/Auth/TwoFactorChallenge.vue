<script setup>
import {ref} from 'vue'
import {Head, useForm} from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import InputError from '@/Components/InputError.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const recovery = ref(false)

const form = useForm({
  code: '',
  recovery_code: '',
})

const toggleRecovery = () => {
  recovery.value ^= true

  if (recovery.value) {
    form.code = ''
  } else {
    form.recovery_code = ''
  }

  setTimeout(() => {
    if (recovery.value) {
      document.getElementById('recovery_code').focus()
    } else {
      document.getElementById('code').focus()
    }
  }, 100)
}

const submit = () => {
  form.post(route('two-factor.login'))
}
</script>

<template>
  <Head title="Two-factor Confirmation"/>

  <AuthenticationCard>
    <template #logo>
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500"/>
    </template>

    <div class="mb-4 text-sm text-muted-foreground">
      <template v-if="!recovery">
        Please confirm access to your account by entering the authentication code provided by your authenticator
        application.
      </template>

      <template v-else>
        Please confirm access to your account by entering one of your emergency recovery codes.
      </template>
    </div>

    <form @submit.prevent="submit">
      <div v-if="!recovery">
        <FormField
          label="Code"
          v-model="form.code"
          type="number"
          autofocus
          error="form.errors.code"
        />
      </div>

      <div v-else>
        <FormField
          label="Recovery Code"
          v-model="form.recovery_code"
          autocomplete="one-time-code"
          :error="form.errors.recovery_code"
        />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Button
          type="button"
          class="text-sm underline hover:opacity-75"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="toggleRecovery" >
          <template v-if="!recovery">
            Use a recovery code
          </template>

          <template v-else>
            Use an authentication code
          </template>
        </Button>

        <Button
          class="ml-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing">
          Log in
        </Button>
      </div>
    </form>
  </AuthenticationCard>
</template>
