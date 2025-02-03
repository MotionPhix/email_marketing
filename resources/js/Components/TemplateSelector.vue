<script setup lang="ts">
import {PlusIcon, CheckCircleIcon, FrameIcon} from "lucide-vue-next";
import EmptyState from "@/Components/EmptyState.vue";
import {Link} from "@inertiajs/vue3"
import {computed} from "vue";

interface Template {
  value: number;
  label: string;
  description: string;
  preview: string;
}

interface Props {
  modelValue?: number;
  templates: Template[];
}

const props = defineProps<Props>();
const emits = defineEmits(["update:modelValue"]);

const hasTemplates = computed(() => props.templates?.length > 0);
</script>

<template>
  <div>
    <EmptyState
      v-if="!hasTemplates"
      title="No templates found"
      description="You haven't created any templates yet. Start by creating one."
      :icon="FrameIcon">
      <template #action>
        <Button
          size="sm"
          as-child
          class="inline-flex items-center">
          <Link as="button" class="gap-2" :href="route('templates.create')">
            <PlusIcon class="h-4 w-4"/>
            Create template
          </Link>
        </Button>
      </template>
    </EmptyState>

    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <label
        v-for="template in templates"
        :key="template.value"
        class="group relative flex cursor-pointer flex-col rounded-lg border-2 bg-white shadow-sm transition-all hover:shadow-md"
        :class="{
          'border-gray-200': modelValue !== template.value,
          'border-primary ring-2 ring-primary/30': modelValue === template.value,
        }">
        <input
          type="radio"
          class="sr-only"
          :value="template.value"
          :checked="modelValue === template.value"
          @change="$emit('update:modelValue', template.value)"
        />

        <!-- Preview Section -->
        <div class="relative h-72 mb-4 aspect-video w-full overflow-hidden rounded-md bg-muted">
          <img
            v-if="template.preview.startsWith('http')"
            :src="template.preview"
            :alt="template.label"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
          />

          <div
            v-else
            v-html="template.preview"
            class="h-full scroll-smooth scrollbar-none w-full overflow-auto bg-muted p-2 text-sm text-muted-foreground"
          />
        </div>

        <!-- Template Info -->
        <div class="flex flex-1 flex-col px-4">
          <div class="mb-2 flex items-center justify-between">
            <h3 class="text-base font-medium">{{ template.label }}</h3>
            <CheckCircleIcon
              v-show="modelValue === template.value"
              class="h-5 w-5 text-primary"
            />
          </div>
          <p class="text-sm text-muted-foreground">{{ template.description }}</p>
        </div>

        <!-- Selected Overlay -->
        <div
          v-if="modelValue === template.value"
          class="absolute inset-0 rounded-lg ring-2 ring-primary"
          aria-hidden="true"
        />
      </label>
    </div>
  </div>
</template>

<style scoped>
.group {
  transition: all 0.2s ease-in-out;
}

.group:hover {
  transform: translateY(-2px);
}

/* Ensure consistent aspect ratio for preview images */
.aspect-video {
  aspect-ratio: 16 / 9;
}
</style>
