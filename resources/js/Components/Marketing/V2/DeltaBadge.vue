<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"

const props = withDefaults(defineProps<{
    current: number | null | undefined
    previous: number | null | undefined
    better?: "up" | "down" | "none"
    format?: (value: number) => string
}>(), {
    better: "up",
})

const change = computed(() => {
    if (props.current === null || props.current === undefined || props.previous === null || props.previous === undefined) {
        return null
    }
    if (props.previous === 0) {
        return props.current === 0 ? { label: "0%", direction: 0 } : { label: ctrans("new"), direction: Math.sign(props.current) }
    }
    const percent = Math.round((props.current - props.previous) / Math.abs(props.previous) * 100)
    return { label: `${Math.abs(percent)}%`, direction: Math.sign(percent) }
})

const tone = computed(() => {
    if (!change.value || change.value.direction === 0 || props.better === "none") {
        return "bg-gray-100 text-gray-600"
    }
    const isGood = props.better === "up" ? change.value.direction > 0 : change.value.direction < 0
    return isGood ? "bg-green-50 text-green-700" : "bg-red-50 text-red-700"
})

const tooltip = computed(() => props.previous === null || props.previous === undefined
    ? undefined
    : ctrans("Compared period: :value", { value: props.format ? props.format(props.previous) : String(props.previous) }))
</script>

<template>
    <span v-if="change" v-tooltip="tooltip" class="inline-flex items-center gap-0.5 whitespace-nowrap rounded-full px-1.5 py-0.5 text-xs font-medium tabular-nums" :class="tone">
        <span aria-hidden="true">{{ change.direction > 0 ? "▲" : change.direction < 0 ? "▼" : "" }}</span>
        <span>{{ change.label }}</span>
    </span>
</template>
