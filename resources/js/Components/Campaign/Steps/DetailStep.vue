<script setup>
import {computed} from 'vue'
import {Field} from 'vee-validate'
import Combobox from '@/Components/Combobox.vue'

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  templates: {
    type: Array,
    required: true
  },
  processing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['next'])

const templateOptions = computed(() =>
  props.templates.map(template => ({
    value: template.id,
    label: template.name
  }))
)
</script>

<template>
  <div class="space-y-6">
    <!-- Campaign Details -->
    <Card>
      <CardHeader>
        <CardTitle>Campaign Details</CardTitle>
        <CardDescription>
          Basic information about your email campaign
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <!-- Name and Subject fields -->
            <FormField
              label="Campaign Name"
              v-model="form.name"
              :error="form.errors.name"
              placeholder="Spring Newsletter 2024"
            />

            <FormField
              label="Email Subject"
              v-model="form.subject"
              :error="form.errors.subject"
              placeholder="Your Spring Updates Are Here!"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <FormField
              label="From Name"
              v-model="form.from_name"
              :error="form.errors.from_name"
              placeholder="Your Company Name"
            />

            <FormField
              label="From Email"
              v-model="form.from_email"
              :error="form.errors.from_email"
              placeholder="newsletter@yourcompany.com"
              type="email"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <FormField
              v-model="form.reply_to"
              label="Reply-To Email (Optional)"
              placeholder="support@yourcompany.com"
              :error="form.errors.reply_to"
              type="email"
            />

            <div class="grid">
              <Label>Email Template</Label>
              <Combobox
                :model-value="form.template_id || null"
                :options="templateOptions"
                :error="form.errors.template_id"
                placeholder="Select a template"
                search-placeholder="Search templates..."
                empty-message="No templates found"
                @update:model-value="value => form.template_id = value"
              />
            </div>
          </div>

          <!-- Campaign Settings -->
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <Label
                class="flex items-center space-x-2">
                <Checkbox
                  :checked="form.settings.track_opens"
                  @update:checked="$emit('update:modelValue', $event)"
                />

                <span>Track Opens</span>
              </Label>

              <Label
                class="flex items-center space-x-2">
                <Checkbox
                  :checked="form.settings.track_clicks"
                  @update:checked="$emit('update:modelValue', $event)"
                />

                <span>Track Clicks</span>
              </Label>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-end">
      <Button
        type="button"
        variant="default"
        :disabled="processing"
        @click="$emit('next')">
        Continue to Design
      </Button>
    </div>
  </div>
</template>
