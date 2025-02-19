<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/Components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import {computed, ref, watch} from 'vue'

interface Option {
  value: string | number
  label: string
}

const props = defineProps<{
  modelValue: string | number | null
  options: Option[]
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const open = ref(false)
const search = ref('')

const filteredOptions = computed(() => {
  if (!search.value) return props.options

  const searchLower = search.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchLower)
  )
})

const selectedLabel = computed(() => {
  if (!props.modelValue) return props.placeholder || 'Select option...'
  return props.options.find(opt => opt.value === props.modelValue)?.label || props.placeholder
})

const handleSelect = (value: string | number) => {
  emit('update:modelValue', value)
  open.value = false
  search.value = ''
}

// Reset search when popover closes
watch(open, (newValue) => {
  if (!newValue) {
    search.value = ''
  }
})
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="open"
        :disabled="disabled"
        class="w-full justify-between">
        {{ selectedLabel }}
        <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-full p-0">
      <Command>
        <CommandInput
          v-model="search"
          class="h-9"
          :placeholder="searchPlaceholder || 'Search...'"
        />
        <CommandEmpty>{{ emptyMessage || 'No option found.' }}</CommandEmpty>
        <CommandList>
          <CommandGroup>
            <CommandItem
              v-for="option in filteredOptions"
              :key="option.value"
              :value="option.value"
              @select="handleSelect(option.value)">
              <span>{{ option.label }}</span>
              <CheckIcon
                :class="cn(
                  'ml-auto h-4 w-4',
                  modelValue === option.value ? 'opacity-100' : 'opacity-0'
                )"
              />
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
