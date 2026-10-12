<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import { computed } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBoxOpen, faExclamationTriangle, faTruckContainer, faList } from "@fal"

library.add(faBoxOpen, faExclamationTriangle, faTruckContainer, faList)

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    agentName: string
    stockDelivery: { reference: string, state_label: string, url: string }
    listUrl: string
    currency: string | null
    orgCurrency: string
    orgExchange: number | null
    container: { label: string, cbm: number }
    summary: {
        products: number
        suppliers: number
        cartons: number
        amount: number
        cbm: number
        products_without_volume: number
        purchase_orders: number
        days: number
    }
    rows: object
}>()

const locale = useLocaleStore()

const money = (amount: number) => (props.currency ? locale.currencyFormat(props.currency, amount) : locale.number(amount))
const orgMoney = (amount: number) => (props.orgExchange ? locale.currencyFormat(props.orgCurrency, amount * props.orgExchange) : null)
const volume = (cbm: number | null) => (cbm === null ? "—" : `${locale.number(cbm)} m³`)

const fill = computed(() => (props.container.cbm ? (props.summary.cbm / props.container.cbm) * 100 : 0))
const isWorthBooking = computed(() => fill.value >= 90)
const volumeToGo = computed(() => Math.max(0, Math.round((props.container.cbm - props.summary.cbm) * 100) / 100))
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-col gap-4 pb-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <div :title="stockDelivery.reference" class="max-w-xl truncate text-base font-semibold text-gray-900">{{ stockDelivery.reference }}</div>
                <div class="text-xs text-gray-500">{{ stockDelivery.state_label }} · {{ ctrans("filling for :count days", { count: locale.number(summary.days) }) }}</div>
            </div>
            <div class="flex gap-2">
                <Link :href="listUrl">
                    <Button :label="ctrans('All organisations')" type="tertiary" icon="fal fa-list" size="s" />
                </Link>
                <Link :href="stockDelivery.url">
                    <Button :label="ctrans('Stock delivery')" type="tertiary" icon="fal fa-truck-container" size="s" />
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">{{ ctrans("Of a :container", { container: container.label }) }}</div>
                        <div class="text-3xl font-semibold tabular-nums" :class="isWorthBooking ? 'text-green-600' : 'text-gray-900'">{{ locale.number(Math.round(fill * 10) / 10) }}%</div>
                    </div>
                    <div class="text-right text-sm tabular-nums text-gray-700">
                        <div>
                            {{ volume(summary.cbm) }} / {{ volume(container.cbm) }}
                            <span v-if="summary.products_without_volume" v-tooltip="ctrans(':count products have no carton volume recorded and are not counted', { count: summary.products_without_volume })" class="cursor-help text-amber-600">
                                <FontAwesomeIcon :icon="faExclamationTriangle" aria-hidden="true" />
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">{{ isWorthBooking ? ctrans("Enough to book a container") : ctrans(":volume to go", { volume: volume(volumeToGo) }) }}</div>
                    </div>
                </div>
                <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-gray-100" role="progressbar" :aria-valuenow="Math.round(fill)" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full" :class="isWorthBooking ? 'bg-green-500' : 'bg-gray-500'" :style="{ width: `${Math.min(100, fill)}%` }" />
                </div>
            </section>

            <dl class="grid grid-cols-2 gap-2 text-center">
                <div class="flex flex-col justify-center rounded-xl border border-gray-200 bg-white px-2 py-2 shadow-sm">
                    <dd class="text-lg font-semibold tabular-nums text-gray-900">{{ locale.number(summary.cartons) }}</dd>
                    <dt class="text-xs text-gray-500">{{ ctrans("cartons") }}</dt>
                </div>
                <div class="flex flex-col justify-center rounded-xl border border-gray-200 bg-white px-2 py-2 shadow-sm">
                    <dd class="text-lg font-semibold tabular-nums text-gray-900">{{ locale.number(summary.products) }}</dd>
                    <dt class="text-xs text-gray-500">{{ ctrans("products from :count suppliers", { count: locale.number(summary.suppliers) }) }}</dt>
                </div>
                <div class="flex flex-col justify-center rounded-xl border border-gray-200 bg-white px-2 py-2 shadow-sm">
                    <dd class="text-lg font-semibold tabular-nums text-gray-900">{{ orgMoney(summary.amount) ?? money(summary.amount) }}</dd>
                    <dt class="text-xs tabular-nums text-gray-500">{{ orgExchange ? money(summary.amount) : ctrans("total") }}</dt>
                </div>
                <div class="flex flex-col justify-center rounded-xl border border-gray-200 bg-white px-2 py-2 shadow-sm">
                    <dd class="text-lg font-semibold tabular-nums text-gray-900">{{ locale.number(summary.purchase_orders) }}</dd>
                    <dt class="text-xs text-gray-500">{{ ctrans("purchase orders") }}</dt>
                </div>
            </dl>
        </div>

        <Table :resource="rows" class="mt-0">
            <template #cell(code)="{ item }">
                <span class="font-medium text-gray-800">{{ item.code }}</span>
            </template>
            <template #cell(supplier_code)="{ item }">
                <span v-tooltip="item.supplier_name">{{ item.supplier_code }}</span>
            </template>
            <template #cell(cartons)="{ item }">
                <span class="tabular-nums">{{ item.cartons === null ? "—" : locale.number(item.cartons) }}</span>
            </template>
            <template #cell(amount)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ orgMoney(item.amount) ?? money(item.amount) }}</div>
                <div v-if="orgExchange" class="whitespace-nowrap text-xs leading-tight text-gray-400">{{ money(item.amount) }}</div>
            </template>
            <template #cell(cbm)="{ item }">
                <span class="whitespace-nowrap tabular-nums">{{ volume(item.cbm) }}</span>
            </template>
        </Table>

        <p class="mt-8 max-w-3xl text-xs text-gray-400">
            {{ ctrans("What :agent holds in its warehouse for your organisation, as its suppliers deliver. It fills up until there is enough to book a container; the invoice is raised and the container sent after that.", { agent: agentName }) }}
        </p>
    </div>
</template>
