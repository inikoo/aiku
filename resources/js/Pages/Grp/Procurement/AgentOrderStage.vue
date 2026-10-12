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
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronRight, faChevronDown, faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt } from "@fal"

library.add(faChevronRight, faChevronDown, faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt)

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
    note: string
    agentName: string
    orgCurrency: string
    container: { label: string, cbm: number }
    scope: "ours" | "all"
    oursCount: number
    lines: Record<number, Line[]>
    rows: { data: Row[] }
}>()

const locale = useLocaleStore()

const rowAt = (index: number) => props.rows.data[index]

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

        <Table :resource="rows" class="mt-0" useExpandTable>
            <template #cell(reference)="{ item }">
                <div class="flex items-start gap-2">
                    <button
                        type="button"
                        class="mt-0.5 rounded p-0.5 text-gray-400 hover:bg-gray-200 hover:text-gray-700"
                        :aria-expanded="openRows.has(item.id)"
                        :aria-label="openRows.has(item.id) ? ctrans('Hide the products') : ctrans('Show the products')"
                        @click="toggleRow(item.id)">
                        <FontAwesomeIcon :icon="openRows.has(item.id) ? faChevronDown : faChevronRight" fixed-width aria-hidden="true" />
                    </button>
                    <div class="min-w-0">
                        <div v-tooltip="item.organisation_name" class="font-medium text-gray-800">{{ item.organisation_code }}</div>
                        <Link v-if="item.url" :href="item.url" :title="item.reference" class="block max-w-xs truncate text-xs leading-tight text-gray-500 underline decoration-dotted underline-offset-2">{{ item.reference }}</Link>
                        <div v-else :title="item.reference" class="max-w-xs truncate text-xs leading-tight text-gray-400">{{ item.reference }}</div>
                    </div>
                </div>
            </template>
            <template #cell(supplier_code)="{ item }">
                <div class="font-medium text-gray-800">{{ item.supplier_code }}</div>
                <div :title="item.supplier_name ?? ''" class="max-w-[14rem] truncate text-xs leading-tight text-gray-500">{{ item.supplier_name }}</div>
            </template>
            <template #cell(purchase_orders)="{ item }">
                <span class="tabular-nums">{{ locale.number(item.purchase_orders ?? 0) }}</span>
            </template>
            <template #cell(date)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ day(item.date) }}</div>
                <div class="text-xs leading-tight text-gray-500">{{ item.state_label }}</div>
            </template>
            <template #cell(eta)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ day(item.eta) }}</div>
                <div v-if="item.days_late" class="text-xs font-medium leading-tight text-amber-600">{{ ctrans(":count days late", { count: locale.number(item.days_late) }) }}</div>
            </template>
            <template #cell(lines)="{ item }">
                <span class="tabular-nums">{{ locale.number(item.lines) }}</span>
            </template>
            <template #cell(cartons)="{ item }">
                <span class="tabular-nums">{{ cartons(item.cartons) }}</span>
            </template>
            <template #cell(amount)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ orgMoney(item, item.amount) ?? money(item, item.amount) }}</div>
                <div v-if="item.org_exchange" class="whitespace-nowrap text-xs leading-tight text-gray-400">{{ money(item, item.amount) }}</div>
            </template>
            <template #cell(cbm)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ volume(item.cbm) }}</div>
                <div v-if="item.cbm" v-tooltip="ctrans(':share of a :container', { share: containerShare(item.cbm), container: container.label })" class="text-xs leading-tight text-gray-400">{{ containerShare(item.cbm) }}</div>
            </template>
            <template #expandRow="{ item }">
                <div v-if="rowAt(item.rowIndex) && openRows.has(rowAt(item.rowIndex).id)" class="border-y border-gray-200 bg-gray-50 py-2 pl-12 pr-4">
                    <table class="w-full text-sm text-gray-600">
                        <thead class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-1 pr-2">{{ ctrans("Product") }}</th>
                                <th class="px-2 py-1 text-right">{{ ctrans("Cartons") }}</th>
                                <th class="px-2 py-1 text-right">{{ ctrans("Unit cost") }}</th>
                                <th class="px-2 py-1 text-right">{{ ctrans("Total") }}</th>
                                <th class="py-1 pl-2 text-right">{{ ctrans("Volume") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="line in lines[rowAt(item.rowIndex).id] ?? []" :key="line.id" class="border-t border-gray-200">
                                <td class="py-1.5 pr-2">
                                    <span class="font-medium text-gray-700">{{ line.code }}</span>
                                    <span class="ml-2">{{ line.name }}</span>
                                </td>
                                <td class="px-2 py-1.5 text-right tabular-nums">{{ cartons(line.cartons) }}</td>
                                <td class="px-2 py-1.5 text-right tabular-nums">{{ line.unit_cost === null ? "—" : money(rowAt(item.rowIndex), line.unit_cost) }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">
                                    <div>{{ orgMoney(rowAt(item.rowIndex), line.amount) ?? money(rowAt(item.rowIndex), line.amount) }}</div>
                                    <div v-if="rowAt(item.rowIndex).org_exchange" class="text-xs leading-tight text-gray-400">{{ money(rowAt(item.rowIndex), line.amount) }}</div>
                                </td>
                                <td class="py-1.5 pl-2 whitespace-nowrap text-right tabular-nums">{{ volume(line.cbm) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </Table>

        <p class="mt-8 max-w-3xl text-xs text-gray-400">{{ note }}</p>
    </div>
</template>
