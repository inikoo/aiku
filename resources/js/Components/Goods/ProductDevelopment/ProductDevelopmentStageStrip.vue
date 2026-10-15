<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"

interface Segment {
    key: string
    number: string
    label: string
    state: string
    is_late: boolean
    due_at: string | null
    progress: { done: number, total: number, missing: number } | null
}

defineProps<{
    segments: Segment[]
}>()

const barClass = (segment: Segment) => {
    if (segment.state === "completed" || segment.state === "skipped") {
        return "bg-green-500"
    }
    if (segment.state === "in_progress") {
        return segment.is_late ? "bg-red-500" : "bg-amber-500"
    }

    return "bg-gray-200"
}

const statusLine = (segment: Segment) => {
    if (segment.key === "gate") {
        return segment.state === "completed" ? ctrans("Open") : ctrans("Locked")
    }
    if (segment.state === "completed") {
        return ctrans("Done")
    }
    if (segment.state === "skipped") {
        return ctrans("Skipped")
    }
    if (segment.state === "in_progress" && segment.progress?.total) {
        return ctrans(":done of :total", { done: String(segment.progress.done), total: String(segment.progress.total) })
    }
    if (segment.due_at) {
        return useFormatTime(segment.due_at, { formatTime: "d MMM" })
    }

    return ""
}

const statusClass = (segment: Segment) => {
    if (segment.state === "completed") {
        return "text-green-700"
    }
    if (segment.state === "in_progress") {
        return segment.is_late ? "text-red-600" : "text-amber-700"
    }

    return "text-gray-400"
}
</script>

<template>
    <div class="overflow-x-auto border-b border-gray-200 px-4 py-3 [scrollbar-width:thin] md:px-6">
        <ol class="grid min-w-[56rem] grid-cols-10 gap-2">
            <li v-for="segment in segments" :key="segment.key" class="min-w-0">
                <div class="h-1 rounded-full" :class="barClass(segment)" />
                <div class="mt-2 text-xs tabular-nums text-gray-400">{{ segment.number }}</div>
                <div class="truncate text-sm" :class="segment.state === 'pending' || segment.state === 'locked' ? 'text-gray-500' : 'font-medium text-gray-900'" v-tooltip="segment.label">
                    {{ segment.label }}
                </div>
                <div class="text-xs tabular-nums" :class="statusClass(segment)">{{ statusLine(segment) }}</div>
            </li>
        </ol>
    </div>
</template>
