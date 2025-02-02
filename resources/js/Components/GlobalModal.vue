<script setup lang="ts">
import {Modal} from '@inertiaui/modal-vue'
import {type Component, ref} from "vue";

const modalRef = ref()

withDefaults(
  defineProps<{
    manualClose?: boolean
    placement?: 'center' | 'top' | 'bottom'
    hasCloseButton?: boolean
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
    padding?: string
    modalTitle?: string
    description?: string
    icon?: Component
  }>(), {
    maxWidth: 'md',
    placement: 'center',
    manualClose: true
  }
)

function onClose() {
  modalRef.value.close()
}

defineExpose({
  onClose,
});
</script>

<template>
  <Modal
    ref="modalRef"
    :max-width="maxWidth"
    :position="placement"
    :paddingClasses="padding"
    :close-explicitly="manualClose"
    :close-button="hasCloseButton"
    panel-classes="border-none dark:bg-gray-800 rounded-lg bg-gray-100 dark:text-muted-foreground max-h-[80svh] overflow-y-auto scrollbar-none scroll-smooth">
    <slot name="header" v-if="$slots.header">

      <CardHeader class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-800">
        <CardTitle class="flex items-center gap-2">
          <component :is="icon"/>
          {{ modalTitle }}
        </CardTitle>

        <CardDescription>
          {{ description }}
        </CardDescription>
      </CardHeader>

    </slot>

    <slot/>
  </Modal>
</template>
