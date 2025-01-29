<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import FormField from "@/Components/Forms/FormField.vue";
import {CardFooter} from "@/Components/ui/card/index.js";

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Register"/>

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo/>
    </template>

    <form @submit.prevent="submit">
      <Card class="mx-auto max-w-md">
        <CardHeader>
          <CardTitle class="text-xl">
            Sign Up
          </CardTitle>

          <CardDescription>
            Enter your information to create an account
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="grid gap-2">
            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <FormField
                  v-model="form.first_name"
                  label="First name"
                  placeholder="Max"
                  :error="form.errors.first_name"
                  required
                />
              </div>

              <div class="grid gap-2">
                <FormField
                  v-model="form.last_name"
                  label="Last name"
                  :error="form.errors.last_name"
                  placeholder="Robinson"
                />
              </div>
            </div>

            <div class="grid gap-2">
              <FormField
                label="Email"
                type="email"
                v-model="form.email"
                placeholder="m@example.com"
                :error="form.errors.email"
                required
              />
            </div>

            <div class="grid gap-2">
              <FormField
                label="Password"
                type="password"
                placeholder="Enter a strong password"
                :error="form.errors.password"
                v-model="form.password"
                required
              />

              <FormField
                label="Confirm password"
                type="password"
                v-model="form.password_confirmation"
                placeholder="Confirm your password"
                :error="form.errors.password_confirmation"
                required
              />

              <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
                <InputLabel for="terms">
                  <div class="flex items-center">
                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required/>

                    <div class="ms-2">
                      I agree to the <a target="_blank" :href="route('terms.show')"
                                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">Terms
                      of Service</a> and <a target="_blank" :href="route('policy.show')"
                                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">Privacy
                      Policy</a>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.terms"/>
                </InputLabel>
              </div>
            </div>

            <Button
              type="submit"
              class="w-full"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing">
              Create an account
            </Button>
          </div>

          <div class="mt-4 text-center text-sm">
            Already have an account?
            <Link :href="route('login')" as="button" class="underline">
              Sign in
            </Link>
          </div>
        </CardContent>
      </Card>
    </form>
  </AuthenticationCard>
</template>
