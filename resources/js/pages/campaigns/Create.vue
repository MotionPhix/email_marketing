<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { AlertCircle, Mail, Calendar, Users } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useDark, useStorage } from '@vueuse/core';
import InputError from '@/components/InputError.vue';
import { EmailEditor } from 'vue-email-editor';
import axios from 'axios';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
  templates: Array<{ id: number; uuid: string; name: string }>;
  lists: Array<{ id: number; uuid: string; name: string }>;
}>();

const page = usePage().props.auth;
const activeTab = useStorage('campaign', 'details');
const campaignCreator = ref(null)
const unlayerProjectId = parseInt(import.meta.env.VITE_UNLAYER_PROJECT_ID);
const isDarkMode = useDark();

const form = useForm({
  name: '',
  subject: '',
  preview_text: '',
  template_id: null as number | null,
  content: {
    design: {},
    html: ''
  },
  list_ids: [] as number[],
  from_name: page.current_organization.default_from_name,
  from_email: page.current_organization.default_from_email ?? '',
  reply_to: page.current_organization.default_reply_to ?? '',
  scheduled_at: null as string | null,
});

// unlayer editor configuration
const tools = {
  heading: {
    properties: {
      text: {
        value: 'Email Marketing'
      },
      fontFamily: {
        label: 'Geist Mono',
        value: 'geist mono,monospace'
      }
    }
  },
  image: {
    enabled: true
  },
  social: {
    enabled: true,
  },
}

const appearance = {
  theme: isDarkMode.value ? 'dark' : 'light',
  panels: {
    tools: {
      dock: 'left'
    }
  }
}

const breadcrumbs = [
  { title: page.current_team.name, href: route('dashboard') },
  { title: 'Campaigns', href: route('app.campaigns.index') },
  { title: 'Create Campaign', href: '#' },
];

const editorLoaded = async () => {
  await nextTick()

  if (form.template_id) {
    await loadTemplate(form.template_id);
  }

  // Set merge tags
  campaignCreator.value?.editor.setMergeTags({
    first_name: 'First Name',
    last_name: 'Last Name',
    email: 'Email Address',
  });
};

const saveDesign = async () => {
  try {
    campaignCreator.value?.editor.saveDesign(design => {
      form.content.design = design;
    });

    campaignCreator.value?.editor.exportHtml(data => {
      form.content.html = data.html;
    });
  } catch (error) {
    console.error('Error saving design:', error);
  }
};

const loadTemplate = async (templateId: number) => {
  try {
    const resp = await axios.get(route('api.templates.show', templateId));
    const template = resp.data.template;

    // Ensure we have valid design data
    const design = template.design
      ? (typeof template.design === 'string' ? JSON.parse(template.design) : template.design)
      : {};

    campaignCreator.value.editor.loadDesign(design);

    campaignCreator.value.editor.setBodyValues({
      preheaderText: template.preview_text || form.preview_text,
      fontFamily: {
        label: 'Geist Mono',
        value: "'Geist Mono',monospace"
      },
    });

    form.content = {
      design: design,
      html: template.content || ''
    };
  } catch (error) {
    console.error('Error loading template:', error);
  }
};

const recipientsCount = computed(() => {
  return form.list_ids.length;
});

const isValid = computed(() => {
  const requiredFields = ['name', 'subject', 'content', 'from_name', 'from_email'];
  return requiredFields.every(field => form[field]) && form.list_ids.length > 0;
});

watch(() => form.template_id, async (newId) => {
  if (newId && campaignCreator.value?.editor) {
    try {
      await loadTemplate(newId);
    } catch (error) {
      console.error('Error loading template:', error);
    }
  }
}, { immediate: true });

const submit = () => {
  form.post(route('app.campaigns.store'), {
    preserveScroll: true,
  });
};

onMounted(() => {
  if (campaignCreator.value?.editor) {
    campaignCreator.value.editor.addEventListener('design:updated', saveDesign);
    campaignCreator.value.editor.addEventListener('editor:ready', () => {
      if (form.template_id) {
        loadTemplate(form.template_id);
      }
    });
  }
});
</script>

<template>
  <Head title="Create Campaign" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-5xl p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <Heading
            title="Create Campaign"
            description="Design and schedule your email campaign"
          />
        </div>

        <div class="flex gap-x-2">
          <Button
            variant="outline"
            :href="route('app.campaigns.index')">
            Cancel
          </Button>

          <Button :disabled="!isValid" @click="submit">
            Create Campaign
          </Button>
        </div>
      </div>

      <Tabs v-model="activeTab" class="space-y-4">
        <TabsList class="grid w-full grid-cols-4">
          <TabsTrigger value="details" class="flex items-center gap-2">
            <Mail class="h-4 w-4" />
            Campaign Details
          </TabsTrigger>
          <TabsTrigger value="content" class="flex items-center gap-2">
            <AlertCircle class="h-4 w-4" />
            Content & Design
          </TabsTrigger>
          <TabsTrigger value="recipients" class="flex items-center gap-2">
            <Users class="h-4 w-4" />
            Recipients
          </TabsTrigger>
          <TabsTrigger value="schedule" class="flex items-center gap-2">
            <Calendar class="h-4 w-4" />
            Schedule
          </TabsTrigger>
        </TabsList>

        <TabsContent value="details" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Campaign Details</CardTitle>
              <CardDescription>Basic information about your campaign</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Campaign Name</Label>
                  <Input v-model="form.name" placeholder="Summer Newsletter 2024" />

                  <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <div class="space-y-2">
                  <Label>Email Subject</Label>
                  <Input v-model="form.subject" placeholder="Don't miss our summer deals!" />

                  <InputError :message="form.errors.subject" class="mt-1" />
                </div>
              </div>

              <div class="space-y-2">
                <Label>Preview Text</Label>
                <Input v-model="form.preview_text" placeholder="A brief preview that appears in email clients" />

                <InputError :message="form.errors.preview_text" class="mt-1" />
              </div>

              <Separator />

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>From Name</Label>
                  <Input v-model="form.from_name" :placeholder="page.current_team.name" />

                  <InputError :message="form.errors.from_name" class="mt-1" />
                </div>
                <div class="space-y-2">
                  <Label>From Email</Label>
                  <Input v-model="form.from_email" type="email" placeholder="newsletter@yourdomain.com" />

                  <InputError :message="form.errors.from_email" class="mt-1" />
                </div>
              </div>

              <div class="space-y-2">
                <Label>Reply-to Email</Label>
                <Input v-model="form.reply_to" type="email" placeholder="support@yourdomain.com" />

                <InputError :message="form.errors.reply_to" class="mt-1" />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="content">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center justify-between">
                <div>
                  Content & Design
                </div>

                <div class="flex items-center gap-4">
                  <Select v-model="form.template_id">
                    <SelectTrigger class="w-[240px]">
                      <SelectValue placeholder="Choose a template..." />
                    </SelectTrigger>

                    <SelectContent>
                      <SelectItem
                        v-for="template in templates"
                        :key="template.id"
                        :value="template.id">
                        {{ template.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardTitle>

              <CardDescription>
                Create your email content
              </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">

              <div class="border rounded-2xl">
                <div class="container">
                  <EmailEditor
                    ref="campaignCreator"
                    v-on:load="editorLoaded"
                    :appearance="appearance"
                    :project-id="unlayerProjectId"
                    :tools="tools"
                    minHeight="600px"
                    displayMode="email"
                    v-on:update="saveDesign"
                  />
                </div>
              </div>

              <InputError :message="form.errors.content" class="mt-2" />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="recipients">
          <Card>
            <CardHeader>
              <CardTitle>Select Recipients</CardTitle>
              <CardDescription>Choose who will receive this campaign</CardDescription>
            </CardHeader>

            <CardContent>
              <ScrollArea class="h-72">
                <div class="space-y-4">
                  <div v-for="list in lists" :key="list.id" class="flex items-center space-x-2">
                    <input
                      type="checkbox"
                      :id="`list-${list.id}`"
                      :value="list.id"
                      v-model="form.list_ids"
                      class="h-4 w-4 rounded border-gray-300"
                    />
                    <Label :for="`list-${list.id}`">{{ list.name }}</Label>
                  </div>

                  <InputError :message="form.errors.list_ids" class="mt-1" />
                </div>
              </ScrollArea>

              <div class="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                <Users class="h-4 w-4" />
                Selected Lists: {{ form.list_ids.length }}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="schedule">
          <Card>
            <CardHeader>
              <CardTitle>Schedule Campaign</CardTitle>
              <CardDescription>Choose when to send your campaign</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label>Schedule Date & Time</Label>
                <Input
                  v-model="form.scheduled_at"
                  type="datetime-local"
                  :min="new Date().toISOString().slice(0, 16)"
                />
                <p class="text-sm text-muted-foreground">
                  Leave empty to save as draft
                </p>

                <InputError :message="form.errors.scheduled_at" class="mt-1" />
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>

<style>
.unlayer-editor {
  border-radius: 0.375rem;
}

.unlayer-editor-counter {
  display: none;
}

 a.blockbuilder-branding {
   display: none !important;
 }

.blockbuilder {
  overflow-x: hidden !important;
}

.container {
  border-radius: 1rem;
  overflow: hidden;
}

.unlayer-editor {
  height: 700px;
}

#editor {
  width: 20px !important;
}
</style>
