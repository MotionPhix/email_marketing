<script setup lang="ts">
import { Transition } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  as?: string
  appear?: boolean
  show?: boolean
  enter?: string
  enterFrom?: string
  enterTo?: string
  leave?: string
  leaveFrom?: string
  leaveTo?: string
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  appear: false,
  show: true,
  enter: 'transition-all duration-300',
  enterFrom: 'opacity-0',
  enterTo: 'opacity-100',
  leave: 'transition-all duration-200',
  leaveFrom: 'opacity-100',
  leaveTo: 'opacity-0'
})
</script>

<template>
  <Transition
    :enter-active-class="enter"
    :enter-from-class="enterFrom"
    :enter-to-class="enterTo"
    :leave-active-class="leave"
    :leave-from-class="leaveFrom"
    :leave-to-class="leaveTo"
    :appear="appear">
    <component
      :is="as"
      v-if="show"
      :class="cn('transition-all')"
    >
      <slot />
    </component>
  </Transition>
</template>
