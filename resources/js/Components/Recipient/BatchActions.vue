<script setup lang="ts">
import {Button} from "@/Components/ui/button";

const { selectedRecipients } = defineProps<{ selectedRecipients: number[] }>();

const emit = defineEmits(["perform-action"]);

const performAction = (action: string) => {
  emit("perform-action", { action, recipients: selectedRecipients });
};

const handleAction = (payload: { action: string; recipients: number[] }) => {
  const { action, recipients } = payload;
  switch (action) {
    case "delete":
      // Handle deletion logic
      console.log("Deleting:", recipients);
      break;
    case "export_pdf":
      // Handle export to PDF
      console.log("Exporting to PDF:", recipients);
      break;
    case "export_excel":
      // Handle export to Excel
      console.log("Exporting to Excel:", recipients);
      break;
    case "export_csv":
      // Handle export to CSV
      console.log("Exporting to CSV:", recipients);
      break;
    default:
      console.warn("Unknown action:", action);
  }
};
</script>

<template>
  <div class="flex items-center gap-4">
    <Button
      class="bg-red-500"
      @click="performAction('delete')"
      :disabled="!selectedRecipients.length"
    >
      Delete Selected
    </Button>
    <span v-if="selectedRecipients.length">{{ selectedRecipients.length }} selected</span>
  </div>
</template>
