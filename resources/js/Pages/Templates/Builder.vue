<script setup lang="ts">
import { EmailEditor } from 'vue-email-editor'
import ApplicationMark from "@/Components/ApplicationMark.vue"
import { useCampaignStore } from "@/Stores/campaign"
import { Head, Link, router, useForm } from "@inertiajs/vue3"
import { ref, onMounted } from "vue"
import Editable from "@/Components/Editable.vue"
import {useToast} from "vue-toast-notification";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from "@/Components/ui/select"
import BackButton from "@/Components/BackButton.vue"

interface Template {
  id?: number;
  uuid?: string;
  name?: string;
  mode?: 'static' | 'dynamic';
  design?: string;
  content?: string;
}

interface Campaign {
  id?: number;
  uuid?: string;
  template_id?: number;
  step?: number;
}

interface Props {
  fullDesign: Template;
  campaign: Campaign;
}
const props = defineProps<Props>();
const campaignStore = useCampaignStore();
const toast = useToast();
const emailEditor = ref();

// Store the campaign step before navigating to template builder
onMounted(() => {
  if (props.campaign?.step) {
    localStorage.setItem('campaignStep', props.campaign.step.toString());
  }
});

const form = useForm({
  name: props.fullDesign.name || '',
  design: props.fullDesign.design || '',
  content: props.fullDesign.content || '',
  campaign_id: props.campaign?.id ?? null,
  mode: props.fullDesign.mode ?? 'static',
});

const editorConfig = {
  minHeight: "90vh",
  locale: "en",
  projectId: 214012,
  tools: {
    image: { enabled: true }
  },
  options: {},
  appearance: {
    theme: 'dark',
    panels: {
      tools: { dock: 'right' }
    }
  }
};

const editorReady = () => {
  console.log('Editor ready');
};

const editorLoaded = () => {
  if (props.fullDesign.design) {
    emailEditor.value.editor.loadDesign(JSON.parse(props.fullDesign.design));
  }
};

const handleSaveSuccess = (page: { template: Template }) => {
  toast.success('Template saved successfully');

  // If we came from campaign creation, return to it
  if (props.campaign?.uuid) {
    router.put(
      route('campaigns.assign', {
        template: page.props.template.uuid,
        campaign: props.campaign.uuid
      }),
      {},
      {
        onSuccess: () => {
          // Then redirect back to campaign edit with template step
          router.visit(route('campaigns.edit', props.campaign.uuid), {
            data: { step: '2' }
          });
        },
        onError: () => {
          toast.error('Failed to assign template to campaign');
        }
      }
    );
  } else {
    router.visit(route('templates.index'));
  }
};

const exportHtml = () => {
  if (!form.name) {
    toast.error('Please provide a template name');
    return;
  }

  emailEditor.value.editor.exportHtml((data) => {
    form.content = data.html;
    form.design = data.design;

    const endpoint = props.fullDesign.uuid
      ? route('templates.update', props.fullDesign.uuid)
      : route('templates.store');

    const method = props.fullDesign.uuid ? 'put' : 'post';

    // Add slight delay to ensure design is properly serialized
    setTimeout(() => {
      form[method](endpoint, {
        onSuccess: handleSaveSuccess,
        onError: (errors: Record<string, string>) => {
          const errorMessage = errors.name || errors.design || errors.content || 'Failed to save template';
          toast.error(errorMessage);
        }
      });
    }, 100);
  });
};
</script>

<template>
  <Head>
    <title>{{ form.name ? `Edit ${form.name}` : 'New Template' }}</title>
  </Head>

  <div id="example" class="dark:bg-gray-700">
    <div class="container">
      <div id="bar" class="flex gap-4 items-center">
        <BackButton />

        <h2 class="flex items-center gap-4 font-thin text-2xl text-gray-800 dark:text-gray-200 leading-tight">
          <ApplicationMark class="size-8"/>
          <Editable
            placeholder="Add template name"
            v-model="form.name"
            :error="form.errors.name"
          />
        </h2>

        <span class="flex-1"/>

        <Select v-model="form.mode">
          <SelectTrigger class="w-32 dark:bg-gray-700 dark:text-gray-100">
            <SelectValue
              class="dark:text-gray-700 font-medium"
              placeholder="Set template type"
            />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="static">Static</SelectItem>
            <SelectItem value="dynamic">Dynamic</SelectItem>
          </SelectContent>
        </Select>

        <Button
          variant="ghost"
          :href="route('campaigns.assign', {
            template: props.fullDesign?.uuid,
            campaign: campaignStore?.campaign?.uuid
          })"
          v-if="props.fullDesign.uuid && props.campaign?.uuid"
          as-child
        >
          <Link as="button">Assign</Link>
        </Button>

        <Button
          @click="exportHtml"
          :disabled="form.processing"
          :loading="form.processing">
          Save
        </Button>
      </div>

      <EmailEditor
        ref="emailEditor"
        v-on:load="editorLoaded"
        v-on:ready="editorReady"
        :options="editorConfig.options"
        :appearance="editorConfig.appearance"
        :min-height="editorConfig.minHeight"
        :project-id="editorConfig.projectId"
        :locale="editorConfig.locale"
        :tools="editorConfig.tools"
      />
    </div>
  </div>
</template>

<style lang="scss">
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
}

#app, #example {
  height: 100%;
}

#example .container {
  display: flex;
  flex-direction: column;
  position: relative;
  height: 100%;
}

#bar {
  flex: 1;
  display: flex;
  max-height: 50px;
  @apply py-5;
}

a.blockbuilder-branding {
  display: none !important;
  @apply hidden;
}
</style>
