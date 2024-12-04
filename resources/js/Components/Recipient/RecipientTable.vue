<script setup lang="ts">
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/Components/ui/table";
import MazCheckbox from "maz-ui/components/MazCheckbox";

const {recipients, selectedRecipients} = defineProps<{
  recipients: Array<{ id: number; name: string; email: string }>;
  selectedRecipients: Set<number>;
}>();

const emit = defineEmits(["toggle-recipient", "select-all"]);

const toggleAll = (selectAll: boolean) => {
  emit("select-all", selectAll);
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
        <TableHead>Email</TableHead>
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
        <TableCell>{{ recipient.name }}</TableCell>
        <TableCell>{{ recipient.email }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
