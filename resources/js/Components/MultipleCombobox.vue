<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/Components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import { cn } from '@/lib/utils'
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-vue-next'

interface Option {
  value: string | number
  label: string
}

const props = withDefaults(defineProps<{
  modelValue: (string | number)[]
  options: Option[]
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  error?: boolean
  triggerText?: (selected: Option[]) => string
}>(), {
  modelValue: () => [], // Provide default empty array
  options: () => [],    // Provide default empty array
  placeholder: 'Select options...',
  searchPlaceholder: 'Search...',
  emptyMessage: 'No options found.',
  error: false
})

const emit = defineEmits<{
  'update:modelValue': [value: (string | number)[]]
}>()

const open = ref(false)
const search = ref('')

const selectedOptions = computed(() =>
  props.options.filter(option => props.modelValue.includes(option.value))
)

const filteredOptions = computed(() => {
  if (!search.value) return props.options

  const searchLower = search.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchLower)
  )
})

const handleSelect = (value: string | number) => {
  const newValue = props.modelValue.includes(value)
    ? props.modelValue.filter(v => v !== value)
    : [...props.modelValue, value]

  emit('update:modelValue', newValue)
  search.value = ''
}

const defaultTriggerText = (selected: Option[]) =>
  selected.length
    ? `${selected.length} item${selected.length === 1 ? '' : 's'} selected`
    : props.placeholder || 'Select options...'
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="open"
        :class="cn(
          'w-full justify-between h-11',
          error && 'border-destructive',
          !selectedOptions.length && 'text-muted-foreground'
        )">
        {{ (triggerText || defaultTriggerText)(selectedOptions) }}
        <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-full p-0">
      <Command>
        <CommandInput
          v-model="search"
          :placeholder="searchPlaceholder"
        />
        <CommandEmpty>{{ emptyMessage || 'No options found.' }}</CommandEmpty>
        <CommandGroup>
          <CommandItem
            v-for="option in filteredOptions"
            :key="option.value"
            :value="option.value"
            @select="handleSelect(option.value)">
            <CheckIcon
              :class="cn(
                'mr-2 h-4 w-4',
                modelValue.includes(option.value) ? 'opacity-100' : 'opacity-0'
              )"
            />
            {{ option.label }}
          </CommandItem>
        </CommandGroup>
      </Command>
    </PopoverContent>
  </Popover>
</template>
