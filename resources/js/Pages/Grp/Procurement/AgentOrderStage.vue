<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronRight, faChevronDown, faChevronLeft, faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt } from "@fal"

library.add(faChevronRight, faChevronDown, faChevronLeft, faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt)

type Row = {
    id: number
    reference: string
    organisation_code: string
    organisation_name: string
    is_ours: boolean
    url: string | null
    supplier_code: string | null
    supplier_name: string | null
    purchase_orders: number | null
    state_label: string
    lines: number
    cartons: number | null
    amount: number
    currency: string | null
    org_exchange: number | null
    cbm: number | null
    date: string | null
    eta: string | null
    days_late: number | null
}

type Line = {
    id: number
    code: string | null
    name: string | null
    cartons: number | null
    unit_cost: number | null
    amount: number
    cbm: number | null
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    stage: string
    isOrdered: boolean
    dateLabel: string
    note: string
    agentName: string
    orgCurrency: string
    container: { label: string, cbm: number }
    scope: "ours" | "all"
    oursCount: number
    lines: Record<number, Line[]>
    rows: { data: Row[], from: number | null, to: number | null, total: number, prev_page_url: string | null, next_page_url: string | null }
}>()

const locale = useLocaleStore()

const hasEta = computed(() => props.stage === "preparing" || props.stage === "on-the-way")
const columnCount = computed(() => 8 + (hasEta.value ? 1 : 0))

const money = (row: Row, amount: number) => (row.currency ? locale.currencyFormat(row.currency, amount) : locale.number(amount))
const orgMoney = (row: Row, amount: number) => (row.org_exchange ? locale.currencyFormat(props.orgCurrency, amount * row.org_exchange) : null)
const volume = (cbm: number | null) => (cbm === null ? "—" : `${locale.number(cbm)} m³`)
const cartons = (value: number | null) => (value === null ? "—" : locale.number(value))
const day = (date: string | null) => (date ? useFormatTime(date) : "—")
const containerShare = (cbm: number) => `${locale.number(Math.round((cbm / props.container.cbm) * 1000) / 10)}%`

const selectScope = (value: string | null) => {
    router.get(window.location.pathname, value === "ours" ? {} : { scope: "all" }, { preserveScroll: true })
}

const openRows = ref<Set<number>>(new Set(Object.keys(props.lines).map(Number)))
const isEveryRowOpen = computed(() => props.rows.data.length > 0 && props.rows.data.every((row) => openRows.value.has(row.id)))

const showRows = (ids: Set<number>) => {
    openRows.value = ids
    router.reload({ only: ["lines"], data: { open: [...ids].join(",") } })
}

const toggleRow = (id: number) => {
    const next = new Set(openRows.value)
    next.has(id) ? next.delete(id) : next.add(id)
    showRows(next)
}

const toggleEveryRow = () => showRows(isEveryRowOpen.value ? new Set() : new Set(props.rows.data.map((row) => row.id)))
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-col gap-4 pb-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <PillFilterBar
                :label="isOrdered ? ctrans('Purchase orders') : ctrans('Stock deliveries')"
                :options="[{ value: 'ours', label: ctrans('Of my organisation'), count: oursCount }]"
                :selected="scope === 'ours' ? 'ours' : null"
                @select="selectScope" />
            <Button
                v-if="rows.data.length"
                :label="isEveryRowOpen ? ctrans('Hide all products') : ctrans('Show all products')"
                type="tertiary"
                :icon="isEveryRowOpen ? 'fal fa-chevron-down' : 'fal fa-chevron-right'"
                size="s"
                @click="toggleEveryRow" />
        </div>

        <div v-if="rows.data.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-[11px] font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">{{ isOrdered ? ctrans("Purchase order") : ctrans("Stock delivery") }}</th>
                        <th class="px-2 py-2">{{ isOrdered ? ctrans("Supplier") : ctrans("Purchase orders") }}</th>
                        <th class="px-2 py-2">{{ dateLabel }}</th>
                        <th v-if="hasEta" class="px-2 py-2">{{ ctrans("Expected") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Products") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Cartons") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Unit cost") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Total") }}</th>
                        <th class="py-2 pl-2 pr-4 text-right">{{ ctrans("Volume") }}</th>
                    </tr>
                </thead>
                <tbody v-for="row in rows.data" :key="row.id" class="border-b border-gray-100 last:border-b-0">
                    <tr :class="{ 'bg-gray-100': row.is_ours && scope === 'all' }">
                        <td class="py-2 pl-4 pr-2">
                            <div class="flex items-start gap-2">
                                <button
                                    type="button"
                                    class="mt-0.5 rounded p-0.5 text-gray-400 hover:bg-gray-200 hover:text-gray-700"
                                    :aria-expanded="openRows.has(row.id)"
                                    :aria-label="openRows.has(row.id) ? ctrans('Hide the products') : ctrans('Show the products')"
                                    @click="toggleRow(row.id)">
                                    <FontAwesomeIcon :icon="openRows.has(row.id) ? faChevronDown : faChevronRight" fixed-width aria-hidden="true" />
                                </button>
                                <div class="min-w-0">
                                    <div v-tooltip="row.organisation_name" class="font-medium text-gray-800">{{ row.organisation_code }}</div>
                                    <Link v-if="row.url" :href="row.url" :title="row.reference" class="block max-w-xs truncate text-[11px] leading-tight text-gray-500 underline decoration-dotted underline-offset-2">{{ row.reference }}</Link>
                                    <div v-else :title="row.reference" class="max-w-xs truncate text-[11px] leading-tight text-gray-400">{{ row.reference }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2">
                            <template v-if="isOrdered">
                                <div class="font-medium text-gray-800">{{ row.supplier_code }}</div>
                                <div :title="row.supplier_name ?? ''" class="max-w-[14rem] truncate text-[11px] leading-tight text-gray-500">{{ row.supplier_name }}</div>
                            </template>
                            <span v-else class="tabular-nums">{{ locale.number(row.purchase_orders ?? 0) }}</span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap">
                            <div class="tabular-nums">{{ day(row.date) }}</div>
                            <div class="text-[11px] leading-tight text-gray-500">{{ row.state_label }}</div>
                        </td>
                        <td v-if="hasEta" class="px-2 py-2 whitespace-nowrap">
                            <div class="tabular-nums">{{ day(row.eta) }}</div>
                            <div v-if="row.days_late" class="text-[11px] font-medium leading-tight text-amber-600">{{ ctrans(":count days late", { count: locale.number(row.days_late) }) }}</div>
                        </td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ locale.number(row.lines) }}</td>
                        <td class="px-2 py-2 text-right tabular-nums">{{ cartons(row.cartons) }}</td>
                        <td />
                        <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                            <div>{{ orgMoney(row, row.amount) ?? money(row, row.amount) }}</div>
                            <div v-if="row.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(row, row.amount) }}</div>
                        </td>
                        <td class="py-2 pl-2 pr-4 whitespace-nowrap text-right tabular-nums">
                            <div>{{ volume(row.cbm) }}</div>
                            <div v-if="row.cbm" v-tooltip="ctrans(':share of a :container', { share: containerShare(row.cbm), container: container.label })" class="text-[11px] font-normal leading-tight text-gray-400">{{ containerShare(row.cbm) }}</div>
                        </td>
                    </tr>
                    <template v-if="openRows.has(row.id)">
                        <tr v-for="line in lines[row.id] ?? []" :key="line.id" class="border-t border-gray-50 text-gray-600">
                            <td class="py-1.5 pl-12 pr-2" :colspan="columnCount - 4">
                                <span class="font-medium text-gray-700">{{ line.code }}</span>
                                <span class="ml-2">{{ line.name }}</span>
                            </td>
                            <td class="px-2 py-1.5 text-right tabular-nums">{{ cartons(line.cartons) }}</td>
                            <td class="px-2 py-1.5 text-right tabular-nums">{{ line.unit_cost === null ? "—" : money(row, line.unit_cost) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">
                                <div>{{ orgMoney(row, line.amount) ?? money(row, line.amount) }}</div>
                                <div v-if="row.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(row, line.amount) }}</div>
                            </td>
                            <td class="py-1.5 pl-2 pr-4 whitespace-nowrap text-right tabular-nums">{{ volume(line.cbm) }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div v-if="rows.prev_page_url || rows.next_page_url" class="flex items-center justify-between gap-3 border-t border-gray-200 px-4 py-2 text-xs text-gray-500">
                <span class="tabular-nums">{{ ctrans(":from to :to of :total", { from: locale.number(rows.from ?? 0), to: locale.number(rows.to ?? 0), total: locale.number(rows.total) }) }}</span>
                <div class="flex gap-2">
                    <Link v-if="rows.prev_page_url" :href="rows.prev_page_url" preserve-scroll>
                        <Button :label="ctrans('Previous')" type="tertiary" icon="fal fa-chevron-left" size="s" />
                    </Link>
                    <Link v-if="rows.next_page_url" :href="rows.next_page_url" preserve-scroll>
                        <Button :label="ctrans('Next')" type="tertiary" iconRight="fal fa-chevron-right" size="s" />
                    </Link>
                </div>
            </div>
        </div>

        <p v-else class="py-16 text-center text-sm text-gray-400">
            {{ scope === "ours" ? ctrans("Nothing of your organisation here for :agent", { agent: agentName }) : ctrans("Nothing here for :agent", { agent: agentName }) }}
        </p>
        <p class="mt-8 max-w-3xl text-xs text-gray-400">{{ note }}</p>
    </div>
</template>
