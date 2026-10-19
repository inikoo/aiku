<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Created: Mon, 19 Oct 2026, Bali, Indonesia
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'
import Dialog from 'primevue/dialog'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle } from '@fal'
import { useFormatTime } from '@/Composables/useFormatTime'
import { useLocaleStore } from '@/Stores/locale'
import { ctrans } from '@/Composables/useTrans'
import { routeType } from '@/types/route'

type Conversions = {
    registrations: number
    orders: number
    revenue: number
    pending: number
}

const props = defineProps<{
    data: {
        period_label: string
        from: string | null
        to: string
        currency_code: string
        lookback_days: number
        tracking_started_at: string | null
        conversions_route: routeType
        unique_visitors: number
        totals: Conversions & {
            spend: number
            roas: number | null
            cac: number | null
        }
        channels: (Conversions & {
            type: string
            name: string
            group: string
            group_label: string
            route: routeType | null
            visitors: number
            spend: number
            spend_is_estimated: boolean
            roas: number | null
        })[]
        untraced: {
            direct: Conversions & { visitors: number }
            unlinked: Conversions
        }
        attribution: {
            w_shaped_orders: number
            u_shaped_orders: number
            linked_revenue_pct: number | null
        }
    }
}>()

library.add(faInfoCircle)

const locale = useLocaleStore()
const money = (value: number) => locale.currencyFormat(props.data.currency_code, value)

const REVENUE_COLOR = '#006300'
const SPEND_COLOR = '#6b7280'
const BAR_TRACK_COLOR = '#f1f1ef'

const maxBarValue = computed(() => Math.max(1, ...props.data.channels.flatMap(channel => [channel.spend, channel.revenue])))
const barWidth = (value: number) => `${Math.max(value > 0 ? 1.5 : 0, (value / maxBarValue.value) * 100)}%`

const count = (value: number) => Number.isInteger(value) ? locale.number(value) : value.toFixed(1)
const dash = (value: number, format: (value: number) => string) => value > 0 ? format(value) : '—'
type Kind = 'registrations' | 'orders' | 'revenue' | 'pending'

type ConversionRow = {
    reference: string | null
    route: routeType | null
    customer: string | null
    customer_route: routeType | null
    at: string
    amount: number
    share: number
    credited_amount: number
    model: string | null
}

const kindTitle: Record<Kind, string> = {
    registrations: ctrans('Registrations'),
    orders: ctrans('Orders'),
    revenue: ctrans('Invoices'),
    pending: ctrans('Orders awaiting invoice'),
}

const drilldown = ref<{ channel: string, kind: Kind, label: string } | null>(null)
const drilldownRows = ref<ConversionRow[]>([])
const drilldownTotal = ref(0)
const drilldownTruncated = ref(false)
const drilldownLoading = ref(false)
const drilldownFailed = ref(false)

const openDrilldown = async (channel: string, kind: Kind, label: string) => {
    drilldown.value = { channel, kind, label }
    drilldownRows.value = []
    drilldownLoading.value = true
    drilldownFailed.value = false

    try {
        const response = await axios.get(route(props.data.conversions_route.name, { ...props.data.conversions_route.parameters, channel, kind }))
        drilldownRows.value = response.data.rows
        drilldownTotal.value = response.data.total
        drilldownTruncated.value = response.data.truncated
    } catch {
        drilldownFailed.value = true
    } finally {
        drilldownLoading.value = false
    }
}

const isDrilldownOpen = computed({
    get: () => drilldown.value !== null,
    set: (open: boolean) => { if (!open) drilldown.value = null },
})

const showsAmounts = computed(() => drilldown.value?.kind === 'revenue' || drilldown.value?.kind === 'pending')
const modelLabel = (model: string | null) => model === 'w_shaped' ? ctrans('W-shaped') : model === 'u_shaped' ? ctrans('U-shaped') : '—'

const roasClass = (roas: number | null) => roas === null ? 'text-gray-300' : roas >= 1 ? 'text-[#006300]' : 'text-[#d03b3b]'

const untracedRows = computed(() => [
    {
        key: 'direct',
        label: ctrans('Direct'),
        help: ctrans('No referrer: typed the address, a bookmark, or an app. Only gets credit when no other channel touched the customer.'),
        visitors: props.data.untraced.direct.visitors,
        ...props.data.untraced.direct,
    },
    {
        key: 'unlinked',
        label: ctrans('No recorded visits'),
        help: ctrans('Customers with no visit linked to them yet, so no channel can be named.'),
        visitors: 0,
        ...props.data.untraced.unlinked,
    },
])

const columnHelp = {
    channel: ctrans('From each visit\'s landing link and referrer.'),
    visitors: ctrans('Unique browsers.'),
    spend: ctrans('Ad spend. Email is estimated.'),
    pending: ctrans('Ordered, not invoiced yet.'),
    revenue: ctrans('Shared across the customer\'s earlier visits. W-shaped for a new customer\'s first order, U-shaped for other orders.'),
    registrations: ctrans('Shared across the visits before sign-up, U-shaped.'),
    orders: ctrans('Same method as revenue.'),
    roas: ctrans('Revenue divided by spend.'),
}
</script>

<template>
    <div class="space-y-4 py-4">
        <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="text-sm font-medium text-gray-800">{{ ctrans('Channel performance from website visits') }}</span>
                    <span class="ml-2 text-xs text-gray-400">{{ data.period_label.toLowerCase() }}, {{ data.currency_code }}</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" :style="{ background: REVENUE_COLOR }" />{{ ctrans('Revenue') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" :style="{ background: SPEND_COLOR }" />{{ ctrans('Spend') }}
                    </span>
                </div>
            </div>

            <p class="mt-2 text-xs text-gray-500">
                {{ ctrans('Counted from website visits, not from traffic sources.') }}
            </p>

            <div class="mt-4 grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('Unique visitors') }}</div>
                    <div class="tabular-nums text-gray-800">{{ locale.number(data.unique_visitors) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('Registrations') }}</div>
                    <div class="tabular-nums text-gray-800">{{ count(data.totals.registrations) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('Orders') }}</div>
                    <div class="tabular-nums text-gray-800">{{ count(data.totals.orders) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('Revenue') }}</div>
                    <div class="tabular-nums text-gray-800">{{ money(data.totals.revenue) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('Spend') }}</div>
                    <div class="tabular-nums text-gray-800">{{ money(data.totals.spend) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400">{{ ctrans('ROAS') }}</div>
                    <div class="tabular-nums" :class="roasClass(data.totals.roas)">{{ data.totals.roas !== null ? data.totals.roas.toFixed(2) + '×' : '—' }}</div>
                </div>
            </div>

            <div v-if="data.channels.length" class="mt-4">
                <component :is="channel.route ? Link : 'div'" v-for="channel in data.channels" :key="channel.type"
                    :href="channel.route ? route(channel.route.name, channel.route.parameters) : undefined"
                    class="grid grid-cols-[7rem_minmax(0,1fr)_5.5rem_3.5rem] md:grid-cols-[11rem_minmax(0,1fr)_7rem_4rem] items-center gap-x-3 rounded-lg px-2 py-2.5 hover:bg-gray-50">
                    <div class="min-w-0">
                        <div class="text-sm text-gray-700 truncate">{{ channel.name }}</div>
                        <div class="text-xs tabular-nums text-gray-400">
                            {{ locale.number(channel.visitors) }} {{ ctrans('visitors') }}
                            <template v-if="channel.registrations > 0"> · {{ count(channel.registrations) }} {{ ctrans('registrations') }}</template>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="h-4 flex items-center">
                            <div class="relative h-2 w-full rounded-r-[3px]" :style="{ background: BAR_TRACK_COLOR }">
                                <div class="absolute inset-y-0 left-0 rounded-r-[3px]" :style="{ width: barWidth(channel.revenue), background: REVENUE_COLOR }" />
                            </div>
                        </div>
                        <div class="h-4 flex items-center">
                            <div class="relative h-2 w-full rounded-r-[3px]" :style="{ background: BAR_TRACK_COLOR }">
                                <div class="absolute inset-y-0 left-0 rounded-r-[3px]" :style="{ width: barWidth(channel.spend), background: SPEND_COLOR }" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 text-xs tabular-nums whitespace-nowrap">
                        <div class="h-4 flex items-center justify-end" :class="channel.revenue > 0 ? 'text-gray-700' : 'text-gray-300'">
                            {{ dash(channel.revenue, money) }}
                        </div>
                        <div class="h-4 flex items-center justify-end" :class="channel.spend > 0 ? 'text-gray-500' : 'text-gray-300'">
                            {{ dash(channel.spend, money) }}
                        </div>
                    </div>

                    <div class="text-right text-sm tabular-nums" :class="roasClass(channel.roas)">
                        {{ channel.roas !== null ? channel.roas.toFixed(2) + '×' : '—' }}
                    </div>
                </component>
                <p class="mt-2 px-2 text-xs text-gray-400">
                    {{ ctrans('One shared scale across every channel; the widest bar is') }} {{ money(maxBarValue) }}
                </p>
            </div>
            <div v-else class="mt-6 text-sm text-gray-400">{{ ctrans('No website visits recorded for this period.') }}</div>

            <table v-if="data.channels.length" class="mt-5 w-full text-xs">
                <thead class="whitespace-nowrap">
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="text-left font-normal py-1.5 pr-2">{{ ctrans('Channel') }}<FontAwesomeIcon v-tooltip="columnHelp.channel" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Visitors') }}<FontAwesomeIcon v-tooltip="columnHelp.visitors" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Spend') }}<FontAwesomeIcon v-tooltip="columnHelp.spend" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Awaiting invoice') }}<FontAwesomeIcon v-tooltip="columnHelp.pending" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Revenue') }}<FontAwesomeIcon v-tooltip="columnHelp.revenue" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Registrations') }}<FontAwesomeIcon v-tooltip="columnHelp.registrations" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Orders') }}<FontAwesomeIcon v-tooltip="columnHelp.orders" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                        <th class="text-right font-normal py-1.5 pl-2">{{ ctrans('ROAS') }}<FontAwesomeIcon v-tooltip="columnHelp.roas" icon="fal fa-info-circle" class="ml-1 text-gray-300 cursor-help" /></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="channel in data.channels" :key="channel.type" class="border-b border-gray-50 text-gray-700">
                        <td class="py-1.5 pr-2">
                            {{ channel.name }}
                            <span class="ml-1 text-gray-300">{{ channel.group_label }}</span>
                        </td>
                        <td class="text-right px-2 tabular-nums">{{ dash(channel.visitors, locale.number) }}</td>
                        <td class="text-right px-2 tabular-nums">
                            {{ dash(channel.spend, money) }}<span v-if="channel.spend_is_estimated" class="text-gray-400"> *</span>
                        </td>
                        <td class="text-right px-2 tabular-nums text-gray-500"><button v-if="channel.pending > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(channel.type, 'pending', channel.name)">{{ money(channel.pending) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="channel.revenue > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(channel.type, 'revenue', channel.name)">{{ money(channel.revenue) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="channel.registrations > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(channel.type, 'registrations', channel.name)">{{ count(channel.registrations) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="channel.orders > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(channel.type, 'orders', channel.name)">{{ count(channel.orders) }}</button><template v-else>—</template></td>
                        <td class="text-right pl-2 tabular-nums" :class="roasClass(channel.roas)">{{ channel.roas !== null ? channel.roas.toFixed(2) + '×' : '—' }}</td>
                    </tr>
                    <tr class="font-medium text-gray-900 border-t border-gray-300">
                        <td class="py-1.5 pr-2">{{ ctrans('Total') }}</td>
                        <td class="text-right px-2 tabular-nums">{{ locale.number(data.unique_visitors) }}</td>
                        <td class="text-right px-2 tabular-nums">{{ dash(data.totals.spend, money) }}</td>
                        <td class="text-right px-2 tabular-nums">{{ dash(data.totals.pending, money) }}</td>
                        <td class="text-right px-2 tabular-nums">{{ dash(data.totals.revenue, money) }}</td>
                        <td class="text-right px-2 tabular-nums">{{ dash(data.totals.registrations, count) }}</td>
                        <td class="text-right px-2 tabular-nums">{{ dash(data.totals.orders, count) }}</td>
                        <td class="text-right pl-2 tabular-nums" :class="roasClass(data.totals.roas)">{{ data.totals.roas !== null ? data.totals.roas.toFixed(2) + '×' : '—' }}</td>
                    </tr>
                    <tr v-for="row in untracedRows" :key="row.key" class="text-gray-500">
                        <td class="py-1.5 pr-2"><span v-tooltip="row.help" class="cursor-help">{{ row.label }}</span></td>
                        <td class="text-right px-2 tabular-nums">{{ dash(row.visitors, locale.number) }}</td>
                        <td class="text-right px-2 tabular-nums">—</td>
                        <td class="text-right px-2 tabular-nums"><button v-if="row.pending > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(row.key, 'pending', row.label)">{{ money(row.pending) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="row.revenue > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(row.key, 'revenue', row.label)">{{ money(row.revenue) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="row.registrations > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(row.key, 'registrations', row.label)">{{ count(row.registrations) }}</button><template v-else>—</template></td>
                        <td class="text-right px-2 tabular-nums"><button v-if="row.orders > 0" type="button" class="underline decoration-dotted underline-offset-2 hover:text-gray-900" @click="openDrilldown(row.key, 'orders', row.label)">{{ count(row.orders) }}</button><template v-else>—</template></td>
                        <td class="text-right pl-2 text-gray-300">—</td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-4 text-xs text-gray-400">
                <template v-if="data.attribution.linked_revenue_pct !== null">
                    {{ ctrans(':pct% of revenue is from customers with recorded visits.', { pct: data.attribution.linked_revenue_pct }) }}
                </template>
                <template v-if="data.channels.some(channel => channel.spend_is_estimated)">* {{ ctrans('estimated cost of sending the emails') }}</template>
            </p>
        </div>
        <Dialog v-model:visible="isDrilldownOpen" modal dismissableMask :header="drilldown ? `${drilldown.label} · ${kindTitle[drilldown.kind]}` : ''" :style="{ width: '60rem', maxWidth: '95vw' }">
            <div v-if="drilldownLoading" class="py-6 text-center text-sm text-gray-400">{{ ctrans('Loading') }}...</div>
            <div v-else-if="drilldownFailed" class="py-6 text-center text-sm text-[#d03b3b]">{{ ctrans('Could not load the list.') }}</div>
            <div v-else-if="!drilldownRows.length" class="py-6 text-center text-sm text-gray-400">{{ ctrans('Nothing to show.') }}</div>
            <template v-else>
                <p v-if="drilldownTruncated" class="mb-2 text-xs text-gray-400">
                    {{ ctrans('Showing the :shown with the largest credit of :total.', { shown: drilldownRows.length, total: drilldownTotal }) }}
                </p>
                <table class="w-full text-xs">
                    <thead class="whitespace-nowrap">
                        <tr class="text-gray-400 border-b border-gray-100">
                            <th class="text-left font-normal py-1.5 pr-2">{{ drilldown?.kind === 'registrations' ? ctrans('Customer') : ctrans('Reference') }}</th>
                            <th v-if="drilldown?.kind !== 'registrations'" class="text-left font-normal py-1.5 px-2">{{ ctrans('Customer') }}</th>
                            <th class="text-left font-normal py-1.5 px-2">{{ ctrans('Date') }}</th>
                            <th v-if="showsAmounts" class="text-right font-normal py-1.5 px-2">{{ ctrans('Amount') }}</th>
                            <th class="text-right font-normal py-1.5 px-2">{{ ctrans('Credit') }}</th>
                            <th v-if="showsAmounts" class="text-right font-normal py-1.5 px-2">{{ ctrans('Credited') }}</th>
                            <th class="text-left font-normal py-1.5 pl-2">{{ ctrans('Model') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in drilldownRows" :key="index" class="border-b border-gray-50 text-gray-700">
                            <td class="py-1.5 pr-2 whitespace-nowrap">
                                <template v-if="drilldown?.kind === 'registrations'">
                                    <Link v-if="row.customer_route" :href="route(row.customer_route.name, row.customer_route.parameters)" class="hover:underline">{{ row.customer }}</Link>
                                    <template v-else>{{ row.customer }}</template>
                                </template>
                                <template v-else>
                                    <Link v-if="row.route" :href="route(row.route.name, row.route.parameters)" class="hover:underline">{{ row.reference }}</Link>
                                    <template v-else>{{ row.reference }}</template>
                                </template>
                            </td>
                            <td v-if="drilldown?.kind !== 'registrations'" class="px-2">
                                <Link v-if="row.customer_route" :href="route(row.customer_route.name, row.customer_route.parameters)" class="hover:underline">{{ row.customer }}</Link>
                                <template v-else>{{ row.customer }}</template>
                            </td>
                            <td class="px-2 whitespace-nowrap">{{ useFormatTime(row.at) }}</td>
                            <td v-if="showsAmounts" class="text-right px-2 tabular-nums">{{ money(row.amount) }}</td>
                            <td class="text-right px-2 tabular-nums">{{ row.share }}%</td>
                            <td v-if="showsAmounts" class="text-right px-2 tabular-nums">{{ money(row.credited_amount) }}</td>
                            <td class="pl-2 whitespace-nowrap text-gray-500">{{ modelLabel(row.model) }}</td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </Dialog>
    </div>
</template>
