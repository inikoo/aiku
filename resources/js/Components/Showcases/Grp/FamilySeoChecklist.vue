<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"
import ChecklistItems from "@/Components/DataDisplay/ChecklistItems.vue"

interface Check {
    key: string
    label: string
    is_passed: boolean | null
}

const props = defineProps<{
    pageScore: {
        passed: number
        total: number
        areas: { key: string, label: string, checks: Check[] }[]
    }
}>()

const items = computed(() => props.pageScore.areas.flatMap((area) => area.checks).map((check) => ({
    key: check.key,
    label: check.label,
    done: check.is_passed === true,
    unchecked: check.is_passed === null,
})))
const hasUncheckedChecks = computed(() => items.value.some((item) => item.unchecked))
const percent = computed(() => (props.pageScore.total ? (props.pageScore.passed / props.pageScore.total) * 100 : 0))
</script>

<template>
    <div class="rounded-lg border border-[--app-accent] bg-white p-4">
        <h3 class="font-semibold text-gray-900">{{ ctrans("SEO checklist") }}</h3>
        <div class="mt-1 flex items-baseline gap-1">
            <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ pageScore.passed }} / {{ pageScore.total }}</span>
            <span class="text-sm text-gray-500">{{ ctrans("checks met") }}</span>
        </div>
        <div role="img" :aria-label="ctrans(':met of :total SEO checks met', { met: pageScore.passed, total: pageScore.total })"
            class="mb-3 mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full bg-[--app-accent]" :style="{ width: `${percent}%` }" />
        </div>
        <ChecklistItems :items="items" variant="list" />
        <p v-if="hasUncheckedChecks" class="mt-3 text-xs text-gray-500">{{ ctrans("Checks marked with a dash are confirmed by the next crawl of the page.") }}</p>
        <div v-if="$slots.default" class="mt-3">
            <slot />
        </div>
    </div>
</template>
