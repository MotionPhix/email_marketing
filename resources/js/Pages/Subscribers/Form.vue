<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToast } from '@/hooks/useToast'

const { toast } = useToast()

const props = defineProps<{
  subscriber?: {
    id: number
    email: string
    first_name: string | null
    last_name: string | null
    company: string | null
    metadata: Record<string, any> | null
  } | null
}>()

const form = useForm({
  email: props.subscriber?.email ?? '',
  first_name: props.subscriber?.first_name ?? '',
  last_name: props.subscriber?.last_name ?? '',
  company: props.subscriber?.company ?? '',
  metadata: props.subscriber?.metadata ?? {},
})

const submit = () => {
  if (props.subscriber) {
    form.put(route('subscribers.update', props.subscriber.id), {
      onSuccess: () => {
        toast({
          title: "Success",
          description: "Subscriber updated successfully",
        })
      },
    })
  } else {
    form.post(route('subscribers.store'), {
      onSuccess: () => {
        toast({
          title: "Success",
          description: "Subscriber created successfully",
        })
      },
    })
  }
}
</script>

<template>
  <AppLayout :title="subscriber ? 'Edit Subscriber' : 'Add Subscriber'">
    <Head :title="subscriber ? 'Edit Subscriber' : 'Add Subscriber'" />

    <div class="container max-w-2xl space-y-6 p-4 md:p-8">
      <div>
        <h2 class="text-3xl font-bold tracking-tight">
          {{ subscriber ? 'Edit Subscriber' : 'Add Subscriber' }}
        </h2>
        <p class="text-muted-foreground">
          {{ subscriber ? 'Update subscriber information' : 'Add a new subscriber to your list' }}
        </p>
      </div>

      <Card>
        <CardContent class="p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-4">
              <FormField>
                <Label for="email">Email</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  :disabled="form.processing"
                  required
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                  {{ form.errors.email }}
                </p>
              </FormField>

              <div class="grid gap-4 sm:grid-cols-2">
                <FormField>
                  <Label for="first_name">First Name</Label>
                  <Input
                    id="first_name"
                    v-model="form.first_name"
                    type="text"
                    :disabled="form.processing"
                  />
                  <p v-if="form.errors.first_name" class="text-sm text-destructive">
                    {{ form.errors.first_name }}
                  </p>
                </FormField>

                <FormField>
                  <Label for="last_name">Last Name</Label>
                  <Input
                    id="last_name"
                    v-model="form.last_name"
                    type="text"
                    :disabled="form.processing"
                  />
                  <p v-if="form.errors.last_name" class="text-sm text-destructive">
                    {{ form.errors.last_name }}
                  </p>
                </FormField>
              </div>

              <FormField>
                <Label for="company">Company</Label>
                <Input
                  id="company"
                  v-model="form.company"
                  type="text"
                  :disabled="form.processing"
                />
                <p v-if="form.errors.company" class="text-sm text-destructive">
                  {{ form.errors.company }}
                </p>
              </FormField>
            </div>

            <div class="flex justify-end gap-4">
              <Button
                variant="outline"
                type="button"
                @click="router.get(route('subscribers.index'))"
                :disabled="form.processing"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ subscriber ? 'Update' : 'Create' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>

    <!-- Toast Provider at the App Root -->
    <ToastProvider />
  </AppLayout>
</template>
