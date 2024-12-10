<script setup lang="ts">
import {Button} from "@/Components/ui/button";
import {SearchIcon} from "lucide-vue-next";
import TextInput from "@/Components/TextInput.vue";

const { modelValue } = defineProps<{ modelValue: string }>();
const emit = defineEmits(["update:modelValue", "search"]);

// Emit the updated value whenever the input changes
const updateValue = (value: string) => {
  emit("update:modelValue", value);
};

// Trigger the search action
const onSearch = () => {
  emit("search");
};
</script>

<template>
  <div class="relative flex items-center">
    <TextInput
      type="text"
      class="w-full"
      :value="modelValue"
      @input="updateValue($event.target.value)"
      placeholder="Search recipients..." />

    <Button
      size="icon"
      class="absolute top-[4%] right-1 z-10"
      @click="onSearch">
      <SearchIcon />
    </Button>
  </div>
</template>
