<!--
  - Author Louis Perez
  - Created on 17-10-2026-23h-55m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faChevronRight, faCheckCircle } from "@fal"
library.add(faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faChevronRight, faCheckCircle)

export interface WaitingRow {
    kind: "checklist" | "design" | "review" | "print_limits" | "sign_off"
    label: string
    detail: string | null
    role_label: string
    route: routeType
    development: { code: string, name: string }
    due_at: string | null
    is_late: boolean
}

const props = defineProps<{
    rows: WaitingRow[]
    grouped?: boolean
}>()

const kindIcon: Record<WaitingRow["kind"], string> = {
    checklist: "fal fa-clipboard-list",
    design: "fal fa-pencil-ruler",
    review: "fal fa-eye",
    print_limits: "fal fa-ruler-combined",
    sign_off: "fal fa-signature",
}

const groups = computed(() => {
    const map = new Map<string, { development: WaitingRow["development"], rows: WaitingRow[] }>()
    for (const row of props.rows) {
        if (!map.has(row.development.code)) {
            map.set(row.development.code, { development: row.development, rows: [] })
        }
        map.get(row.development.code)!.rows.push(row)
    }
    return [...map.values()]
})

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <div>
        <div v-if="!rows.length" class="flex flex-col items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-500">
            <FontAwesomeIcon icon="fal fa-check-circle" class="text-2xl text-green-600" fixed-width aria-hidden="true" />
            {{ ctrans("Nothing is waiting on you") }}
        </div>

        <div v-else class="space-y-4">
            <section v-for="group in (grouped ? groups : [{ development: null, rows }])" :key="group.development?.code ?? 'all'" class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <header v-if="group.development" class="border-b border-gray-100 bg-gray-50 px-4 py-2 text-sm">
                    <span class="font-medium text-gray-900">{{ group.development.code }}</span>
                    <span class="ml-1.5 text-gray-500">{{ group.development.name }}</span>
                </header>
                <ul class="divide-y divide-gray-100">
                    <li v-for="(row, index) in group.rows" :key="index">
                        <Link :href="route(row.route.name, row.route.parameters)" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-gray-50 active:bg-gray-100">
                            <FontAwesomeIcon :icon="kindIcon[row.kind]" class="shrink-0 text-[--app-accent]" fixed-width aria-hidden="true" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm text-gray-900">{{ row.label }}</span>
                                <span class="block truncate text-xs text-gray-500">
                                    {{ [grouped ? null : row.development.code, row.role_label, row.detail].filter(Boolean).join(" · ") }}
                                </span>
                            </span>
                            <span v-if="row.due_at" class="shrink-0 text-xs tabular-nums" :class="row.is_late ? 'font-medium text-red-600' : 'text-gray-500'">
                                {{ row.is_late ? ctrans("Overdue :date", { date: formatDate(row.due_at) ?? "" }) : ctrans("Due :date", { date: formatDate(row.due_at) ?? "" }) }}
                            </span>
                            <FontAwesomeIcon icon="fal fa-chevron-right" class="shrink-0 text-xs text-gray-300" fixed-width aria-hidden="true" />
                        </Link>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
