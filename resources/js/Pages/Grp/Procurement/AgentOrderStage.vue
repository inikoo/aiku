<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle, faHourglassHalf, faCheckCircle, faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt } from "@fal"

library.add(faClipboardListCheck, faBoxOpen, faShip, faWarehouseAlt)

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
    agent_invoice: "missing" | "unpaid" | "paid" | null
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
    canSeeAll: boolean
    oursCount: number
    agentInvoice: { selected: string | null, counts: Record<string, number> } | null
    rows: { data: Row[] }
}>()

const locale = useLocaleStore()

const money = (row: Row, amount: number) => (row.currency ? locale.currencyFormat(row.currency, amount) : locale.number(amount))
const orgMoney = (row: Row, amount: number) => (row.org_exchange ? locale.currencyFormat(props.orgCurrency, amount * row.org_exchange) : null)
const volume = (cbm: number | null) => (cbm === null ? "—" : `${locale.number(cbm)} m³`)
const cartons = (value: number | null) => (value === null ? "—" : locale.number(value))
const day = (date: string | null) => (date ? useFormatTime(date) : "—")
const containerShare = (cbm: number) => `${locale.number(Math.round((cbm / props.container.cbm) * 1000) / 10)}%`

const agentInvoiceStates: Record<string, { label: string, icon: object, class: string }> = {
    missing: { label: ctrans("Not set up"), icon: faExclamationTriangle, class: "text-amber-600" },
    unpaid: { label: ctrans("Not paid"), icon: faHourglassHalf, class: "text-amber-600" },
    paid: { label: ctrans("Paid"), icon: faCheckCircle, class: "text-green-600" },
}

const selectAgentInvoice = (value: string | null) => {
    router.get(window.location.pathname, value ? { agent_invoice: value } : {}, { preserveScroll: true })
}

const selectScope = (value: string | null) => {
    router.get(window.location.pathname, value === "ours" ? {} : { scope: "all" }, { preserveScroll: true })
}

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-col gap-4 pb-8">
        <div v-if="canSeeAll" class="flex flex-wrap items-center justify-between gap-3">
            <PillFilterBar
                :label="isOrdered ? ctrans('Purchase orders') : ctrans('Stock deliveries')"
                :options="[{ value: 'ours', label: ctrans('Of my organisation'), count: oursCount }]"
                :selected="scope === 'ours' ? 'ours' : null"
                @select="selectScope" />
        </div>

        <PillFilterBar
            v-if="agentInvoice"
            :label="ctrans('Agent invoice')"
            :options="Object.entries(agentInvoiceStates).map(([value, state]) => ({ value, label: state.label, count: agentInvoice.counts[value] }))"
            :selected="agentInvoice.selected"
            @select="selectAgentInvoice" />

        <Table :resource="rows" class="mt-0">
            <template #cell(reference)="{ item }">
                <Link v-if="!canSeeAll && item.url" :href="item.url" :title="item.reference" class="block max-w-xs truncate font-medium text-gray-800 underline decoration-dotted underline-offset-2">{{ item.reference }}</Link>
                <template v-else>
                <div v-tooltip="item.organisation_name" class="font-medium text-gray-800">{{ item.organisation_code }}</div>
                <Link v-if="item.url" :href="item.url" :title="item.reference" class="block max-w-xs truncate text-xs leading-tight text-gray-500 underline decoration-dotted underline-offset-2">{{ item.reference }}</Link>
                <div v-else :title="item.reference" class="max-w-xs truncate text-xs leading-tight text-gray-400">{{ item.reference }}</div>
                </template>
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
            <template #cell(agent_invoice)="{ item }">
                <span v-if="item.agent_invoice" class="inline-flex items-center gap-1.5 whitespace-nowrap" :class="agentInvoiceStates[item.agent_invoice].class">
                    <FontAwesomeIcon :icon="agentInvoiceStates[item.agent_invoice].icon" fixed-width aria-hidden="true" />
                    {{ agentInvoiceStates[item.agent_invoice].label }}
                </span>
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
        </Table>

        <p class="mt-8 max-w-3xl text-xs text-gray-400">{{ note }}</p>
    </div>
</template>
