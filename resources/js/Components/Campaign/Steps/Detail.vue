<script setup>
import { computed } from 'vue'
import { Field } from 'vee-validate'
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/Components/ui/form'
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
            <FormField name="name">
              <FormItem>
                <FormLabel>Campaign Name</FormLabel>
                <FormControl>
                  <Field name="name" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Spring Newsletter 2024" />
                  </Field>
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <FormField name="subject">
              <FormItem>
                <FormLabel>Email Subject</FormLabel>
                <FormControl>
                  <Field name="subject" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Your Spring Updates Are Here!" />
                  </Field>
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <FormField name="from_name">
              <FormItem>
                <FormLabel>From Name</FormLabel>
                <FormControl>
                  <Field name="from_name" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Your Company Name" />
                  </Field>
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <FormField name="from_email">
              <FormItem>
                <FormLabel>From Email</FormLabel>
                <FormControl>
                  <Field name="from_email" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" type="email" placeholder="newsletter@yourcompany.com" />
                  </Field>
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>

          <FormField name="reply_to">
            <FormItem>
              <FormLabel>Reply-To Email (Optional)</FormLabel>
              <FormControl>
                <Field name="reply_to" v-slot="{ field, errorMessage }">
                  <Input v-bind="field" :error="!!errorMessage" type="email" placeholder="support@yourcompany.com" />
                </Field>
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField name="template_id">
            <FormItem>
              <FormLabel>Email Template</FormLabel>
              <FormControl>
                <Field name="template_id" v-slot="{ field, errorMessage }">
                  <Combobox
                    v-model="field.value"
                    :options="templateOptions"
                    :error="!!errorMessage"
                    placeholder="Select a template"
                    search-placeholder="Search templates..."
                    empty-message="No templates found"
                    @update:modelValue="setFieldValue('template_id', $event)"
                  />
                </Field>
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>

          <!-- Campaign Settings -->
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <!-- Tracking options -->
              <Field name="settings.track_opens" v-slot="{ field }">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox v-bind="field" />
                  </FormControl>

                  <FormLabel>Track Opens</FormLabel>
                </FormItem>
              </Field>

              <Field name="settings.track_clicks" v-slot="{ field }">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox v-bind="field" />
                  </FormControl>
                  <FormLabel>Track Clicks</FormLabel>
                </FormItem>
              </Field>
            </div>

            <!-- Schedule options -->
            <!-- Campaign Settings -->
            <Card>
              <CardHeader>
                <CardTitle>Campaign Schedule</CardTitle>
                <CardDescription>
                  Configure scheduling options
                </CardDescription>
              </CardHeader>

              <CardContent>
                <div class="space-y-4">
                  <Field name="settings.schedule_send" v-slot="{ field }">
                    <FormItem class="flex items-center space-x-2">
                      <FormControl>
                        <Checkbox
                          :checked="field.value"
                          @update:checked="setFieldValue('settings.schedule_send', $event)"
                        />
                      </FormControl>
                      <FormLabel>Schedule Send</FormLabel>
                    </FormItem>
                  </Field>

                  <div v-if="values.settings.schedule_send" class="grid grid-cols-2 gap-4">
                    <Field name="settings.scheduled_at" v-slot="{ field, errorMessage }">
                      <FormItem>
                        <FormLabel>Schedule Date & Time</FormLabel>
                        <FormControl>
                          <Input
                            type="datetime-local"
                            :value="field.value"
                            :error="!!errorMessage"
                            :min="new Date().toISOString().slice(0, 16)"
                            @update:value="setFieldValue('settings.scheduled_at', $event)"
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    </Field>

                    <Field name="settings.timezone" v-slot="{ field, errorMessage }">
                      <FormItem>
                        <FormLabel>Timezone</FormLabel>
                        <FormControl>
                          <Select
                            :value="field.value"
                            :error="!!errorMessage"
                            @update:value="setFieldValue('settings.timezone', $event)">
                            <option
                              v-for="tz in Intl.supportedValuesOf('timeZone')"
                              :key="tz"
                              :value="tz">
                              {{ tz }}
                            </option>
                          </Select>
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    </Field>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-end space-x-4">
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
