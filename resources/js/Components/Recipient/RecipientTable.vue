<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import { Link } from '@inertiajs/vue3';
import { PenLineIcon, FileTextIcon } from 'lucide-vue-next';
import {IconChartLine, IconGenderFemale, IconGenderMale, IconMail} from "@tabler/icons-vue";
import {Checkbox} from "@/Components/ui/checkbox";

const props = defineProps<{
  recipients: Array<{
    id: number;
    uuid: string;
    name: string;
    email: string;
    gender: string;
    status: string;
  }>;
  selectedRecipients: Set<number>;
}>();

const emit = defineEmits(["toggle-recipient", "select-all"]);

const toggleAll = (selectAll: boolean) => {
  emit("select-all", selectAll);
};

const matchedStatus = (status: string) => {
  const statusMap = {
    banned: {
      label: 'Blacklisted',
      class: 'bg-rose-500/20 text-destructive dark:bg-rose-500/30'
    },
    active: {
      label: 'Active',
      class: 'bg-green-500/20 text-success dark:bg-green-500/30'
    },
    inactive: {
      label: 'Dormant',
      class: 'bg-muted text-muted-foreground dark:bg-muted/30'
    },
    unsubscribed: {
      label: 'Opted out',
      class: 'bg-orange-500/20 text-warning dark:bg-orange-500/30'
    },
  };

  return statusMap[status] || { label: 'Unknown', class: 'bg-muted' };
};
</script>

<template>
  <!-- Desktop Table View -->
  <div class="hidden sm:block">
    <Table>
      <TableHeader>
        <TableRow class="hover:bg-muted/50">
          <TableHead class="w-10">
            <label class="relative flex items-center">
              <input
                type="checkbox"
                @change="toggleAll($event.target.checked)"
                :checked="recipients.every(({ id }) => selectedRecipients.has(id))"
                class="peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
              />
              <span class="sr-only">Select all recipients</span>
            </label>
          </TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Gender</TableHead>
          <TableHead class="text-right w-28">Status</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow
          v-for="recipient in recipients"
          :key="recipient.id"
          class="group hover:bg-muted/50">
          <TableCell class="w-10">
            <label class="relative flex items-center">
              <input
                type="checkbox"
                :checked="selectedRecipients.has(recipient.id)"
                @change="$emit('toggle-recipient', recipient.id)"
                class="peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
              />
              <span class="sr-only">Select recipient</span>
            </label>
          </TableCell>

          <TableCell>
            <div class="flex flex-col">
              <span class="font-medium">{{ recipient.name }}</span>
              <span class="text-sm text-muted-foreground">{{ recipient.email }}</span>
            </div>
          </TableCell>

          <TableCell class="capitalize">
            {{ recipient.gender }}
          </TableCell>

          <TableCell class="text-right">
            <div class="flex items-center justify-end space-x-2">
              <Badge
                :class="[
                  matchedStatus(recipient.status).class,
                  'group-hover:hidden rounded-md px-2 py-1'
                ]">
                {{ matchedStatus(recipient.status).label }}
              </Badge>

              <div class="hidden group-hover:inline-flex items-center space-x-2">
                <GlobalLink
                  size="icon"
                  :href="route('recipients.edit', recipient.uuid)"
                  as="Button">
                  <PenLineIcon class="h-4 w-4" />
                </GlobalLink>

                <Button
                  as-child
                  size="icon"
                  variant="secondary"
                  :href="route('recipients.show', recipient.uuid)"
                  class="h-8 w-8">
                  <Link as="button">
                    <FileTextIcon class="h-4 w-4" />
                  </Link>
                </Button>
              </div>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <!-- Mobile Card View -->
  <div class="block sm:hidden space-y-4">
    <Card
      v-for="recipient in recipients"
      :key="recipient.id"
      class="group">
      <CardContent class="p-4">
        <div class="flex items-start justify-between">
          <div class="flex-1 space-y-3">
            <!-- Header with Checkbox -->
            <div class="flex-col flex gap-3">
              <label class="relative flex items-center gap-x-4">
                <Checkbox
                  :checked="selectedRecipients.has(recipient.id)"
                  @update:checked="$emit('toggle-recipient', recipient.id)"
                  class="peer h-5 w-5 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                />
                <span class="sr-only">Select recipient</span>

                <div class="text-xl font-thin">
                  <span class="font-medium">{{ recipient.name }}</span>
                </div>
              </label>

              <section class="mt-4 divide-y space-y-2">
                <div class="flex items-center gap-x-4 text-sm">
                  <IconMail class="h-4 w-4 text-muted-foreground" />
                  <span class="text-muted-foreground">{{ recipient.email }}</span>
                </div>

                <div class="flex items-center gap-x-4 text-sm pt-2">
                  <component class="h-4 w-4" :is="recipient.gender === 'male' ? IconGenderMale : IconGenderFemale" />
                  <span class="text-muted-foreground capitalize">{{ recipient.gender }}</span>
                </div>

                <div class="flex items-center gap-x-4 text-sm pt-2">
                  <IconChartLine class="h-4 w-4 text-muted-foreground" />

                  <Badge
                    :class="[
                      matchedStatus(recipient.status).class,
                      'rounded-md py-1 px-2 capitalize text-sm'
                    ]">

                    {{ matchedStatus(recipient.status).label }}
                  </Badge>
                </div>
              </section>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-2">
            <GlobalLink
              as="Button"
              size="icon"
              :href="route('recipients.edit', recipient.uuid)">
              <PenLineIcon />
            </GlobalLink>

            <Button
              as-child
              size="icon"
              variant="secondary"
              :href="route('recipients.show', recipient.uuid)"
              class="h-8 w-8">
              <Link as="button">
                <FileTextIcon class="h-4 w-4" />
              </Link>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.group:hover .group-hover\:hidden {
  display: none;
}

.group:hover .group-hover\:inline-flex {
  display: inline-flex;
}
</style>
