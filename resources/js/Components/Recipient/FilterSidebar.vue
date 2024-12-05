<script setup lang="ts">
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
  SelectGroup,
  SelectLabel,
  SelectSeparator
} from "@/Components/ui/select";

const {modelValue} = defineProps<{ modelValue: Record<string, any> }>();
const emit = defineEmits(["update:modelValue"]);

const updateFilter = (key: string, value: any) => {
  emit("update:modelValue", {...modelValue, [key]: value});
};
</script>

<template>
  <div class="w-1/4 sticky top-28">
    <h4 class="font-bold mb-2">Filters</h4>
    <Select
      v-model="modelValue.status"
      @update:modelValue="updateFilter('status', $event)">
      <SelectTrigger class="w-full">
        <SelectValue placeholder="Filter recipients"/>
      </SelectTrigger>

      <SelectContent>
        <SelectGroup>
          <SelectLabel>Filter by gender</SelectLabel>

          <SelectSeparator />

          <SelectItem value="female">
            Male
          </SelectItem>
          <SelectItem value="male">
            Female
          </SelectItem>
          <SelectItem value="unspecified">
            Not known
          </SelectItem>
        </SelectGroup>

        <SelectGroup>
          <SelectLabel>Filter by status</SelectLabel>

          <SelectSeparator />

          <SelectItem value="active">
            Active
          </SelectItem>
          <SelectItem value="inactive">
            Not active
          </SelectItem>
          <SelectItem value="banned">
            Banned
          </SelectItem>
          <SelectItem value="unsubscribed">
            Opted out
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
  </div>
</template>
