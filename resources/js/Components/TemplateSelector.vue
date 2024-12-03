<script setup lang="ts">
defineProps<{
  modelValue?: number,
  templates: Array<{
    value: number;
    label: string;
    description: string;
    preview: string;
  }>;
}>()

const emits = defineEmits(["update:modelValue"]);
</script>

<template>
  <div class="grid grid-cols-3 gap-6">
    <label
      v-for="template in templates"
      :key="template.value"
      class="w-full cursor-pointer rounded-md shadow-sm bg-white border-2 hover:shadow-lg transition-all"
      :class="{
        'border-gray-300': modelValue !== template.value,
        'border-green-500 ring-2 ring-green-300': modelValue === template.value,
      }"
    >
      <input
        type="radio"
        class="sr-only"
        :value="template.value"
        :checked="modelValue === template.value"
        @change="$emit('update:modelValue', template.value)"
      />

      <section class="flex flex-col gap-3">
        <!-- Preview Section -->
        <div class="h-64 bg-gray-100 rounded-md overflow-hidden flex items-center justify-center">
          <img
            v-if="template.preview.startsWith('http')"
            :src="template.preview"
            alt="Template preview"
            class="h-full w-full object-cover"
          />

          <div
            v-html="template.preview"
            class="w-full h-full p-2 overflow-auto bg-gray-50 text-sm text-gray-800 rounded" />
        </div>

        <!-- Template Info -->
        <div class="px-4 flex justify-between items-center">
          <span class="font-bold text-lg">{{ template.label }}</span>
          <svg
            v-show="modelValue === template.value"
            class="w-6 h-6 text-green-500"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <p class="text-sm text-gray-600">{{ template.description }}</p>
      </section>
    </label>
  </div>
</template>

<style scoped>
/* Optional: Add custom styles for consistent height and hover effects */
label {
  transition: all 0.3s ease-in-out;
}
label:hover {
  transform: scale(1.02);
}
</style>
