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

const props = defineProps<{
  modelValue: string | number | null
  options: Option[]
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  error?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const open = ref(false)
const search = ref('')

const selectedOption = computed(() =>
  props.options.find(option => option.value === props.modelValue)
)

const filteredOptions = computed(() => {
  if (!search.value) return props.options

  const searchLower = search.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchLower)
  )
})

const handleSelect = (value: string | number) => {
  emit('update:modelValue', value)
  open.value = false
  search.value = ''
}
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="open"
        :class="cn(
          'w-full justify-between',
          error && 'border-destructive',
          !selectedOption && 'text-muted-foreground'
        )"
      >
        {{ selectedOption?.label || placeholder || 'Select option...' }}
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
                modelValue === option.value ? 'opacity-100' : 'opacity-0'
              )"
            />
            {{ option.label }}
          </CommandItem>
        </CommandGroup>
      </Command>
    </PopoverContent>
  </Popover>
</template>
