<script setup lang="ts">
import {Button} from "@/Components/ui/button";

const { selectedRecipients } = defineProps<{ selectedRecipients: number[] }>();

const emit = defineEmits(["perform-action"]);

const performAction = (action: string) => {
  emit("perform-action", { action, recipients: selectedRecipients });
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
