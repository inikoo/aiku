<script setup lang="ts">
import { inject } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faInfoCircle } from "@fal"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"

export type SummaryCardTone = "accent" | "green" | "gray" | "red" | "amber"

export type SummaryCard = {
    key: string
    value: number
    label: string
    description?: string
    tone?: SummaryCardTone
}

defineProps<{
    cards: SummaryCard[]
    info?: string
}>()

const locale = inject("locale", aikuLocaleStructure)

const toneClass: Record<SummaryCardTone, string> = {
    accent: "border-[--app-accent] border-t-[--app-accent] bg-[--app-accent-soft]",
    green: "border-gray-200 border-t-green-500 bg-white",
    gray: "border-gray-200 border-t-gray-400 bg-white",
    red: "border-gray-200 border-t-red-500 bg-white",
    amber: "border-gray-200 border-t-amber-500 bg-white",
}
</script>

<template>
    <div class="space-y-3">
        <div
            v-if="info || $slots.info"
            class="flex items-start gap-3 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] px-4 py-3 text-sm text-gray-700"
        >
            <FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-gray-500" fixed-width aria-hidden="true" />
            <div>
                <slot name="info">{{ info }}</slot>
            </div>
        </div>

        <div class="grid grid-cols-[repeat(auto-fit,minmax(13rem,1fr))] gap-3">
            <div
                v-for="card in cards"
                :key="card.key"
                class="rounded-lg border border-t-4 px-4 py-3"
                :class="toneClass[card.tone ?? 'gray']"
            >
                <div class="text-2xl font-semibold tabular-nums text-gray-900">{{ locale.number(card.value) }}</div>
                <div class="mt-1 text-sm font-medium text-gray-800">{{ card.label }}</div>
                <div v-if="card.description" class="text-xs text-gray-500">{{ card.description }}</div>
            </div>
        </div>
    </div>
</template>
