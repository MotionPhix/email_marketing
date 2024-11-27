<script setup>
import {EmailEditor} from 'vue-email-editor'
import ApplicationMark from "@/Components/ApplicationMark.vue"
import {Button} from "@/Components/ui/button";
import {ArrowLeftIcon} from "@radix-icons/vue";
import {useCampaignStore} from "@/Stores/campaignStore";
import {Head, Link, router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import Editable from "@/Components/Editable.vue";
import { useToast } from 'maz-ui'

const {fullDesign, campaign} = defineProps({
  campaign: Object,
  fullDesign: {
    type: Object,
    default: () => {
    }
  }
})

const form = useForm({
  name: fullDesign.name,
  design: fullDesign.design,
  content: fullDesign.content,
  campaign_id: campaign.id,
})

const emailEditor = ref()
const campaignStore = useCampaignStore()
const toast = useToast()

const minHeight = "90vh"
const locale = "en"
const projectId = 214012
const tools = {
  image: {
    enabled: true
  }
}
const options = {}
const appearance = {
  theme: 'dark',
  panels: {
    tools: {
      dock: 'right'
    }
  }
}

const editorReady = () => {
  console.log('editorReady');
}

const editorLoaded = () => {
  // Pass your template JSON here
  emailEditor.value.editor.loadDesign(fullDesign.design);
}

const saveDesign = () => {
  emailEditor.value.editor.saveDesign(
    (design) => {
      form.design = design

      if (fullDesign.uuid) {
        form.put(route('templates.update', fullDesign.uuid))
        return
      }

      form.post(route('templates.store'))
    }
  )
}

const exportHtml = () => {
  emailEditor.value.editor.exportHtml(
    (data) => {
      form.content = data.html

      emailEditor.value.editor.saveDesign(
        (design) => {
          form.design = design
        }
      )

      if (fullDesign.uuid) {

        form.put(route('templates.update', fullDesign.uuid), {

          onError: (errors) => {
            toast.error(errors.name ?? errors.design ?? errors.content)
          }

        })

      } else {

        form.post(route('templates.store', campaign.uuid), {

          onError: (errors) => {
            toast.error(errors.name ?? errors.design ?? errors.content)
          }

        })

      }
    }
  )
}
</script>

<template>

  <Head>
    <title>Manage templates</title>
  </Head>

  <div id="example">
    <div class="container">
      <div id="bar" class="flex gap-4 items-center">

        <Button size="icon" variant="outline" as-child>
          <Link
            :href="route('campaigns.create', { template_created: fullDesign.uuid })" as="button">
            <ArrowLeftIcon/>
          </Link>
        </Button>

        <h2
          class="flex items-center gap-4 font-thin text-2xl text-gray-800 dark:text-gray-200 leading-tight">
          <ApplicationMark class="size-8"/>

          <Editable
            placeholder="Add template name"
            v-model="form.name"/>
        </h2>

        <span class="flex-1"/>

        <Button
          variant="ghost"
          v-on:click="exportHtml" as-child>
          <Link
            as="button"
            v-if="fullDesign.uuid"
            :href="route('campaigns.assign', { template: fullDesign?.uuid, campaign: campaignStore?.campaign?.uuid })">
            Assign
          </Link>
        </Button>

        <Button v-on:click="exportHtml">
          Save
        </Button>

      </div>

      <EmailEditor
        ref="emailEditor"
        v-on:load="editorLoaded"
        v-on:ready="editorReady"
        :options="options"
        :appearance="appearance"
        :min-height="minHeight"
        :project-id="projectId"
        :locale="locale"
        :tools="tools"/>
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
  height: 10%;
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
