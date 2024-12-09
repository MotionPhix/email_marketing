<script setup lang="ts">
import {EmailEditor} from 'vue-email-editor'
import ApplicationMark from "@/Components/ApplicationMark.vue"
import {Button} from "@/Components/ui/button";
import {ArrowLeftIcon} from "@radix-icons/vue";
import {useCampaignStore} from "@/Stores/campaignStore";
import {Head, Link, router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import Editable from "@/Components/Editable.vue";
import { useToast } from 'maz-ui'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from "@/Components/ui/select";

const {fullDesign, campaign} = defineProps<{
  campaign?: object
  fullDesign: object
}>()

const form = useForm({
  name: fullDesign.name,
  design: fullDesign.design,
  content: fullDesign.content,
  campaign_id: campaign?.id ?? null,
  mode: fullDesign.mode ?? 'static',
})

const back = () => window.history.back()

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
  emailEditor.value.editor.loadDesign(JSON.parse(fullDesign.design));
}

const exportHtml = () => {
  emailEditor.value.editor.exportHtml(
    (data) => {
      form.content = data.html
      form.design = data.design

      //  wait for the design to be saved before proceeding
      setTimeout(() => {

        if (fullDesign.uuid) {

          form.put(route('templates.update', fullDesign.uuid), {

            onError: (errors) => {
              toast.error(errors.name ?? errors.design ?? errors.content)
            },

            onSuccess: () => {
              router.visit(route('templates.index'))
            }

          })

        } else {

          form.post(route('templates.store', campaign.uuid), {

            onError: (errors) => {
              toast.error(errors.name ?? errors.design ?? errors.content)
            }

          })

        }

      }, 100)
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

        <Button size="icon" variant="outline" @click="back">
          <ArrowLeftIcon/>
        </Button>

        <h2
          class="flex items-center gap-4 font-thin text-2xl text-gray-800 dark:text-gray-200 leading-tight">
          <ApplicationMark class="size-8"/>

          <Editable
            placeholder="Add template name"
            v-model="form.name"/>
        </h2>

        <span class="flex-1"/>

        <Select v-model="form.mode">
          <SelectTrigger class="w-32">
            <SelectValue placeholder="Set template type" />
          </SelectTrigger>

          <SelectContent>
            <SelectItem value="static">
              Static
            </SelectItem>

            <SelectItem value="dynamic">
              Dynamic
            </SelectItem>
          </SelectContent>
        </Select>

        <Button
          variant="ghost"
          :href="route(
            'campaigns.assign',
            { template: fullDesign?.uuid, campaign: campaignStore?.campaign?.uuid }
          )"
          v-if="fullDesign.uuid && campaign.uuid"
          as-child>
          <Link
            as="button">
            Assign
          </Link>
        </Button>

        <Button @click="exportHtml">
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
