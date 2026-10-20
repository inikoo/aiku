<!--
  - Author Louis Perez
  - Created on 18-10-2026-00h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import { Link } from "@inertiajs/vue3"
import ProductDevelopmentSpecField from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSpecField.vue"
import type { WaitingRow } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentWaitingList.vue"
import type { ChecklistGroupOptions, ChecklistRow } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronRight, faChevronDown, faArrowRight } from "@fal"
library.add(faChevronRight, faChevronDown, faArrowRight)

export interface WaitingCardRow extends WaitingRow {
    fields?: (ChecklistRow & { sku?: string })[]
    context?: { countries: { value: number, label: string }[], options: ChecklistGroupOptions, update_route: string }
}

const props = defineProps<{
    row: WaitingCardRow
    open: boolean
}>()

const emit = defineEmits<{
    toggle: []
}>()

const canFillIn = computed(() => !!props.row.fields?.length && !!props.row.context)
const dueSoon = computed(() => {
    if (!props.row.due_at) {
        return false
    }
    const days = (new Date(props.row.due_at).getTime() - Date.now()) / 86400000
    return days <= 2
})
const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <article class="overflow-hidden rounded-xl border bg-white transition-shadow" :class="open ? 'border-[--app-accent] shadow-md' : 'border-gray-200 hover:shadow-sm'">
        <component
            :is="canFillIn ? 'button' : Link"
            v-bind="canFillIn ? { type: 'button' } : { href: route(row.route.name, row.route.parameters) }"
            class="flex w-full items-start gap-3 px-4 py-3.5 text-left"
            :aria-expanded="canFillIn ? open : undefined"
            @click="canFillIn && emit('toggle')"
        >
            <span class="min-w-0 flex-1">
                <span class="block truncate font-mono text-xs text-gray-500">{{ row.development.code }} · {{ row.development.name }}</span>
                <span class="mt-0.5 block font-semibold text-gray-900" :class="open ? 'text-base sm:text-lg' : 'text-sm sm:text-base'">{{ row.label }}</span>
                <span v-if="row.detail || row.role_label" class="mt-0.5 block truncate text-xs text-gray-500">{{ [row.role_label, row.detail].filter(Boolean).join(" · ") }}</span>
            </span>
            <span v-if="row.due_at" class="shrink-0 text-xs font-medium tabular-nums" :class="row.is_late ? 'text-red-600' : dueSoon ? 'text-orange-700' : 'text-gray-500'">
                {{ row.is_late ? ctrans("Overdue :date", { date: formatDate(row.due_at) ?? "" }) : ctrans("Due :date", { date: formatDate(row.due_at) ?? "" }) }}
            </span>
            <FontAwesomeIcon :icon="canFillIn ? (open ? 'fal fa-chevron-down' : 'fal fa-chevron-right') : 'fal fa-chevron-right'" class="mt-1 shrink-0 text-xs text-gray-400" fixed-width aria-hidden="true" />
        </component>

        <div v-if="canFillIn && open" class="space-y-4 border-t border-gray-100 px-4 pb-4 pt-3">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="field in row.fields" :key="field.id" :class="['textarea', 'pictograms', 'label_marks', 'ingredients', 'photos'].includes(field.input_type) ? 'md:col-span-2 xl:col-span-3' : ''">
                    <div v-if="field.sku" class="mb-1 font-mono text-xs text-gray-500">{{ field.sku }}</div>
                    <ProductDevelopmentSpecField :row="field" :updateRoute="row.context!.update_route" :countries="row.context!.countries" :options="row.context!.options" />
                </div>
            </div>
            <Link :href="route(row.route.name, row.route.parameters)" class="inline-flex items-center gap-1.5 text-sm text-[--app-accent] hover:text-[--app-accent-strong]">
                {{ ctrans("Open :code", { code: row.development.code }) }}
                <FontAwesomeIcon icon="fal fa-arrow-right" fixed-width aria-hidden="true" />
            </Link>
        </div>
    </article>
</template>
