<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import {Avatar, AvatarFallback} from "@/Components/ui/avatar";
import {Link, router} from "@inertiajs/vue3";
import {
  FileTextIcon,
  XIcon,
  PenIcon,
  PenLineIcon,
  Trash2Icon,
  UserIcon,
  EllipsisIcon,
  PlusCircle,
  UserPlus,
} from 'lucide-vue-next'
import {useToast} from "maz-ui";
import PageTitle from "@/Components/PageTitle.vue";
import EmptyState from "@/Components/EmptyState.vue";

const props = defineProps<{
  audiences: {
    data: Array<any>;
    total: number;
  }
}>();

const toast = useToast()

const perform = (action: string, model: string, path: string, args?: object|string|number) => {
  switch (action.toLowerCase()) {
    case 'get':
      router.get(route(path, args));
      break;

    case 'post':
      router.post(route(path, args), {},{
        preserveScroll: true,
        onSuccess: () => {
          console.log('succeeded!')
        }
      });
      break;

    case 'put':
      router.put(route(path, args), {});
      break;

    case 'patch':
      router.patch(route(path, args), {});
      break;

    case 'delete':
      router.delete(route(path, args), {
        preserveScroll: true,

        onSuccess: () => {
          toast.success(`${model} was deleted successfully!`)
        },

        onError: (errors) => {
          toast.error(errors.flash[0])
        }
      });
      break;

    default:
      console.error(`Invalid HTTP action: ${action}`);
  }
}
</script>

<template>
  <AppLayout title="Audiences">
    <!-- Header -->
    <template #header>
      <PageTitle title="Audience List" />
    </template>

    <!-- Action Button -->
    <template #action>
      <Button
        size="sm"
        v-if="audiences.total"
        class="max-w-md" as-child
        :href="route('audiences.create')">
        <GlobalLink as="button">
          <PlusCircle /> New
        </GlobalLink>
      </Button>
    </template>

    <!-- Content -->
    <div class="py-12">
      <div v-if="!audiences.total" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <EmptyState
          title="No audiences yet"
          description="Get started by creating your first audience"
          :icon="UserIcon">
          <template #action>
            <Button as-child :href="route('audiences.create')">
              <GlobalLink as="button">
                Create Audience
              </GlobalLink>
            </Button>
          </template>
        </EmptyState>
      </div>

      <div v-else class="px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="audience in audiences.data"
          :key="audience.id"
          class="flex flex-col border border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
        >
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            {{ audience.name }}
          </h2>

          <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
            {{ audience.description || 'No description available.' }}
          </p>

          <ul class="mt-4 space-y-2 divide-y dark:divide-gray-600">
            <li
              v-for="recipient in audience.recipients"
              class="group flex items-center justify-between py-1"
              :key="recipient.id"
              v-if="audience.recipients.length">
              <div>
                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                  {{ recipient.name }}
                </h3>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ recipient.email }}
                </p>
              </div>

              <div class="gap-1 hidden group-hover:flex">
                <GlobalLink
                  as="Button"
                  size="icon"
                  variant="secondary" class="w-5 h-5"
                  :href="route('recipients.edit', recipient.uuid)"
                  :data="{ modal: true }">
                  <PenLineIcon/>
                </GlobalLink>

                <Button
                  variant="ghost" size="icon"
                  class="w-5 h-5 text-rose-500"
                  @click="perform(
                    'delete', 'Recipient',
                    'audiences.remove_recipient', {
                    audience: audience.uuid,
                    recipient: recipient.uuid
                  })">
                  <XIcon/>
                </Button>
              </div>
            </li>

            <li v-else class="text-muted-foreground flex flex-col items-center">
              <UserIcon class="h-16 w-16"/>

              <h3>No recipients added, yet!</h3>

              <p>
                <Button
                  as-child
                  :href="route('audiences.add_recipient', audience.uuid)">
                  <GlobalLink as="button">
                    Add one
                  </GlobalLink>
                </Button>
              </p>
            </li>
          </ul>

          <div class="flex-1"></div>

          <div
            class="mt-4 flex items-center"
            :class="audience.remaining_recipients_count ? 'justify-between' : 'justify-end'">
            <span
              v-if="audience.remaining_recipients_count"
              class="text-xs flex items-center gap-1 text-gray-500 dark:text-gray-400">
              <Avatar>
                <AvatarFallback>
                  <UserIcon/>
                </AvatarFallback>
              </Avatar>

              <span>
                +{{ audience.remaining_recipients_count }} more recipients
              </span>
            </span>

            <DropdownMenu :modal="false">
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="icon">
                  <EllipsisIcon/>
                </Button>
              </DropdownMenuTrigger>

              <DropdownMenuContent
                align="end" :side-offset="-36" side="top">
                <DropdownMenuLabel>Quick Actions</DropdownMenuLabel>

                <DropdownMenuSeparator/>

                <DropdownMenuGroup>
                  <GlobalLink
                    as="DropdownMenuItem"
                    :href="route('audiences.add_recipient', audience.uuid)">
                    <UserPlus />
                    <span>Add recipients</span>
                  </GlobalLink>

                  <DropdownMenuSeparator />

                  <GlobalLink
                    as="DropdownMenuItem"
                    :href="route('audiences.edit', audience.uuid)">
                    <PenIcon />
                    <span>Edit audience</span>
                  </GlobalLink>

                  <DropdownMenuItem as-child>
                    <Link
                      as="button" class="w-full"
                      :href="route('audiences.show', audience.uuid)">
                      <FileTextIcon />
                      <span>See audience</span>
                    </Link>
                  </DropdownMenuItem>
                </DropdownMenuGroup>

                <DropdownMenuSeparator/>

                <DropdownMenuItem
                  @click="perform(
                    'delete', 'Audience',
                    'audiences.destroy', audience.uuid
                  )">
                  <Trash2Icon />
                  <span>Delete audience</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
