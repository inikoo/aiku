<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"

const props = defineProps<{
    done: number
    total: number
}>()

const percentage = computed(() => props.total ? Math.round((props.done / props.total) * 100) : 0)
const tone = computed(() => !props.total ? "bg-gray-200" : props.done === props.total ? "bg-green-500" : "bg-amber-500")
</script>

<template>
    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
        <div class="h-full rounded-full transition-all" :class="tone" :style="{ width: percentage + '%' }" />
    </div>
</template>
