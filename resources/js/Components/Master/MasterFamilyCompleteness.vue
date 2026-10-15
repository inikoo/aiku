<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faEdit } from "@fal"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import Button from "@/Components/Elements/Buttons/Button.vue"

const props = defineProps<{
    data: {
        score: number
        complete: number
        total: number
        items: Array<{
            key: string
            label: string
            is_complete: boolean
        }>
    }
    shopsTotal: number
}>()

const emit = defineEmits<{
    edit: []
}>()

const GAUGE_RADIUS = 80

const gaugeBands = [
    { from: 0, to: 0.6, color: "#f87171" },
    { from: 0.6, to: 0.9, color: "#fbbf24" },
    { from: 0.9, to: 1, color: "#34d399" },
]

const gaugePoint = (fraction: number, radius = GAUGE_RADIUS) => {
    const angle = Math.PI * (1 - fraction)

    return { x: 100 + radius * Math.cos(angle), y: 100 - radius * Math.sin(angle) }
}

const gaugeArc = (from: number, to: number) => {
    const start = gaugePoint(from)
    const end = gaugePoint(to)

    return `M ${start.x} ${start.y} A ${GAUGE_RADIUS} ${GAUGE_RADIUS} 0 0 1 ${end.x} ${end.y}`
}

const scoreFraction = computed(() => Math.min(Math.max(props.data.score, 0), 100) / 100)
const marker = computed(() => gaugePoint(scoreFraction.value))
const needleTip = computed(() => gaugePoint(scoreFraction.value, GAUGE_RADIUS - 28))

const scoreStatus = computed(() => {
    if (props.data.score === 100) {
        return { label: ctrans("Complete"), class: "bg-green-100 text-green-700" }
    }
    if (props.data.score >= 75) {
        return { label: ctrans("Nearly complete"), class: "bg-amber-100 text-amber-700" }
    }
    if (props.data.score >= 50) {
        return { label: ctrans("Needs work"), class: "bg-orange-100 text-orange-700" }
    }

    return { label: ctrans("Incomplete"), class: "bg-red-100 text-red-700" }
})

const sortedItems = computed(() => [...props.data.items].sort((a, b) => Number(a.is_complete) - Number(b.is_complete)))
const missingItems = computed(() => props.data.items.filter(item => !item.is_complete))
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="px-4 pt-4 pb-3">
            <h3 class="font-semibold text-gray-700">{{ ctrans("Master completeness") }}</h3>
            <p class="text-xs text-gray-500">{{ ctrans("Information the Master provides for all :count shop pages", { count: shopsTotal }) }}</p>
        </div>

        <div class="grid grid-cols-1 border-t border-gray-100 md:grid-cols-2">
            <div class="flex flex-col items-center justify-center bg-gray-50 px-4 py-6">
                <svg viewBox="0 0 200 125" class="w-full max-w-64" role="img" :aria-label="ctrans(':score out of 100', { score: data.score })">
                    <path v-for="band in gaugeBands" :key="band.from" :d="gaugeArc(band.from, band.to)" :stroke="band.color" stroke-width="18" fill="none" />
                    <line x1="100" y1="100" :x2="needleTip.x" :y2="needleTip.y" stroke="#1f2937" stroke-width="3" stroke-linecap="round" />
                    <circle cx="100" cy="100" r="5" fill="#1f2937" />
                    <circle :cx="marker.x" :cy="marker.y" r="7" fill="#fff" stroke="#1f2937" stroke-width="3" />
                    <text x="20" y="122" text-anchor="middle" class="fill-gray-500 text-[9px]">0</text>
                    <text x="180" y="122" text-anchor="middle" class="fill-gray-500 text-[9px]">100</text>
                </svg>

                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-4xl font-bold text-gray-800">{{ data.score }}</span>
                    <span class="text-sm text-gray-500">/ 100</span>
                </div>
                <span class="mt-2 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="scoreStatus.class">{{ scoreStatus.label }}</span>
                <p class="mt-2 text-xs text-gray-500">
                    {{ ctrans(":complete of :total items complete · applied to :shops shop pages", { complete: data.complete, total: data.total, shops: shopsTotal }) }}
                </p>
            </div>

            <div class="flex flex-col gap-4 p-4">
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ctrans("All :count items", { count: data.total }) }}</h4>
                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="item in sortedItems"
                            :key="item.key"
                            class="flex items-center gap-1.5 rounded border px-2 py-1 text-xs"
                            :class="item.is_complete ? 'border-green-200 bg-green-50 text-gray-700' : 'border-red-300 bg-red-50 font-semibold text-gray-800'">
                            <FontAwesomeIcon :icon="item.is_complete ? faCheck : faTimes" :class="item.is_complete ? 'text-green-600' : 'text-red-500'" fixed-width aria-hidden="true" />
                            <span class="min-w-0 break-words">{{ item.label }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="missingItems.length" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-gray-700">
                    <span class="font-semibold text-red-600">{{ ctransChoice(":count item missing|:count items missing", missingItems.length, { count: missingItems.length }) }}</span>
                    · {{ missingItems.map(item => item.label).join(", ") }}
                </div>
                <div v-else class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700">
                    {{ ctrans("All items complete") }}
                </div>

                <div>
                    <Button type="tertiary" :label="ctrans('Edit in page settings')" :icon="faEdit" @click="emit('edit')" />
                </div>
            </div>
        </div>
    </div>
</template>
