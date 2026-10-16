<script setup lang="ts">
import { computed } from "vue"

const props = withDefaults(defineProps<{
    value: number
    max?: number
    label?: string
    caption?: string
}>(), { max: 100 })

const CENTER_X = 100
const CENTER_Y = 100
const RADIUS = 80

const bands = [
    { from: 0, to: 0.6, class: "stroke-red-400" },
    { from: 0.6, to: 0.88, class: "stroke-amber-400" },
    { from: 0.88, to: 1, class: "stroke-emerald-400" },
]

const ratio = computed(() => Math.min(1, Math.max(0, props.max > 0 ? props.value / props.max : 0)))

const pointAt = (fraction: number, radius = RADIUS) => {
    const angle = Math.PI * (1 - fraction)

    return { x: CENTER_X + radius * Math.cos(angle), y: CENTER_Y - radius * Math.sin(angle) }
}

const arcPath = (from: number, to: number) => {
    const start = pointAt(from)
    const end = pointAt(to)

    return `M ${start.x} ${start.y} A ${RADIUS} ${RADIUS} 0 0 1 ${end.x} ${end.y}`
}

const marker = computed(() => pointAt(ratio.value))
const needleTip = computed(() => pointAt(ratio.value, RADIUS - 22))
</script>

<template>
    <div class="flex flex-col items-center">
        <svg viewBox="0 0 200 118" class="w-full max-w-[16rem]" role="img" :aria-label="`${value} / ${max}`">
            <path v-for="band in bands" :key="band.from" :d="arcPath(band.from, band.to)" :class="band.class" fill="none" stroke-width="16" />
            <line :x1="CENTER_X" :y1="CENTER_Y" :x2="needleTip.x" :y2="needleTip.y" class="stroke-gray-800" stroke-width="3" stroke-linecap="round" />
            <circle :cx="CENTER_X" :cy="CENTER_Y" r="6" class="fill-gray-800" />
            <circle :cx="marker.x" :cy="marker.y" r="7" class="fill-white stroke-gray-800" stroke-width="3" />
            <text x="18" y="116" class="fill-gray-400 text-[9px]">0</text>
            <text x="172" y="116" class="fill-gray-400 text-[9px]">{{ max }}</text>
        </svg>
        <div class="-mt-1 flex items-baseline gap-1">
            <span class="text-4xl font-semibold tabular-nums text-gray-900">{{ Math.round(value) }}</span>
            <span class="text-sm text-gray-500">/ {{ max }}</span>
        </div>
        <span v-if="label" class="mt-2 rounded-full px-3 py-0.5 text-sm font-medium"
            :class="ratio >= 1 ? 'bg-green-50 text-green-700' : ratio >= 0.6 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'">
            {{ label }}
        </span>
        <span v-if="caption" class="mt-2 text-sm text-gray-600">{{ caption }}</span>
    </div>
</template>
