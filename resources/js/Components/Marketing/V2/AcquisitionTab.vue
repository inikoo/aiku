<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import Select from "primevue/select"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { BAR_TRACK_COLOR, CHANNEL_COLORS, OTHER_CHANNEL_COLOR, channelColor } from "@/Components/Marketing/V2/palette"

type WindowFigures = {
    days: number
    revenue: number
    buyers: number
    revenue_per_buyer: number | null
    state: "final" | "growing" | "not_yet"
}

type Channel = {
    type: string
    name: string
    is_paid: boolean
    registrations: number
    buyers: number
    spend: number
    cac: number | null
    windows: WindowFigures[]
}

const props = defineProps<{
    data: {
        currency_code: string
        period_label: string
        from: string | null
        to: string | null
        windows: number[]
        totals: {
            registrations: number
            buyers: number
            paid_registrations: number
            paid_buyers: number
            paid_spend: number
            paid_cac: number | null
            no_first_touch: number
            no_first_touch_buyers: number
        }
        channels: Channel[]
        cohorts: {
            months: { key: string, label: string }[]
            channels: {
                type: string
                name: string
                customers: number
                rows: { key: string, label: string, customers: number, cells: WindowFigures[] }[]
            }[]
        }
    }
}>()

const locale = useLocaleStore()
const money = (value: number) => locale.currencyFormat(props.data.currency_code, value)
const pct = (part: number, whole: number) => whole > 0 ? (part / whole * 100).toFixed(1) + "%" : "—"

const withCustomers = computed(() => props.data.channels.filter(channel => channel.registrations > 0))

const donutSlices = computed(() => {
    const slices: { key: string, name: string, color: string, buyers: number, registrations: number }[] = []
    const other = { key: "other", name: ctrans("Other channels"), color: OTHER_CHANNEL_COLOR, buyers: 0, registrations: 0 }

    for (const channel of withCustomers.value) {
        if (channel.type === "no_first_touch" || CHANNEL_COLORS[channel.type]) {
            slices.push({ key: channel.type, name: channel.name, color: channelColor(channel.type), buyers: channel.buyers, registrations: channel.registrations })
        } else {
            other.buyers += channel.buyers
            other.registrations += channel.registrations
        }
    }

    if (other.registrations > 0) {
        slices.splice(slices.findIndex(slice => slice.key === "no_first_touch") === -1 ? slices.length : slices.findIndex(slice => slice.key === "no_first_touch"), 0, other)
    }

    return slices
})

const donutBackground = computed(() => {
    const total = donutSlices.value.reduce((sum, slice) => sum + slice.buyers, 0)
    if (total === 0) {
        return BAR_TRACK_COLOR
    }
    let start = 0
    const stops = donutSlices.value.filter(slice => slice.buyers > 0).map(slice => {
        const end = start + slice.buyers / total * 360
        const stop = `${slice.color} ${start.toFixed(2)}deg ${(end - 0.8).toFixed(2)}deg, #ffffff ${(end - 0.8).toFixed(2)}deg ${end.toFixed(2)}deg`
        start = end
        return stop
    })
    return `conic-gradient(${stops.join(", ")})`
})

const paidChannels = computed(() => props.data.channels.filter(channel => channel.is_paid))
const maxCac = computed(() => Math.max(1, ...paidChannels.value.map(channel => channel.cac ?? 0)))

const windowDays = ref(props.data.windows[0])
const windowOptions = computed(() => props.data.windows.map(days => ({ label: ctrans(":days days", { days }), value: days })))

const revenueRows = computed(() => withCustomers.value
    .map(channel => ({ ...channel, figures: channel.windows.find(window => window.days === windowDays.value)! }))
    .filter(row => row.buyers > 0))

const windowReached = computed(() => revenueRows.value.some(row => row.figures.state !== "not_yet"))
const windowGrowing = computed(() => revenueRows.value.some(row => row.figures.state === "growing"))
const maxRevenuePerBuyer = computed(() => Math.max(1, ...revenueRows.value.map(row => row.figures.revenue_per_buyer ?? 0)))

const cohortChannelOptions = computed(() => props.data.cohorts.channels.map(channel => ({ label: channel.name, value: channel.type })))
const cohortChannel = ref(
    props.data.cohorts.channels.find(channel => channel.type === "google-ads")?.type
    ?? props.data.cohorts.channels[0]?.type
)
const cohort = computed(() => props.data.cohorts.channels.find(channel => channel.type === cohortChannel.value))

const stateHelp: Record<string, string> = {
    final: ctrans("Every customer of this cohort is past this window: the figure is final."),
    growing: ctrans("Some customers of this cohort are still inside this window: the figure keeps growing."),
    not_yet: ctrans("No customer of this cohort has got this far yet."),
}
</script>

<template>
    <div class="px-4 py-5 md:px-6 space-y-6">
        <p v-if="data.from" class="text-xs text-gray-400">
            {{ ctrans("Customers who registered since") }} {{ data.from }}<span v-if="data.to"> {{ ctrans("to") }} {{ data.to }}</span>
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                <div class="text-xs text-gray-500">{{ ctrans("Registrations") }}</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 tabular-nums">{{ locale.number(data.totals.registrations) }}</div>
            </div>
            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                <div class="text-xs text-gray-500">{{ ctrans("New buyers") }}</div>
                <div class="mt-1 flex items-baseline gap-2 tabular-nums">
                    <span class="text-3xl font-semibold text-gray-900">{{ locale.number(data.totals.buyers) }}</span>
                    <span class="text-sm text-gray-500">{{ pct(data.totals.buyers, data.totals.registrations) }}</span>
                </div>
            </div>
            <div class="rounded-xl bg-[--app-accent-soft] ring-1 ring-[--app-accent-muted] p-5">
                <div class="text-xs text-[--app-accent-deep]">{{ ctrans("Brought by ads") }}</div>
                <div class="mt-1 flex items-baseline gap-2 tabular-nums text-[--app-accent-deep]">
                    <span class="text-3xl font-semibold">{{ pct(data.totals.paid_registrations, data.totals.registrations) }}</span>
                    <span class="text-sm">{{ locale.number(data.totals.paid_buyers) }} {{ ctrans("buyers") }}</span>
                </div>
            </div>
            <div class="rounded-xl bg-[--app-accent] p-5 text-[--app-accent-text]">
                <div class="text-xs opacity-90">{{ ctrans("Paid CAC") }}</div>
                <div class="mt-1 text-3xl font-semibold tabular-nums">{{ data.totals.paid_cac !== null ? money(data.totals.paid_cac) : "—" }}</div>
                <div class="mt-0.5 text-xs opacity-90 tabular-nums">{{ money(data.totals.paid_spend) }} {{ ctrans("ad spend") }}</div>
            </div>
            <div class="rounded-xl ring-1 ring-amber-200 bg-amber-50 p-5">
                <div class="text-xs text-amber-800" v-tooltip="ctrans('No touch in the 90 days before registering.')">{{ ctrans("No first touch") }}</div>
                <div class="mt-1 flex items-baseline gap-2 tabular-nums text-amber-800">
                    <span class="text-3xl font-semibold">{{ pct(data.totals.no_first_touch, data.totals.registrations) }}</span>
                    <span class="text-sm">{{ locale.number(data.totals.no_first_touch) }}</span>
                </div>
            </div>
        </div>

        <div v-if="data.totals.registrations === 0" class="rounded-xl ring-1 ring-gray-200 bg-white py-10 text-center text-sm text-gray-500">
            {{ ctrans("Nobody registered in this period") }}
        </div>

        <template v-else>
            <div class="flex flex-col gap-6 lg:flex-row">
                <section class="flex-1 min-w-0 rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-donut">
                    <h2 id="v2-donut" class="text-sm font-medium text-gray-800">{{ ctrans("Buyers by acquisition channel") }}</h2>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-8">
                        <div role="img" :aria-label="ctrans('Donut chart of new buyers by acquisition channel')"
                             class="flex h-48 w-48 shrink-0 items-center justify-center rounded-full" :style="{ background: donutBackground }">
                            <div class="flex h-28 w-28 flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-3xl font-semibold text-gray-900 tabular-nums">{{ locale.number(data.totals.buyers) }}</span>
                                <span class="text-xs text-gray-500">{{ ctrans("buyers") }}</span>
                            </div>
                        </div>
                        <table class="text-sm">
                            <thead class="whitespace-nowrap">
                                <tr class="text-xs text-gray-400">
                                    <th class="pb-1.5 pr-4 text-left font-normal">{{ ctrans("Channel") }}</th>
                                    <th class="pb-1.5 px-2 text-right font-normal">{{ ctrans("Registrations") }}</th>
                                    <th class="pb-1.5 pl-2 text-right font-normal">{{ ctrans("Buyers") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="slice in donutSlices" :key="slice.key" class="text-gray-700">
                                    <td class="py-1 pr-4">
                                        <span class="flex items-center gap-2">
                                            <span class="h-3 w-3 shrink-0 rounded-sm" :style="{ background: slice.color }" aria-hidden="true" />
                                            <span :class="slice.key === 'no_first_touch' ? 'italic text-gray-500' : ''">{{ slice.name }}</span>
                                        </span>
                                    </td>
                                    <td class="py-1 px-2 text-right tabular-nums text-gray-500">{{ locale.number(slice.registrations) }}</td>
                                    <td class="py-1 pl-2 text-right font-medium tabular-nums">{{ locale.number(slice.buyers) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="flex-1 min-w-0 rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-cac">
                    <h2 id="v2-cac" class="text-sm font-medium text-gray-800">{{ ctrans("Cost per new buyer") }}</h2>
                    <span class="text-xs text-gray-400">{{ ctrans("Ad spend over the period divided by the new buyers the channel brought in") }}</span>
                    <div v-if="paidChannels.length" class="mt-4 flex h-56 items-end justify-around gap-4">
                        <div v-for="channel in paidChannels" :key="channel.type"
                             v-tooltip="ctrans(':channel · :spend spend ÷ :buyers buyers = :cac per buyer', { channel: channel.name, spend: money(channel.spend), buyers: locale.number(channel.buyers), cac: channel.cac !== null ? money(channel.cac) : '—' })"
                             class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                            <span class="text-sm font-medium text-gray-800 tabular-nums">{{ channel.cac !== null ? money(channel.cac) : "—" }}</span>
                            <div class="w-full max-w-16 rounded-t" :style="{ height: channel.cac !== null ? `${Math.max(2, channel.cac / maxCac * 150)}px` : '2px', background: channelColor(channel.type) }" />
                            <span class="text-xs text-gray-600 text-center">{{ channel.name }}</span>
                        </div>
                    </div>
                    <div v-else class="mt-4 py-10 text-center text-sm text-gray-500">{{ ctrans("No paid channel in this period") }}</div>
                </section>
            </div>

            <section class="rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-rpb">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="v2-rpb" class="text-sm font-medium text-gray-800">{{ ctrans("Revenue per buyer") }}</h2>
                        <span class="text-xs text-gray-400">{{ ctrans("Orders placed within the window after registering, per buyer") }}</span>
                    </div>
                    <SegmentedToggle v-model="windowDays" :options="windowOptions" :ariaLabel="ctrans('Window')" />
                </div>
                <div v-if="windowReached" class="mt-5 space-y-2.5">
                    <div v-for="row in revenueRows" :key="row.type"
                         v-tooltip="ctrans(':channel · :revenue revenue ÷ :buyers buyers = :perBuyer per buyer', { channel: row.name, revenue: money(row.figures.revenue), buyers: locale.number(row.figures.buyers), perBuyer: row.figures.revenue_per_buyer !== null ? money(row.figures.revenue_per_buyer) : '—' })"
                         class="grid grid-cols-[8rem_minmax(0,1fr)_6rem] md:grid-cols-[11rem_minmax(0,1fr)_7rem] items-center gap-3">
                        <span class="truncate text-sm" :class="row.type === 'no_first_touch' ? 'italic text-gray-500' : 'text-gray-700'">{{ row.name }}</span>
                        <div class="relative h-4 rounded-r-[4px]" :style="{ background: BAR_TRACK_COLOR }">
                            <div class="absolute inset-y-0 left-0 rounded-r-[4px]" :style="{ width: `${(row.figures.revenue_per_buyer ?? 0) / maxRevenuePerBuyer * 100}%`, background: channelColor(row.type) }" />
                        </div>
                        <span class="text-right text-sm font-medium text-gray-800 tabular-nums">{{ row.figures.revenue_per_buyer !== null ? money(row.figures.revenue_per_buyer) : "—" }}</span>
                    </div>
                    <p v-if="windowGrowing" class="pt-1 text-xs text-gray-400">{{ stateHelp.growing }}</p>
                </div>
                <div v-else class="mt-5 flex min-h-36 items-center justify-center rounded-lg text-sm font-medium text-gray-500"
                     style="background: repeating-linear-gradient(135deg, #f5f5f4 0 12px, #ffffff 12px 24px)">
                    {{ ctrans("Not reached yet") }}
                </div>
            </section>

            <section v-if="cohort" class="rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-cohort">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="v2-cohort" class="text-sm font-medium text-gray-800">{{ ctrans("Cohort growth") }} · {{ cohort.name }}</h2>
                        <span class="text-xs text-gray-400">{{ ctrans("Revenue per buyer by registration month, independent of the selected period") }}</span>
                    </div>
                    <Select v-model="cohortChannel" :options="cohortChannelOptions" optionLabel="label" optionValue="value" class="w-56" :aria-label="ctrans('Channel')" />
                </div>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[36rem] border-separate border-spacing-2 text-sm">
                        <thead class="whitespace-nowrap">
                            <tr class="text-xs text-gray-400">
                                <th class="text-left font-normal">{{ ctrans("Registered") }}</th>
                                <th v-for="days in data.windows" :key="days" class="text-center font-normal">{{ ctrans(":days days", { days }) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in cohort.rows" :key="row.key">
                                <td class="whitespace-nowrap pr-2 text-gray-700">
                                    {{ row.label }}
                                    <span class="ml-1 text-xs text-gray-400 tabular-nums">{{ locale.number(row.customers) }}</span>
                                </td>
                                <td v-for="cell in row.cells" :key="cell.days" v-tooltip="stateHelp[cell.state]"
                                    class="h-12 rounded-lg text-center font-medium tabular-nums"
                                    :class="{
                                        'bg-[--app-accent] text-[--app-accent-text]': cell.state === 'final',
                                        'bg-[--app-accent-soft] text-[--app-accent-deep]': cell.state === 'growing',
                                        'border-2 border-dashed border-gray-200': cell.state === 'not_yet',
                                    }">
                                    {{ cell.state !== "not_yet" && cell.revenue_per_buyer !== null ? money(cell.revenue_per_buyer) : "" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-xs text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-[--app-accent]" />{{ ctrans("Final") }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-[--app-accent-soft] ring-1 ring-[--app-accent-muted]" />{{ ctrans("Growing") }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border-2 border-dashed border-gray-300" />{{ ctrans("Not yet") }}</span>
                </div>
            </section>
        </template>
    </div>
</template>
