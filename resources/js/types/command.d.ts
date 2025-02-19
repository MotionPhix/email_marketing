// First, let's define our types
export interface CommandProps {
  modelValue?: string | number | null
  defaultValue?: string | number | null
  open?: boolean
  defaultOpen?: boolean
  searchTerm?: string
  selectedValue?: string | number | null
  multiple?: boolean
  disabled?: boolean
  name?: string
  dir?: 'ltr' | 'rtl'
  filterFunction?: (value: string) => boolean
  displayValue?: (value: any) => string
  resetSearchTermOnBlur?: boolean
  asChild?: boolean
  as?: any
  class?: any
}

export interface CommandEmits {
  'update:modelValue': [value: string | number | null]
  'update:open': [value: boolean]
  'update:searchTerm': [value: string]
  'update:selectedValue': [value: string | number | null]
}

export interface CommandInputProps {
  modelValue?: string | number | null
  type?: string
  disabled?: boolean
  autoFocus?: boolean
  asChild?: boolean
  as?: any
  class?: any
}

export interface CommandInputEmits {
  'update:modelValue': [value: string | number | null]
}
