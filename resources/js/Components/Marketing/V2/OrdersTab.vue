<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faExclamationTriangle } from "@fal"
import { MODEL_COLORS, ORDER_TYPE_COLORS, BAR_TRACK_COLOR, NO_FIRST_TOUCH_COLOR } from "@/Components/Marketing/V2/palette"

library.add(faCheckCircle, faExclamationTriangle)

type ModelKey = "linear" | "first_touch" | "u_shaped" | "w_shaped"

type ComparedModelKey = Exclude<ModelKey, "linear">

type Figures = { orders: number, revenue: number, first: number, repeat: number }

type Channel = {
    type: string
    name: string
    is_paid: boolean
    spend: number
} & Record<ModelKey, Figures>

const props = defineProps<{
    data: {
        currency_code: string
        window_days: number
        period_label: string
        from: string | null
        to: string | null
        models: { key: ModelKey, label: string, description: string }[]
        totals: { orders: number, first: number, repeat: number, revenue: number }
        no_touch: Record<ModelKey, { orders: number, revenue: number }>
        channels: Channel[]
    }
}>()

const locale = useLocaleStore()
const money = (value: number) => locale.currencyFormat(props.data.currency_code, value)
const decimal = (value: number) => Number.isInteger(value) ? locale.number(value) : value.toFixed(1)

const metric = ref<"orders" | "revenue">("orders")
const comparedModel = ref<ComparedModelKey>("first_touch")
const mixModel = ref<ModelKey>("first_touch")

const metricOptions = [
    { label: ctrans("Orders"), value: "orders" as const },
    { label: ctrans("Revenue"), value: "revenue" as const },
]

const modelOptions = computed(() => props.data.models.map(model => ({ label: model.label, value: model.key })))
const comparedModelOptions = computed(() => modelOptions.value.filter(option => option.value !== "linear"))
const modelLabel = (key: string) => props.data.models.find(model => model.key === key)?.label ?? key
const modelDescription = (key: string) => props.data.models.find(model => model.key === key)?.description ?? ""

const format = (value: number) => metric.value === "revenue" ? money(value) : decimal(value)

const pct = (part: number, whole: number) => whole > 0 ? Math.round(part / whole * 100) + "%" : "—"

const delta = (before: number, after: number) => {
    if (before === 0 && after === 0) {
        return null
    }
    if (before === 0) {
        return { label: ctrans("new"), up: true }
    }
    const change = Math.round((after - before) / before * 100)
    return { label: (change > 0 ? "+" : "") + change + "%", up: change >= 0 }
}

const comparisonRows = computed(() => {
    const rows = props.data.channels
        .map(channel => ({
            key: channel.type,
            name: channel.name,
            linear: channel.linear[metric.value],
            compared: channel[comparedModel.value][metric.value],
            isNoTouch: false,
        }))
        .filter(row => row.linear > 0 || row.compared > 0)

    rows.push({
        key: "no_touch",
        name: ctrans("No touch"),
        linear: props.data.no_touch.linear[metric.value],
        compared: props.data.no_touch[comparedModel.value][metric.value],
        isNoTouch: true,
    })

    return rows
})

const maxComparison = computed(() => Math.max(1, ...comparisonRows.value.flatMap(row => [row.linear, row.compared])))
const barWidth = (value: number) => `${Math.max(value > 0 ? 1 : 0, value / maxComparison.value * 100)}%`

const paidOrders = (model: ModelKey) =>
    props.data.channels.filter(channel => channel.is_paid).reduce((sum, channel) => sum + channel[model].orders, 0)

const paidCredit = computed(() => ({
    linear: paidOrders("linear"),
    compared: paidOrders(comparedModel.value),
}))

const creditCheck = computed(() => props.data.models.map(model => {
    const credited = props.data.channels.reduce((sum, channel) => sum + channel[model.key].orders, 0) + props.data.no_touch[model.key].orders
    return { key: model.key, label: model.label, credited, balanced: Math.abs(credited - props.data.totals.orders) < 0.05 * Math.max(1, props.data.channels.length) }
}))

const creditBalanced = computed(() => creditCheck.value.every(check => check.balanced))

const paidChannels = computed(() => props.data.channels
    .filter(channel => channel.is_paid && (channel.spend > 0 || channel[comparedModel.value].orders > 0 || channel.linear.orders > 0))
    .map(channel => ({
        ...channel,
        orders: channel[comparedModel.value].orders,
        roas: channel.spend > 0 ? channel[comparedModel.value].revenue / channel.spend : null,
        linearRoas: channel.spend > 0 ? channel.linear.revenue / channel.spend : null,
        costPerOrder: channel.spend > 0 && channel[comparedModel.value].orders > 0 ? channel.spend / channel[comparedModel.value].orders : null,
    })))

const mixRows = computed(() => props.data.channels
    .map(channel => ({ name: channel.name, type: channel.type, ...channel[mixModel.value] }))
    .filter(row => row.orders > 0))

const maxMix = computed(() => Math.max(1, ...mixRows.value.map(row => row.orders)))
</script>

<template>
    <div class="px-4 py-5 md:px-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-gray-400">
                <template v-if="data.from">{{ ctrans("Orders placed since") }} {{ data.from }}<span v-if="data.to"> {{ ctrans("to") }} {{ data.to }}</span></template>
            </p>
            <div class="flex items-center gap-3">
                <span v-tooltip="modelDescription(comparedModel)" class="cursor-help text-xs text-gray-500">{{ ctrans("Compare :model with", { model: modelLabel("linear") }) }}</span>
                <SegmentedToggle v-model="comparedModel" :options="comparedModelOptions" :ariaLabel="ctrans('Compared model')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                <div class="text-xs text-gray-500">{{ ctrans("Orders") }}</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 tabular-nums">{{ locale.number(data.totals.orders) }}</div>
                <div v-if="data.totals.orders > 0" class="mt-3 flex h-2 gap-[2px] overflow-hidden rounded-full" aria-hidden="true">
                    <span :style="{ width: pct(data.totals.first, data.totals.orders), background: ORDER_TYPE_COLORS.first }" />
                    <span :style="{ width: pct(data.totals.repeat, data.totals.orders), background: ORDER_TYPE_COLORS.repeat }" />
                </div>
                <div class="mt-1.5 flex justify-between text-xs text-gray-600 tabular-nums">
                    <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: ORDER_TYPE_COLORS.first }" />{{ locale.number(data.totals.first) }} {{ ctrans("first") }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: ORDER_TYPE_COLORS.repeat }" />{{ locale.number(data.totals.repeat) }} {{ ctrans("repeat") }}</span>
                </div>
            </div>

            <div class="rounded-xl bg-[--app-accent-soft] ring-1 ring-[--app-accent-muted] p-5">
                <div class="text-xs text-[--app-accent-deep]">{{ ctrans("Paid ads credit") }}</div>
                <div class="mt-1 text-3xl font-semibold text-[--app-accent-deep] tabular-nums">
                    {{ delta(paidCredit.linear, paidCredit.compared)?.label ?? "—" }}
                </div>
                <div class="mt-1 text-xs text-[--app-accent-deep] tabular-nums">
                    {{ decimal(paidCredit.linear) }} → {{ decimal(paidCredit.compared) }} {{ ctrans("orders") }}
                    <span class="opacity-75">({{ modelLabel("linear") }} → {{ modelLabel(comparedModel) }})</span>
                </div>
            </div>

            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                <div class="text-xs text-gray-500">{{ ctrans("Credit check") }}</div>
                <div class="mt-1 flex items-center gap-2 text-3xl font-semibold tabular-nums" :class="creditBalanced ? 'text-green-700' : 'text-red-600'">
                    <FontAwesomeIcon :icon="creditBalanced ? 'fal fa-check-circle' : 'fal fa-exclamation-triangle'" class="text-2xl" aria-hidden="true" />
                    {{ locale.number(data.totals.orders) }}
                </div>
                <div class="mt-1 text-xs text-gray-500 tabular-nums">
                    <span v-for="(check, index) in creditCheck" :key="check.key">
                        {{ check.label }} Σ {{ check.credited.toFixed(2) }}<span v-if="index < creditCheck.length - 1"> · </span>
                    </span>
                </div>
            </div>

            <div class="rounded-xl ring-1 ring-amber-200 bg-amber-50 p-5">
                <div class="text-xs text-amber-800">{{ ctrans("No touch") }}</div>
                <div class="mt-1 text-3xl font-semibold text-amber-800 tabular-nums">
                    {{ pct(data.no_touch[comparedModel].orders, data.totals.orders) }}
                </div>
                <div class="mt-1 text-xs text-amber-800 tabular-nums">
                    {{ decimal(data.no_touch[comparedModel].orders) }} {{ ctrans("orders") }} · {{ money(data.no_touch[comparedModel].revenue) }}
                    <span class="opacity-75">({{ modelLabel(comparedModel) }})</span>
                </div>
            </div>
        </div>

        <section class="rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-comparison">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="v2-comparison" class="text-sm font-medium text-gray-800">
                        {{ modelLabel("linear") }} {{ ctrans("vs") }} {{ modelLabel(comparedModel) }}
                    </h2>
                    <span class="text-xs text-gray-400">{{ data.period_label.toLowerCase() }}, {{ data.currency_code }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-4 text-xs text-gray-600">
                        <span v-for="model in data.models.filter(model => model.key === 'linear' || model.key === comparedModel)" :key="model.key" v-tooltip="model.description" class="flex items-center gap-1.5 cursor-help">
                            <span class="rounded-sm" :class="model.key === 'linear' ? 'h-1.5 w-4' : 'h-3 w-4'" :style="{ background: MODEL_COLORS[model.key] }" />
                            {{ model.label }}
                        </span>
                    </div>
                    <SegmentedToggle v-model="metric" :options="metricOptions" :ariaLabel="ctrans('Metric')" />
                </div>
            </div>

            <div v-if="data.totals.orders > 0" class="mt-5 space-y-3">
                <div v-for="row in comparisonRows" :key="row.key"
                     v-tooltip="`${row.name} · ${modelLabel('linear')} ${format(row.linear)} · ${modelLabel(comparedModel)} ${format(row.compared)}`"
                     class="grid grid-cols-[8rem_minmax(0,1fr)_4rem] md:grid-cols-[11rem_minmax(0,1fr)_4.5rem] items-center gap-3">
                    <span class="truncate text-sm" :class="row.isNoTouch ? 'italic text-gray-500' : 'text-gray-700'">{{ row.name }}</span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <div class="relative h-1.5 flex-1 rounded-r-[3px]" :style="{ background: BAR_TRACK_COLOR }">
                                <div class="absolute inset-y-0 left-0 rounded-r-[3px]" :style="{ width: barWidth(row.linear), background: MODEL_COLORS.linear }" />
                            </div>
                            <span class="w-24 shrink-0 text-right text-xs text-gray-500 tabular-nums">{{ format(row.linear) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative h-3 flex-1 rounded-r-[4px]" :style="{ background: BAR_TRACK_COLOR }">
                                <div class="absolute inset-y-0 left-0 rounded-r-[4px]" :style="{ width: barWidth(row.compared), background: row.isNoTouch ? NO_FIRST_TOUCH_COLOR : MODEL_COLORS[comparedModel] }" />
                            </div>
                            <span class="w-24 shrink-0 text-right text-xs font-medium text-gray-800 tabular-nums">{{ format(row.compared) }}</span>
                        </div>
                    </div>
                    <span v-if="delta(row.linear, row.compared)"
                          class="justify-self-end whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium tabular-nums"
                          :class="delta(row.linear, row.compared)!.up ? 'bg-[--app-accent-soft] text-[--app-accent-deep]' : 'bg-gray-100 text-gray-700'">
                        {{ delta(row.linear, row.compared)!.up ? "▲" : "▼" }} {{ delta(row.linear, row.compared)!.label }}
                    </span>
                </div>
            </div>
            <div v-else class="mt-4 py-8 text-center text-sm text-gray-500">{{ ctrans("No orders in this period") }}</div>
        </section>

        <section v-if="paidChannels.length" aria-labelledby="v2-paid" class="space-y-3">
            <div>
                <h2 id="v2-paid" class="text-sm font-medium text-gray-800">{{ ctrans("Paid channels") }}</h2>
                <span class="text-xs text-gray-400">{{ modelLabel(comparedModel) }}, {{ ctrans("against ad spend over the period") }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div v-for="channel in paidChannels" :key="channel.type" class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                    <div class="text-sm font-medium text-gray-800">{{ channel.name }}</div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-semibold tabular-nums"
                              :class="channel.roas === null ? 'text-gray-300' : channel.roas >= 1 ? 'text-green-700' : 'text-red-600'">
                            <template v-if="channel.roas !== null">{{ channel.roas >= 1 ? "▲" : "▼" }} {{ channel.roas.toFixed(2) }}×</template>
                            <template v-else>—</template>
                        </span>
                        <span class="text-xs text-gray-500">{{ ctrans("ROAS") }}</span>
                    </div>
                    <div v-if="channel.linearRoas !== null" class="mt-0.5 text-xs text-gray-400 tabular-nums">
                        {{ modelLabel("linear") }} {{ channel.linearRoas.toFixed(2) }}×
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-700 tabular-nums">
                        <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ ctrans("Cost per order") }} {{ channel.costPerOrder !== null ? money(channel.costPerOrder) : "—" }}</span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ ctrans("Spend") }} {{ money(channel.spend) }}</span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ decimal(channel.orders) }} {{ ctrans("orders") }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="mixRows.length" class="rounded-xl ring-1 ring-gray-200 bg-white p-5" aria-labelledby="v2-mix">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="v2-mix" class="text-sm font-medium text-gray-800">{{ ctrans("First vs repeat orders") }}</h2>
                    <span v-tooltip="modelDescription(mixModel)" class="text-xs text-gray-400 cursor-help">{{ modelLabel(mixModel) }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-4 text-xs text-gray-600">
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm" :style="{ background: ORDER_TYPE_COLORS.first }" />{{ ctrans("First order") }}</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm" :style="{ background: ORDER_TYPE_COLORS.repeat }" />{{ ctrans("Repeat order") }}</span>
                    </div>
                    <SegmentedToggle v-model="mixModel" :options="modelOptions" :ariaLabel="ctrans('Attribution model')" />
                </div>
            </div>

            <div class="mt-5 space-y-2.5">
                <div v-for="row in mixRows" :key="row.type"
                     v-tooltip="`${row.name} · ${decimal(row.first)} ${ctrans('first')} · ${decimal(row.repeat)} ${ctrans('repeat')}`"
                     class="grid grid-cols-[8rem_minmax(0,1fr)_4rem] md:grid-cols-[11rem_minmax(0,1fr)_4.5rem] items-center gap-3">
                    <span class="truncate text-sm text-gray-700">{{ row.name }}</span>
                    <div class="flex h-4 gap-[2px]" :style="{ width: `${Math.max(2, row.orders / maxMix * 100)}%` }">
                        <div v-if="row.first > 0" class="h-full rounded-l-[4px]" :class="row.repeat > 0 ? '' : 'rounded-r-[4px]'" :style="{ width: pct(row.first, row.orders), background: ORDER_TYPE_COLORS.first }" />
                        <div v-if="row.repeat > 0" class="h-full rounded-r-[4px]" :class="row.first > 0 ? '' : 'rounded-l-[4px]'" :style="{ width: pct(row.repeat, row.orders), background: ORDER_TYPE_COLORS.repeat }" />
                    </div>
                    <span class="text-right text-xs text-gray-700 tabular-nums">{{ decimal(row.orders) }}</span>
                </div>
            </div>
        </section>
    </div>
</template>
