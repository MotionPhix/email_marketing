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
import {FilterIcon} from "lucide-vue-next";

const {modelValue} = defineProps<{ modelValue: Record<string, any> }>();
const emit = defineEmits(["update:modelValue"]);

const updateFilter = (key: string, value: any) => {
  emit("update:modelValue", {...modelValue, [key]: value});
};
</script>

<template>
  <div class="w-1/4 sticky top-28">
    <h4 class="font-bold gap-2 mb-2 flex items-center">
      <FilterIcon class="w-4 h-4 stroke-2" />
      <span>Filters</span>
    </h4>

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

          <SelectItem value="male">
            Male
          </SelectItem>
          <SelectItem value="female">
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
