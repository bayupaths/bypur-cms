<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ProgressIndicator, ProgressRoot } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
  modelValue?: number
}>()
</script>

<template>
  <ProgressRoot
    v-bind="props"
    :model-value="modelValue"
    :class="cn('bg-secondary relative h-2 w-full overflow-hidden rounded-full', props.class)"
  >
    <ProgressIndicator
      class="bg-primary h-full w-full flex-1 transition-all"
      :style="`transform: translateX(-${100 - (modelValue ?? 0)}%)`"
    />
  </ProgressRoot>
</template>
