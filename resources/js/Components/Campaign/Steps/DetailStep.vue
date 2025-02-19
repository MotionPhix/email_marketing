<script setup>
import {computed} from 'vue'
import {Field} from 'vee-validate'
import {FormControl, FormField, FormItem, FormLabel, FormMessage} from '@/Components/ui/form'
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
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Spring Newsletter 2024"/>
                  </Field>
                </FormControl>
                <FormMessage/>
              </FormItem>
            </FormField>

            <FormField name="subject">
              <FormItem>
                <FormLabel>Email Subject</FormLabel>
                <FormControl>
                  <Field name="subject" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Your Spring Updates Are Here!"/>
                  </Field>
                </FormControl>
                <FormMessage/>
              </FormItem>
            </FormField>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <FormField name="from_name">
              <FormItem>
                <FormLabel>From Name</FormLabel>
                <FormControl>
                  <Field name="from_name" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" placeholder="Your Company Name"/>
                  </Field>
                </FormControl>
                <FormMessage/>
              </FormItem>
            </FormField>

            <FormField name="from_email">
              <FormItem>
                <FormLabel>From Email</FormLabel>
                <FormControl>
                  <Field name="from_email" v-slot="{ field, errorMessage }">
                    <Input v-bind="field" :error="!!errorMessage" type="email"
                           placeholder="newsletter@yourcompany.com"/>
                  </Field>
                </FormControl>
                <FormMessage/>
              </FormItem>
            </FormField>
          </div>

          <FormField name="reply_to">
            <FormItem>
              <FormLabel>Reply-To Email (Optional)</FormLabel>
              <FormControl>
                <Field name="reply_to" v-slot="{ field, errorMessage }">
                  <Input v-bind="field" :error="!!errorMessage" type="email" placeholder="support@yourcompany.com"/>
                </Field>
              </FormControl>
              <FormMessage/>
            </FormItem>
          </FormField>

          <FormField name="template_id">
            <FormItem>
              <FormLabel>Email Template</FormLabel>
              <FormControl>
                <Field name="template_id" v-slot="{ field }">
                  <Combobox
                    :model-value="field.value || null"
                    :options="templateOptions"
                    :error="!!field.error"
                    placeholder="Select a template"
                    search-placeholder="Search templates..."
                    empty-message="No templates found"
                    @update:model-value="field.onChange"
                  />
                </Field>
              </FormControl>
              <FormMessage/>
            </FormItem>
          </FormField>

          <!-- Campaign Settings -->
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <Field name="settings.track_opens" v-slot="{ field }">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox
                      :checked="field.value"
                      @update:checked="$emit('update:modelValue', $event)"
                    />
                  </FormControl>
                  <FormLabel>Track Opens</FormLabel>
                </FormItem>
              </Field>

              <Field name="settings.track_clicks" v-slot="{ field }">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox
                      :checked="field.value"
                      @update:checked="$emit('update:modelValue', $event)"
                    />
                  </FormControl>
                  <FormLabel>Track Clicks</FormLabel>
                </FormItem>
              </Field>
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
