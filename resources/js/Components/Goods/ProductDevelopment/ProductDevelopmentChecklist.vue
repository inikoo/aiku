<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faCircle, faMinusCircle } from "@fal"
library.add(faCheckCircle, faCircle, faMinusCircle)

defineProps<{
    rows: {
        id: number
        label: string
        role: string
        required: boolean
        state: string
        state_label: string
        na_reason: string | null
    }[]
    showRole?: boolean
}>()

const stateIcon: Record<string, { icon: string, class: string }> = {
    complete: { icon: "fal fa-check-circle", class: "text-green-600" },
    not_applicable: { icon: "fal fa-minus-circle", class: "text-gray-400" },
    missing: { icon: "fal fa-circle", class: "text-amber-500" },
}
</script>

<template>
    <ul class="divide-y divide-gray-100">
        <li v-for="row in rows" :key="row.id" class="flex items-start gap-2 py-1.5 text-sm">
            <FontAwesomeIcon :icon="stateIcon[row.state].icon" :class="stateIcon[row.state].class" class="mt-0.5" fixed-width v-tooltip="row.state_label" aria-hidden="true" />
            <div class="min-w-0 flex-1">
                <span :class="row.state === 'missing' ? 'text-gray-900' : 'text-gray-500'">{{ row.label }}</span>
                <span v-if="!row.required" class="ml-1 text-xs text-gray-400">({{ ctrans("optional") }})</span>
                <div v-if="row.na_reason" class="text-xs text-gray-500">{{ ctrans("N/A") }}: {{ row.na_reason }}</div>
            </div>
            <span v-if="showRole" class="shrink-0 text-xs text-gray-400">{{ row.role }}</span>
        </li>
        <li v-if="!rows.length" class="py-2 text-sm text-gray-400">{{ ctrans("No checklist items") }}</li>
    </ul>
</template>
