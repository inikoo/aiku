<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faMinus } from "@fal"
import { ctrans } from "@/Composables/useTrans"

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

const checks = computed(() => props.pageScore.areas.flatMap((area) => area.checks))
const hasUncheckedChecks = computed(() => checks.value.some((check) => check.is_passed === null))
const passedWidth = computed(() => `${Math.round(props.pageScore.passed / (props.pageScore.total || 1) * 100)}%`)

const checkLook = (check: Check) => {
    if (check.is_passed) {
        return { icon: faCheck, iconClass: "text-green-700", rowClass: "", state: ctrans("Met") }
    }
    if (check.is_passed === false) {
        return { icon: faTimes, iconClass: "text-red-700", rowClass: "bg-red-50 font-semibold", state: ctrans("Not met") }
    }

    return { icon: faMinus, iconClass: "text-gray-400", rowClass: "text-gray-500", state: ctrans("Not checked yet") }
}
</script>

<template>
    <section class="bg-white border-2 border-[--app-accent] rounded-xl p-5 flex flex-col gap-3">
        <h2 class="text-base font-semibold">{{ ctrans("SEO checklist") }}</h2>
        <div class="text-2xl font-bold tabular-nums">
            {{ pageScore.passed }} / {{ pageScore.total }}
            <span class="text-sm font-medium text-gray-500">{{ ctrans("checks met") }}</span>
        </div>
        <div role="img" :aria-label="ctrans(':met of :total SEO checks met', { met: pageScore.passed, total: pageScore.total })"
            class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
            <div class="h-full bg-[--app-accent]" :style="{ width: passedWidth }" />
        </div>
        <ul class="flex flex-col gap-0.5">
            <li v-for="check in checks" :key="check.key" class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm" :class="checkLook(check).rowClass">
                <FontAwesomeIcon :icon="checkLook(check).icon" :class="checkLook(check).iconClass" fixed-width aria-hidden="true" />
                <span class="sr-only">{{ checkLook(check).state }}:</span>
                {{ check.label }}
            </li>
        </ul>
        <p v-if="hasUncheckedChecks" class="text-xs text-gray-500">{{ ctrans("Checks marked with a dash are confirmed by the next crawl of the page.") }}</p>
        <slot />
    </section>
</template>
