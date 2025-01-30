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
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";
import { Link } from '@inertiajs/vue3';
import { PenLineIcon, FileTextIcon, MailIcon, UserIcon } from 'lucide-vue-next';

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
      class: 'bg-destructive/20 text-destructive dark:bg-destructive/30'
    },
    active: {
      label: 'Active',
      class: 'bg-success/20 text-success dark:bg-success/30'
    },
    inactive: {
      label: 'Dormant',
      class: 'bg-muted text-muted-foreground dark:bg-muted/30'
    },
    unsubscribed: {
      label: 'Opted out',
      class: 'bg-warning/20 text-warning dark:bg-warning/30'
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
          class="group hover:bg-muted/50"
        >
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
                ]"
              >
                {{ matchedStatus(recipient.status).label }}
              </Badge>

              <div class="hidden group-hover:inline-flex items-center space-x-2">
                <Button
                  as-child
                  size="icon"
                  variant="default"
                  :href="route('recipients.edit', recipient.uuid)"
                  class="h-8 w-8"
                >
                  <Link as="button">
                    <PenLineIcon class="h-4 w-4" />
                  </Link>
                </Button>

                <Button
                  as-child
                  size="icon"
                  variant="secondary"
                  :href="route('recipients.show', recipient.uuid)"
                  class="h-8 w-8"
                >
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
      class="group"
    >
      <CardContent class="p-4">
        <div class="flex items-start justify-between">
          <div class="flex-1 space-y-3">
            <!-- Header with Checkbox -->
            <div class="flex items-center gap-3">
              <label class="relative flex items-center">
                <input
                  type="checkbox"
                  :checked="selectedRecipients.has(recipient.id)"
                  @change="$emit('toggle-recipient', recipient.id)"
                  class="peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                />
                <span class="sr-only">Select recipient</span>
              </label>
              <Badge
                :class="[
                  matchedStatus(recipient.status).class,
                  'rounded-md px-2 py-1'
                ]"
              >
                {{ matchedStatus(recipient.status).label }}
              </Badge>
            </div>

            <!-- Recipient Details -->
            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm">
                <UserIcon class="h-4 w-4 text-muted-foreground" />
                <span class="font-medium">{{ recipient.name }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <MailIcon class="h-4 w-4 text-muted-foreground" />
                <span class="text-muted-foreground">{{ recipient.email }}</span>
              </div>
            </div>

            <!-- Gender -->
            <div class="text-sm text-muted-foreground capitalize">
              Gender: {{ recipient.gender }}
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-2">
            <Button
              as-child
              size="icon"
              variant="default"
              :href="route('recipients.edit', recipient.uuid)"
              class="h-8 w-8"
            >
              <Link as="button">
                <PenLineIcon class="h-4 w-4" />
              </Link>
            </Button>

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
