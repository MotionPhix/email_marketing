<script setup lang="ts">
import {FilterIcon} from "lucide-vue-next";
import {
  Command,
  CommandGroup,
  CommandItem,
  CommandList,
  CommandSeparator
} from '@/Components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {Button} from "@/Components/ui/button"

const {modelValue} = defineProps<{ modelValue: Record<string, any> }>();
const emit = defineEmits(["update:modelValue"]);

const updateFilter = (key: string, value: any) => {
  emit("update:modelValue", {...modelValue, [key]: value});
};
</script>

<template>
  <div class="hidden sm:block">

    <h4 class="font-bold gap-2 mb-2 items-center flex">
      <FilterIcon class="w-4 h-4 stroke-2"/>
      <span>Filters</span>
    </h4>

    <div class="sticky top-18">
      <Command
        multiple
        class="rounded-lg border shadow-sm w-[150px]"
        @update:modelValue="updateFilter('status', $event)"
        v-model="modelValue.status">

        <CommandList>
          <CommandGroup heading="Filter by gender">
            <CommandItem value="male">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M21 9C21 12.3137 18.3137 15 15 15C11.6863 15 9 12.3137 9 9C9 5.68629 11.6863 3 15 3C18.3137 3 21 5.68629 21 9Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 15V17C3 18.8856 3 19.8284 3.58579 20.4142C4.17157 21 5.11438 21 7 21H9M4 20L10.5 13.5"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span>Male</span>
            </CommandItem>

            <CommandItem value="female">
              <svg
                class="mr-2 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M12 14C15.3137 14 18 11.3137 18 8C18 4.68629 15.3137 2 12 2C8.68629 2 6 4.68629 6 8C6 11.3137 8.68629 14 12 14ZM12 14V22M9 19H15"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Female</span>
            </CommandItem>

            <CommandItem value="unspecified">
              <svg
                class="mr-2 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M7 14.9999C5.34315 14.9999 4 16.343 4 17.9999C4 19.6567 5.34315 20.9999 7 20.9999C8.65685 20.9999 10 19.6567 10 17.9999C10 16.343 8.65685 14.9999 7 14.9999Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M17 14.9999C15.3431 14.9999 14 16.343 14 17.9999C14 19.6567 15.3431 20.9999 17 20.9999C18.6569 20.9999 20 19.6567 20 17.9999C20 16.343 18.6569 14.9999 17 14.9999Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 17H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path
                  d="M22 12.9999C19.5434 11.7724 15.9734 10.9999 12 10.9999C8.02658 10.9999 4.45659 11.7724 2 12.9999"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M19 11.4999L17.9425 4.71233C17.7268 3.32807 16.2232 2.578 15.0093 3.24907L14.3943 3.58903C12.9019 4.414 11.0981 4.414 9.60574 3.58903L8.99074 3.24907C7.77676 2.578 6.27318 3.32808 6.05751 4.71233L5 11.4999"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Unknown</span>
            </CommandItem>
          </CommandGroup>

          <CommandSeparator/>

          <CommandGroup heading="Filter by status">
            <CommandItem value="active">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path d="M14 18C14 18 15 18 16 20C16 20 19.1765 15 22 14" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M13 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C8.75591 13.5891 12.7529 13.5096 16 15.2432"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z"
                  stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span>Active</span>
            </CommandItem>

            <CommandItem value="inactive">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path d="M5.25 5L19.25 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path
                  d="M22.25 12C22.25 6.47715 17.7728 2 12.25 2C6.72715 2 2.25 6.47715 2.25 12C2.25 17.5228 6.72715 22 12.25 22C17.7728 22 22.25 17.5228 22.25 12Z"
                  stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span>Dormant</span>
            </CommandItem>

            <CommandItem value="banned">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M19 12.5V17.9808C19 20.2867 19 21.4396 18.2272 21.8523C16.7305 22.6515 13.9232 19.9852 12.59 19.1824C11.8168 18.7168 11.4302 18.484 11 18.484C10.5698 18.484 10.1832 18.7168 9.41 19.1824C8.0768 19.9852 5.26947 22.6515 3.77285 21.8523C3 21.4396 3 20.2867 3 17.9808V9.70753C3 6.07417 3 4.25749 4.17157 3.12875C5.23467 2.10452 6.8857 2.00968 10 2.0009"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M13.2 3.2L18.8 8.8M20 6C20 3.79086 18.2091 2 16 2C13.7909 2 12 3.79086 12 6C12 8.20914 13.7909 10 16 10C18.2091 10 20 8.20914 20 6Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>Blacklisted</span>
            </CommandItem>

            <CommandItem value="unsubscribed">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M13 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C7.97679 14.053 10.8425 13.6575 13.5 14.2952"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z"
                  stroke="currentColor" stroke-width="1.5"/>
                <path
                  d="M16.05 16.05L20.95 20.95M22 18.5C22 16.567 20.433 15 18.5 15C16.567 15 15 16.567 15 18.5C15 20.433 16.567 22 18.5 22C20.433 22 22 20.433 22 18.5Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>Opted out</span>
            </CommandItem>
          </CommandGroup>

        </CommandList>

      </Command>
    </div>
  </div>

  <Popover>
    <PopoverTrigger as-child>
      <Button
        size="icon"
        class="sm:hidden">
        <FilterIcon />
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-[150px] p-0" align="end">
      <Command
        multiple
        @update:modelValue="updateFilter('status', $event)"
        v-model="modelValue.status">

        <CommandList>

          <CommandGroup heading="Filter by gender">
            <CommandItem value="male">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M21 9C21 12.3137 18.3137 15 15 15C11.6863 15 9 12.3137 9 9C9 5.68629 11.6863 3 15 3C18.3137 3 21 5.68629 21 9Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 15V17C3 18.8856 3 19.8284 3.58579 20.4142C4.17157 21 5.11438 21 7 21H9M4 20L10.5 13.5"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span>Male</span>
            </CommandItem>

            <CommandItem value="female">
              <svg
                class="mr-2 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M12 14C15.3137 14 18 11.3137 18 8C18 4.68629 15.3137 2 12 2C8.68629 2 6 4.68629 6 8C6 11.3137 8.68629 14 12 14ZM12 14V22M9 19H15"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Female</span>
            </CommandItem>

            <CommandItem value="unspecified">
              <svg
                class="mr-2 h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M7 14.9999C5.34315 14.9999 4 16.343 4 17.9999C4 19.6567 5.34315 20.9999 7 20.9999C8.65685 20.9999 10 19.6567 10 17.9999C10 16.343 8.65685 14.9999 7 14.9999Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M17 14.9999C15.3431 14.9999 14 16.343 14 17.9999C14 19.6567 15.3431 20.9999 17 20.9999C18.6569 20.9999 20 19.6567 20 17.9999C20 16.343 18.6569 14.9999 17 14.9999Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 17H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path
                  d="M22 12.9999C19.5434 11.7724 15.9734 10.9999 12 10.9999C8.02658 10.9999 4.45659 11.7724 2 12.9999"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M19 11.4999L17.9425 4.71233C17.7268 3.32807 16.2232 2.578 15.0093 3.24907L14.3943 3.58903C12.9019 4.414 11.0981 4.414 9.60574 3.58903L8.99074 3.24907C7.77676 2.578 6.27318 3.32808 6.05751 4.71233L5 11.4999"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Unknown</span>
            </CommandItem>
          </CommandGroup>

          <CommandSeparator/>

          <CommandGroup heading="Filter by status">

            <CommandItem value="active">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path d="M14 18C14 18 15 18 16 20C16 20 19.1765 15 22 14" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M13 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C8.75591 13.5891 12.7529 13.5096 16 15.2432"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z"
                  stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span>Active</span>
            </CommandItem>

            <CommandItem value="inactive">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path d="M5.25 5L19.25 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path
                  d="M22.25 12C22.25 6.47715 17.7728 2 12.25 2C6.72715 2 2.25 6.47715 2.25 12C2.25 17.5228 6.72715 22 12.25 22C17.7728 22 22.25 17.5228 22.25 12Z"
                  stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span>Dormant</span>
            </CommandItem>

            <CommandItem value="banned">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M19 12.5V17.9808C19 20.2867 19 21.4396 18.2272 21.8523C16.7305 22.6515 13.9232 19.9852 12.59 19.1824C11.8168 18.7168 11.4302 18.484 11 18.484C10.5698 18.484 10.1832 18.7168 9.41 19.1824C8.0768 19.9852 5.26947 22.6515 3.77285 21.8523C3 21.4396 3 20.2867 3 17.9808V9.70753C3 6.07417 3 4.25749 4.17157 3.12875C5.23467 2.10452 6.8857 2.00968 10 2.0009"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M13.2 3.2L18.8 8.8M20 6C20 3.79086 18.2091 2 16 2C13.7909 2 12 3.79086 12 6C12 8.20914 13.7909 10 16 10C18.2091 10 20 8.20914 20 6Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>Blacklisted</span>
            </CommandItem>

            <CommandItem value="unsubscribed">
              <svg
                class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24" fill="none">
                <path
                  d="M13 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C7.97679 14.053 10.8425 13.6575 13.5 14.2952"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z"
                  stroke="currentColor" stroke-width="1.5"/>
                <path
                  d="M16.05 16.05L20.95 20.95M22 18.5C22 16.567 20.433 15 18.5 15C16.567 15 15 16.567 15 18.5C15 20.433 16.567 22 18.5 22C20.433 22 22 20.433 22 18.5Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>Opted out</span>
            </CommandItem>
          </CommandGroup>

        </CommandList>

      </Command>

    </PopoverContent>
  </Popover>
</template>
