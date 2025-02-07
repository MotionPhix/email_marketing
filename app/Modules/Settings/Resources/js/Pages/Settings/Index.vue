<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  IconMail,
  IconWorld,
  IconCurrencyDollar,
  IconClock,
  IconSettings,
  IconBrandMailgun,
  IconBrandGmail
} from '@tabler/icons-vue'

interface EmailProvider {
  id: number
  name: string
  slug: string
  description: string
  required_fields: Record<string, {
    type: string
    label: string
    required: boolean
  }>
}

interface Props {
  settings: Record<string, Setting[]>
  emailProviders: EmailProvider[]
  userProviders: {
    provider_id: number
    credentials: Record<string, string>
    is_active: boolean
  }[]
  groups: Record<string, string>
}

const props = defineProps<Props>()
const currentGroup = ref('general')
const showProviderModal = ref(false)
const selectedProvider = ref<EmailProvider | null>(null)

const form = useForm({
  settings: Object.fromEntries(
    Object.values(props.settings)
      .flat()
      .map(setting => [setting.key, setting.value])
  ),
  provider: {
    id: null as number | null,
    credentials: {} as Record<string, string>
  }
})

const getGroupIcon = (group: string) => {
  switch (group) {
    case 'email':
      return IconMail
    case 'locale':
      return IconWorld
    default:
      return IconSettings
  }
}

const saveSettings = () => {
  form.put(route('settings.update'), {
    preserveScroll: true,
    onSuccess: () => {
      showProviderModal.value = false
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
                  <component
                    :is="getGroupIcon(group)"
                    class="mr-2 h-4 w-4"
                  />
                  {{ label }}
                </Button>
              </div>
            </CardContent>
          </Card>

          <!-- Main Content -->
          <div class="col-span-12 md:col-span-9 space-y-6">
            <!-- Email Provider Selection -->
            <Card v-if="currentGroup === 'email'">
              <CardHeader>
                <CardTitle>Email Providers</CardTitle>
                <CardDescription>
                  Configure your email delivery service
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <Card
                    v-for="provider in emailProviders"
                    :key="provider.id"
                    :class="[
                      'cursor-pointer transition-colors hover:bg-muted/50',
                      userProviders.some(up => up.provider_id === provider.id && up.is_active)
                        ? 'border-primary'
                        : ''
                    ]"
                    @click="selectedProvider = provider; showProviderModal = true"
                  >
                    <CardHeader>
                      <CardTitle class="flex items-center gap-2">
                        <component
                          :is="provider.slug === 'sendgrid' ? IconMail : IconBrandMailgun"
                          class="h-5 w-5"
                        />
                        {{ provider.name }}
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <p class="text-sm text-muted-foreground">
                        {{ provider.description }}
                      </p>
                    </CardContent>
                  </Card>
                </div>
              </CardContent>
            </Card>

            <!-- Regular Settings -->
            <Card>
              <CardHeader>
                <CardTitle>{{ groups[currentGroup] }}</CardTitle>
                <CardDescription>
                  Configure your {{ groups[currentGroup].toLowerCase() }}
                </CardDescription>
              </CardHeader>
              <form @submit.prevent="saveSettings">
                <CardContent>
                  <div class="space-y-6">
                    <div
                      v-for="setting in settings[currentGroup]"
                      :key="setting.key"
                      class="space-y-2"
                    >
                      <Label>{{ setting.label }}</Label>

                      <Select
                        v-if="setting.type === 'string' && setting.options"
                        v-model="form.settings[setting.key]"
                      >
                        <option
                          v-for="[value, label] in Object.entries(setting.options)"
                          :key="value"
                          :value="value"
                        >
                          {{ label }}
                        </option>
                      </Select>

                      <Input
                        v-else
                        :type="setting.type === 'email' ? 'email' : 'text'"
                        v-model="form.settings[setting.key]"
                      />

                      <p class="text-sm text-muted-foreground">
                        {{ setting.description }}
                      </p>
                    </div>
                  </div>
                </CardContent>
                <CardFooter>
                  <Button type="submit" :disabled="form.processing">
                    Save Changes
                  </Button>
                </CardFooter>
              </form>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <!-- Email Provider Modal -->
    <Dialog v-model:open="showProviderModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Configure {{ selectedProvider?.name }}</DialogTitle>
          <DialogDescription>
            Enter your credentials for {{ selectedProvider?.name }}
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div
            v-for="(field, key) in selectedProvider?.required_fields"
            :key="key"
            class="space-y-2"
          >
            <Label :for="key">{{ field.label }}</Label>
            <Input
              :id="key"
              :type="field.type"
              :required="field.required"
              v-model="form.provider.credentials[key]"
            />
          </div>

          <div class="space-y-2">
            <Label>
              <div class="flex items-center gap-2">
                <Switch
                  v-model="form.provider.is_active"
                  :disabled="form.processing"
                />
                Set as default provider
              </div>
            </Label>
            <p class="text-sm text-muted-foreground">
              This provider will be used for sending all your emails
            </p>
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="ghost"
            @click="showProviderModal = false"
            :disabled="form.processing"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            @click="saveSettings"
            :disabled="form.processing"
          >
            Save Provider
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
