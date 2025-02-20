<script setup lang="ts">
import {useForm} from '@inertiajs/vue3'
import ConditionBuilder from "@/Pages/Segments/Partials/ConditionBuilder.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps<{
  segment: {
    name: string
    description?: string
    conditions: []
  }
  conditions: {},
  submitRoute: string
  submitMethod: string
}>()

const form = useForm({
  name: props.segment.name,
  description: props.segment.description,
  conditions: props.segment.conditions || []
})

const submit = () => {
  form[props.submitMethod](props.submitRoute, {
    preserveScroll: true,
    onSuccess: () => {
      if (props.submitMethod === 'post') {
        form.reset()
      }
    }
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="space-y-4">
      <div>
        <FormField
          label="Segment Name"
          v-model="form.name"
          class="mt-1"
          placeholder="e.g., Active Subscribers"
          :error="form.errors.name"
        />
      </div>

      <div>
        <FormField
          label="Description (Optional)"
          v-model="form.description"
          type="textarea"
          placeholder="Describe who this segment targets"
          rows="3"
          :error="form.errors.description"
        />
      </div>

      <div class="space-y-2">
        <Label>Conditions</Label>
        <ConditionBuilder
          v-model="form.conditions"
          :available-conditions="conditions"
        />

        <InputError :message="form.errors.conditions"/>
      </div>
    </div>

    <div class="flex items-center justify-end space-x-4">
      <Button
        type="button"
        variant="ghost"
        :href="route('segments.index')">
        Cancel
      </Button>

      <Button
        type="submit"
        :disabled="form.processing">
        {{ props.submitMethod === 'post' ? 'Create Segment' : 'Save Changes' }}
      </Button>
    </div>
  </form>
</template>
