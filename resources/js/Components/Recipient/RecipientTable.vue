<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from "@/Components/ui/table";
import MazCheckbox from "maz-ui/components/MazCheckbox";
import {Badge} from "@/Components/ui/badge";

const {recipients, selectedRecipients} = defineProps<{
  recipients: Array<{ id: number; name: string; email: string, gender: string, status: string }>;
  selectedRecipients: Set<number>;
}>();

const emit = defineEmits(["toggle-recipient", "select-all"]);

const toggleAll = (selectAll: boolean) => {
  emit("select-all", selectAll);
};

const matchedStatus = (status) => {
  const statusMap = {
    banned: { label: 'Blacklisted', class: 'bg-red-500 text-white' },
    active: { label: 'Active', class: 'bg-green-500 text-white' },
    inactive: { label: 'Dormant', class: 'bg-gray-500 text-white' },
    unsubscribed: { label: 'Opted out', class: 'bg-yellow-500 text-black' },
  };

  return statusMap[status] || 'Unknown status';
};
</script>

<template>
  <Table class="w-full">
    <TableHeader>
      <TableRow>
        <TableHead>
          <input
            type="checkbox"
            class="w-6 h-6 rounded-md"
            @change="toggleAll($event.target.checked)"
            :checked="recipients.every(({ id }) => selectedRecipients.has(id))"
          />
        </TableHead>
        <TableHead>Name</TableHead>
        <TableHead>Gender</TableHead>
        <TableHead>Status</TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>

      <TableRow v-for="recipient in recipients" :key="recipient.id">
        <TableCell>
          <MazCheckbox
            size="md"
            color="success"
            :checked="selectedRecipients.has(recipient.id)"
            @change="$emit('toggle-recipient', recipient.id)"
          />
        </TableCell>

        <TableCell class="grid">
          <strong>
            {{ recipient.name }}
          </strong>
          <span class="text-sm text-muted-foreground">
            {{ recipient.email }}
          </span>
        </TableCell>

        <TableCell class="capitalize">
          {{ recipient.gender }}
        </TableCell>

        <TableCell>
          <Badge class="rounded-sm px-2 py-1" :class="matchedStatus(recipient.status).class">
            {{ matchedStatus(recipient.status).label }}
          </Badge>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
