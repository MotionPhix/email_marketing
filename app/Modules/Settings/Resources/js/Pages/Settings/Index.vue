<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import type { SettingsPageProps } from '../../types'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingField from './Components/SettingField.vue'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps<SettingsPageProps>()
const { toast } = useToast()

const currentGroup = ref('general')

const form = useForm(
  Object.values(props.settings)
    .flat()
    .reduce((acc, setting) => ({
      ...acc,
      [setting.key]: setting.value
    }), {})
)

const updateSettings = () => {
  form.put(route('settings.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: 'Success',
        description: 'Settings updated successfully',
        variant: 'success'
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to update settings',
        variant: 'destructive'
      })
    }
  })
}
</script>

<template>
  <AppLayout title="Settings">
    <Head title="Settings" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-6">
          <!-- Sidebar -->
          <Card class="col-span-12 md:col-span-3">
            <CardHeader>
              <CardTitle>Settings</CardTitle>
              <CardDescription>Manage your application settings</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-1">
                <Button
                  v-for="(label, group) in groups"
                  :key="group"
                  variant="ghost"
                  :class="[
                    'w-full justify-start',
                    currentGroup === group ? 'bg-muted' : ''
                  ]"
                  @click="currentGroup = group"
                >
                  {{ label }}
                </Button>
              </div>
            </CardContent>
          </Card>

          <!-- Main Content -->
          <Card class="col-span-12 md:col-span-9">
            <CardHeader>
              <CardTitle>{{ groups[currentGroup] }}</CardTitle>
              <CardDescription>Configure your {{ groups[currentGroup].toLowerCase() }}</CardDescription>
            </CardHeader>

            <form @submit.prevent="updateSettings">
              <CardContent>
                <div class="space-y-6">
                  <SettingField
                    v-for="setting in settings[currentGroup]"
                    :key="setting.key"
                    :setting="setting"
                    v-model="form[setting.key]"
                  />
                </div>
              </CardContent>

              <CardFooter class="flex justify-end space-x-2">
                <Button
                  type="reset"
                  variant="outline"
                  :disabled="form.processing"
                >
                  Reset
                </Button>
                <Button
                  type="submit"
                  :disabled="form.processing || !form.isDirty"
                >
                  Save Changes
                  <Progress
                    v-if="form.processing"
                    class="ml-2 h-4 w-4"
                    :indeterminate="true"
                  />
                </Button>
              </CardFooter>
            </form>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
