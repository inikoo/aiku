<script setup lang="ts">
import { inject } from "vue"
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
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
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
</template>
