<script setup lang="ts">
import {ref} from "vue";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger
} from "@/Components/ui/dropdown-menu";
import {DotsHorizontalIcon, ExitIcon} from "@radix-icons/vue";

defineProps<{
  template_name: string
  content: string
}>()

const width = ref<'sm'|'md'|'lg'|'2xl'>('sm')
const previewModalRef = ref()

const close = () => previewModalRef.value.onClose()
</script>

<template>

  <GlobalModal
    :max-width="width"
    ref="previewModalRef">

    <div class="fixed bg-primary-foreground dark:bg-accent w-full top-0 inset-0 p-6 flex justify-between">
      <h3 class="text-2xl">
        {{ template_name }}
      </h3>

      <div>
        <DropdownMenu>
          <DropdownMenuTrigger>
            <Button size="icon">
              <DotsHorizontalIcon/>
            </Button>
          </DropdownMenuTrigger>

          <DropdownMenuContent align="end">
            <DropdownMenuLabel>
              Quick Actions
            </DropdownMenuLabel>

            <DropdownMenuSeparator/>

            <DropdownMenuItem @click="width = 'sm'">
              Mobile Preview
            </DropdownMenuItem>

            <DropdownMenuItem @click="width = 'md'">
              Tablet Preview
            </DropdownMenuItem>

            <DropdownMenuItem @click="width = 'lg'">
              Laptop Preview
            </DropdownMenuItem>

            <DropdownMenuItem @click="width = '2xl'">
              Desktop Preview
            </DropdownMenuItem>

            <DropdownMenuSeparator/>

            <DropdownMenuItem @click="close">
              <ExitIcon/>
              Close
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>

    <section v-html="content"/>

  </GlobalModal>

</template>
