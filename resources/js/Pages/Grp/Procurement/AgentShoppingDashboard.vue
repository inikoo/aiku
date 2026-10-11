<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 31 Aug 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Deferred, Head, Link, router } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import RescueOrderButton from "@/Components/Procurements/RescueOrderButton.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import {
    faExclamationTriangle,
    faWallet,
    faWarehouse,
    faStopwatch,
    faLayerGroup,
    faChevronDown,
    faMagic,
    faTrashAlt,
    faCheck,
    faSkullCrossbones,
    faSeedling,
    faBan,
} from "@fal"
import ModalAutoFillAgentShoppingList from "@/Components/Procurement/ModalAutoFillAgentShoppingList.vue"

library.add(
    faExclamationTriangle,
    faWallet,
    faWarehouse,
    faStopwatch,
    faLayerGroup,
    faChevronDown,
    faMagic,
    faTrashAlt,
    faCheck,
    faSkullCrossbones,
    faSeedling,
    faBan
)

type Tone = "red-deep" | "red" | "orange" | "amber" | "yellow" | "green" | "slate" | "violet" | "gray"
type LeadTime = { days: number, source: "measured" | "estimate" | "default", samples: number }
type RankBreakdown = { rank: string, count: number, on_list: number }
type CoverBucket = { bucket: string, label: string, tone: Tone, count: number, on_list: number, on_the_way: number, untouched: number, suppliers: number, stock_value: number, ranks: RankBreakdown[] }
type OrderCapacity = {
    agent_capacity: { lands_for_us_per_30d: number | null, source: "measured" | "sales" | "none", samples: number }
    list: { value: number, lines: number, units: number }
    warehouse: { total_locations: number, empty_locations: number, free_ratio: number | null, inbound_open_po_lines: number, agent_share_used: number, agent_share_limit: number }
    currency: string
    blocked: { at_capacity: boolean, warehouse_full: boolean }
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    orgAgent: { id: number, slug: string, name: string, currency: string }
    stats: { open_items_count: number, oldest_item_at: string | null, estimated_total: number }
    coverBuckets: CoverBucket[]
    coverTotal: number
    leadTime: LeadTime
    orderCapacity: OrderCapacity
    canPrepareOrder: boolean
    prepareOrder?: { buckets: InstanceType<typeof RescueOrderButton>["$props"]["buckets"], url: string }
}>()

const locale = useLocaleStore()

const tonePalette: Record<Tone, { text: string, soft: string, accent: string, chip: string, hex: string }> = {
    "red-deep": { text: "text-red-800", soft: "bg-red-50", accent: "bg-red-800", chip: "bg-red-100 text-red-800", hex: "#991b1b" },
    red: { text: "text-red-600", soft: "bg-red-50", accent: "bg-red-500", chip: "bg-red-100 text-red-700", hex: "#ef4444" },
    orange: { text: "text-orange-600", soft: "bg-orange-50", accent: "bg-orange-500", chip: "bg-orange-100 text-orange-700", hex: "#f97316" },
    amber: { text: "text-amber-600", soft: "bg-amber-50", accent: "bg-amber-500", chip: "bg-amber-100 text-amber-700", hex: "#f59e0b" },
    yellow: { text: "text-yellow-600", soft: "bg-yellow-50", accent: "bg-yellow-400", chip: "bg-yellow-100 text-yellow-700", hex: "#facc15" },
    green: { text: "text-green-700", soft: "bg-green-50", accent: "bg-green-500", chip: "bg-green-100 text-green-700", hex: "#22c55e" },
    slate: { text: "text-slate-500", soft: "bg-slate-100", accent: "bg-slate-400", chip: "bg-slate-100 text-slate-600", hex: "#94a3b8" },
    violet: { text: "text-violet-700", soft: "bg-violet-50", accent: "bg-violet-400", chip: "bg-violet-100 text-violet-700", hex: "#a78bfa" },
    gray: { text: "text-gray-600", soft: "bg-gray-100", accent: "bg-gray-300", chip: "bg-gray-100 text-gray-600", hex: "#d1d5db" },
}

const STOCK_COVER_STORAGE_KEY = "agentShopping.stockCoverOpen"

const readStockCoverOpen = () => {
    try {
        return localStorage.getItem(STOCK_COVER_STORAGE_KEY) !== "0"
    } catch {
        return true
    }
}

const stockCoverOpen = ref(readStockCoverOpen())
const toggleStockCover = () => {
    stockCoverOpen.value = !stockCoverOpen.value
    try {
        localStorage.setItem(STOCK_COVER_STORAGE_KEY, stockCoverOpen.value ? "1" : "0")
    } catch {
        return
    }
}

const passiveBucketKeys = ["ok", "dead", "gone", "never"]
const passiveBucketIcons: Record<string, typeof faCheck> = { ok: faCheck, dead: faSkullCrossbones, gone: faBan, never: faSeedling }
const rankClasses: Record<string, string> = { A: "", B: "", C: "", D: "opacity-50", Z: "opacity-50" }

const isPassiveBucket = (bucket: CoverBucket) => passiveBucketKeys.includes(bucket.bucket)
const orderingBuckets = computed(() => props.coverBuckets.filter((bucket) => !isPassiveBucket(bucket)))
const passiveBuckets = computed(() => props.coverBuckets.filter(isPassiveBucket))
const orderingCount = computed(() => orderingBuckets.value.reduce((sum, bucket) => sum + bucket.count, 0))

const coverPercentLabel = (bucket: CoverBucket) => {
    if (!props.coverTotal || !bucket.count) {
        return "0%"
    }
    const percent = (bucket.count / props.coverTotal) * 100
    return percent < 1 ? "<1%" : `${Math.round(percent)}%`
}

const DONUT_CENTER = 90
const DONUT_OUTER_RADIUS = 86
const DONUT_INNER_RADIUS = 56

const donutPoint = (radius: number, angle: number) => `${DONUT_CENTER + radius * Math.cos(angle)} ${DONUT_CENTER + radius * Math.sin(angle)}`

const donutArcPath = (start: number, end: number) => {
    const cappedEnd = Math.min(end, start + Math.PI * 2 - 0.001)
    const largeArc = cappedEnd - start > Math.PI ? 1 : 0
    return [
        `M ${donutPoint(DONUT_OUTER_RADIUS, start)}`,
        `A ${DONUT_OUTER_RADIUS} ${DONUT_OUTER_RADIUS} 0 ${largeArc} 1 ${donutPoint(DONUT_OUTER_RADIUS, cappedEnd)}`,
        `L ${donutPoint(DONUT_INNER_RADIUS, cappedEnd)}`,
        `A ${DONUT_INNER_RADIUS} ${DONUT_INNER_RADIUS} 0 ${largeArc} 0 ${donutPoint(DONUT_INNER_RADIUS, start)}`,
        "Z",
    ].join(" ")
}

const donutSegments = computed(() => {
    if (!props.coverTotal) {
        return []
    }
    let angle = -Math.PI / 2
    return props.coverBuckets
        .filter((bucket) => bucket.count > 0)
        .map((bucket) => {
            const sweep = (bucket.count / props.coverTotal) * Math.PI * 2
            const segment = { bucket, path: donutArcPath(angle, angle + sweep) }
            angle += sweep
            return segment
        })
})

const segmentWidth = (bucket: CoverBucket, part: number) => (bucket.count ? `${Math.max((part / bucket.count) * 100, part ? 1.5 : 0)}%` : "0%")
const shouldNotBeOrdered = (bucket: CoverBucket) => ["ok", "dead", "gone"].includes(bucket.bucket) && bucket.on_list > 0
const needsAction = (bucket: CoverBucket) => !isPassiveBucket(bucket) && bucket.untouched > 0
const canAutoFill = (bucket: CoverBucket) => !isPassiveBucket(bucket) && bucket.ranks.some((rank) => rank.count > rank.on_list)
const visibleRanks = (bucket: CoverBucket) => bucket.ranks.filter((rank) => rank.count > 0)

const capacityShare = computed(() => {
    const cap = props.orderCapacity.agent_capacity.lands_for_us_per_30d
    return cap ? Math.min(props.orderCapacity.list.value / cap, 1) : null
})

const meterTone = (share: number) => (share >= 1 ? "bg-red-500" : share >= 0.8 ? "bg-amber-500" : "bg-green-500")

const usedLocations = computed(() => props.orderCapacity.warehouse.total_locations - props.orderCapacity.warehouse.empty_locations)
const warehouseSegment = (part: number) => `${(part / Math.max(props.orderCapacity.warehouse.total_locations, 1)) * 100}%`
const warehouseLegend = computed(() => [
    { label: ctrans("in use"), value: usedLocations.value, dot: "bg-gray-400" },
    { label: ctrans("inbound PO/SD lines"), value: props.orderCapacity.warehouse.inbound_open_po_lines, dot: "bg-indigo-400" },
    { label: ctrans("on your draft orders"), value: props.orderCapacity.list.lines, dot: "bg-violet-400" },
])

const bucketItemsRoute = (bucket: string, rank?: string) =>
    `${route("grp.org.procurement.org_agents.show.shopping.items.index", [route().params.organisation, props.orgAgent.slug])}?cover=${bucket}${rank ? `&rank=${rank}` : ""}`

const dashboardReload = { only: ["coverBuckets", "coverTotal", "orderCapacity", "stats"], preserveScroll: true }

const removeMisplaced = (bucket: string) => {
    router.delete(
        route("grp.org.procurement.org_agents.show.shopping.misplaced.destroy", [route().params.organisation, props.orgAgent.slug]),
        { data: { bucket }, ...dashboardReload }
    )
}

const autoFillOpen = ref(false)
const autoFillScope = ref<{ bucket: string, rank: string | null, supplierId: number | null, label: string } | null>(null)

const openAutoFill = (bucket: CoverBucket) => {
    autoFillScope.value = { bucket: bucket.bucket, rank: null, supplierId: null, label: bucket.label }
    autoFillOpen.value = true
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-col gap-6 pb-8">
        <Deferred v-if="canPrepareOrder" data="prepareOrder">
            <template #fallback>
                <div class="h-48 animate-pulse rounded-lg border border-gray-200 bg-gray-100" role="status" :aria-label="ctrans('Working out what to order')" />
            </template>
            <RescueOrderButton
                v-if="prepareOrder"
                inline
                isAgent
                :partnerName="orgAgent.name"
                :currencyCode="orgAgent.currency"
                :buckets="prepareOrder.buckets"
                :orderUrl="prepareOrder.url" />
        </Deferred>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[--app-accent-soft] text-[--app-accent]">
                            <FontAwesomeIcon :icon="faWallet" fixed-width aria-hidden="true" />
                        </span>
                        <span class="text-sm font-medium text-gray-600">{{ ctrans("Order budget used") }}</span>
                    </div>
                    <span v-if="orderCapacity.blocked.at_capacity" class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                        {{ ctrans("at capacity") }}
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap items-baseline gap-x-1.5">
                    <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ locale.currencyFormat(orgAgent.currency, stats.estimated_total) }}</span>
                    <span v-if="orderCapacity.agent_capacity.lands_for_us_per_30d" class="text-sm tabular-nums text-gray-400">
                        / {{ locale.currencyFormat(orgAgent.currency, orderCapacity.agent_capacity.lands_for_us_per_30d) }}
                    </span>
                </div>
                <template v-if="capacityShare !== null">
                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-2 grow overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full transition-all" :class="meterTone(capacityShare)" :style="{ width: `${capacityShare * 100}%` }" />
                        </div>
                        <span class="w-10 text-right text-xs font-medium tabular-nums text-gray-500">{{ Math.round(capacityShare * 100) }}%</span>
                    </div>
                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        <template v-if="orderCapacity.agent_capacity.source === 'measured'">
                            {{ ctrans("one order cycle of what they historically land for us, measured from :n deliveries", { n: orderCapacity.agent_capacity.samples }) }}
                        </template>
                        <template v-else>
                            {{ ctrans("budget = one order cycle (:days days) of what we actually sell of their products", { days: leadTime.days + 7 }) }}
                        </template>
                    </p>
                </template>
                <p v-else class="mt-3 text-xs leading-5 text-gray-500">
                    {{ ctrans("No budget: no delivery history and no forecast data yet — the list is uncapped.") }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    {{ ctrans("all sub-supplier currencies converted to :currency", { currency: orgAgent.currency }) }}
                </p>
            </div>

            <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[--app-accent-soft] text-[--app-accent]">
                            <FontAwesomeIcon :icon="faWarehouse" fixed-width aria-hidden="true" />
                        </span>
                        <span class="text-sm font-medium text-gray-600">{{ ctrans("Warehouse space") }}</span>
                    </div>
                    <span v-if="orderCapacity.blocked.warehouse_full" class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                        {{ ctrans("full") }}
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap items-baseline gap-x-1.5">
                    <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ locale.number(orderCapacity.warehouse.empty_locations) }}</span>
                    <span class="text-sm tabular-nums text-gray-400">/ {{ locale.number(orderCapacity.warehouse.total_locations) }} {{ ctrans("locations free") }}</span>
                </div>
                <div class="mt-3 flex h-2 w-full gap-px overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full bg-gray-400" :style="{ width: warehouseSegment(usedLocations) }" />
                    <div class="h-full bg-indigo-400" :style="{ width: warehouseSegment(orderCapacity.warehouse.inbound_open_po_lines) }" />
                    <div class="h-full bg-violet-400" :style="{ width: warehouseSegment(orderCapacity.list.lines) }" />
                </div>
                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs tabular-nums text-gray-500">
                    <span v-for="legend in warehouseLegend" :key="legend.label" class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full" :class="legend.dot" />
                        <b class="font-medium text-gray-700">{{ locale.number(legend.value) }}</b> {{ legend.label }}
                    </span>
                </div>
                <p class="mt-2 text-xs leading-5 text-gray-500">
                    {{ ctrans("new products from this agent: :used of :limit free slots (their fair share)", { used: orderCapacity.warehouse.agent_share_used, limit: orderCapacity.warehouse.agent_share_limit }) }}
                </p>
            </div>

            <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[--app-accent-soft] text-[--app-accent]">
                            <FontAwesomeIcon :icon="faStopwatch" fixed-width aria-hidden="true" />
                        </span>
                        <span class="truncate text-sm font-medium text-gray-600">{{ ctrans("Lead time") }} · {{ orgAgent.name }}</span>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-baseline gap-x-1.5">
                    <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ leadTime.days }}</span>
                    <span class="text-sm text-gray-400">{{ ctrans("days order → booked in") }}</span>
                </div>
                <p class="mt-3 text-xs leading-5 text-gray-500">
                    <template v-if="leadTime.source === 'measured'">
                        {{ ctrans("measured from :samples deliveries", { samples: leadTime.samples }) }}
                    </template>
                    <template v-else>
                        {{ ctrans("estimate — set per product in supplier settings") }}
                    </template>
                </p>
                <p class="mt-1 text-xs text-gray-400">{{ ctrans(":total products in their catalogue", { total: locale.number(coverTotal) }) }}</p>
            </div>
        </section>

        <section class="rounded-r border-l-4 border-[--app-accent] bg-gray-50">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3">
                <div>
                    <button type="button" class="flex items-center gap-2 text-sm font-semibold text-gray-800" :aria-expanded="stockCoverOpen" @click="toggleStockCover">
                        <FontAwesomeIcon :icon="faLayerGroup" class="text-gray-400" fixed-width aria-hidden="true" />
                        {{ ctrans("Stock cover") }}
                        <FontAwesomeIcon :icon="faChevronDown" class="text-[10px] text-gray-400 transition-transform" :class="{ '-rotate-90': !stockCoverOpen }" fixed-width aria-hidden="true" />
                    </button>
                    <p class="mt-0.5 text-xs text-gray-500">
                        {{ ctrans(":total products in their catalogue, grouped by how long our stock lasts against a :days day lead time", { total: locale.number(coverTotal), days: leadTime.days }) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 px-4 py-3">
                <div v-if="donutSegments.length" v-show="stockCoverOpen" class="flex flex-col items-center gap-x-6 gap-y-3 sm:flex-row">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 180 180" class="h-full w-full" role="img" :aria-label="ctrans('Stock cover distribution')">
                            <path
                                v-for="segment in donutSegments"
                                :key="segment.bucket.bucket"
                                v-tooltip="`${segment.bucket.label}: ${locale.number(segment.bucket.count)} (${coverPercentLabel(segment.bucket)})`"
                                :d="segment.path"
                                :fill="tonePalette[segment.bucket.tone].hex"
                                stroke="white"
                                stroke-width="2"
                                class="cursor-pointer transition-opacity hover:opacity-75"
                                @click="router.visit(bucketItemsRoute(segment.bucket.bucket))"
                            />
                        </svg>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-base font-semibold leading-none tabular-nums text-gray-900">{{ locale.number(coverTotal) }}</span>
                            <span class="mt-0.5 text-[10px] text-gray-500">{{ ctrans("products") }}</span>
                        </div>
                    </div>
                    <div class="grid w-full min-w-0 grow grid-cols-1 gap-x-6 gap-y-0 sm:grid-cols-2 xl:grid-cols-4">
                        <Link
                            v-for="bucket in coverBuckets"
                            :key="bucket.bucket"
                            :href="bucketItemsRoute(bucket.bucket)"
                            class="group flex items-center gap-2 rounded-md px-2 py-0.5 text-xs hover:bg-gray-50"
                        >
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm" :class="tonePalette[bucket.tone].accent" />
                            <span class="min-w-0 grow truncate font-medium text-gray-700 group-hover:underline">{{ bucket.label }}</span>
                            <span class="shrink-0 font-semibold tabular-nums text-gray-900">{{ locale.number(bucket.count) }}</span>
                            <span class="w-9 shrink-0 text-right tabular-nums text-gray-400">{{ coverPercentLabel(bucket) }}</span>
                        </Link>
                    </div>
                </div>

                <div v-show="stockCoverOpen" class="border-t border-dashed border-gray-300" />

                <div class="">
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        {{ ctrans("Needs ordering") }}
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 normal-case tracking-normal tabular-nums text-gray-600">{{ locale.number(orderingCount) }}</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                        <div
                            v-for="bucket in orderingBuckets"
                            :key="bucket.bucket"
                            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:border-gray-300 hover:shadow-sm"
                        >
                            <div class="h-1 w-full" :class="tonePalette[bucket.tone].accent" />
                            <div class="flex grow flex-col gap-2.5 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <Link
                                            :href="bucketItemsRoute(bucket.bucket)"
                                            class="text-3xl font-semibold leading-none tabular-nums hover:underline"
                                            :class="bucket.count ? tonePalette[bucket.tone].text : 'text-gray-300'"
                                        >
                                            {{ locale.number(bucket.count) }}
                                        </Link>
                                        <Link :href="bucketItemsRoute(bucket.bucket)" class="mt-1.5 block text-xs font-medium leading-4 text-gray-700 hover:underline">
                                            {{ bucket.label }}
                                        </Link>
                                        <div v-if="bucket.suppliers" class="mt-0.5 text-[10px] tabular-nums text-gray-400">
                                            {{ ctrans("across :n suppliers", { n: bucket.suppliers }) }}
                                        </div>
                                    </div>
                                    <span v-if="needsAction(bucket)" class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums" :class="tonePalette[bucket.tone].chip">
                                        {{ ctrans(":count need action", { count: locale.number(bucket.untouched) }) }}
                                    </span>
                                    <span v-else-if="bucket.count" class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold text-green-700">
                                        {{ ctrans("all handled") }}
                                    </span>
                                </div>

                                <template v-if="bucket.on_the_way || bucket.on_list">
                                    <div class="flex h-1.5 w-full gap-px overflow-hidden rounded-full bg-gray-100">
                                        <div v-if="bucket.on_the_way" class="h-full" :class="tonePalette[bucket.tone].accent" :style="{ width: segmentWidth(bucket, bucket.on_the_way) }" />
                                        <div v-if="bucket.on_list" class="h-full opacity-40" :class="tonePalette[bucket.tone].accent" :style="{ width: segmentWidth(bucket, bucket.on_list) }" />
                                    </div>
                                    <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] tabular-nums text-gray-500">
                                        <span v-if="bucket.on_the_way" class="flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="tonePalette[bucket.tone].accent" />
                                            {{ bucket.on_the_way }} {{ ctrans("on order") }}
                                        </span>
                                        <span v-if="bucket.on_list" class="flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full opacity-40" :class="tonePalette[bucket.tone].accent" />
                                            {{ bucket.on_list }} {{ ctrans("on list") }}
                                        </span>
                                    </div>
                                </template>

                                <div class="mt-auto flex flex-wrap items-center gap-1.5 pt-1">
                                    <Link
                                        v-for="rank in visibleRanks(bucket)"
                                        :key="rank.rank"
                                        :href="bucketItemsRoute(bucket.bucket, rank.rank)"
                                        v-tooltip="ctrans(':count ranked :rank, :on_list already on the list', { count: rank.count, rank: rank.rank, on_list: rank.on_list })"
                                        class="rounded-md border border-gray-200 px-1.5 py-0.5 text-[11px] tabular-nums text-gray-600 hover:border-gray-300 hover:bg-gray-50"
                                        :class="rankClasses[rank.rank]"
                                    >
                                        <b class="font-bold text-gray-800">{{ rank.rank }}</b> {{ rank.count }}<span v-if="rank.on_list" class="text-gray-400">/{{ rank.on_list }}</span>
                                    </Link>
                                    <button
                                        v-if="canAutoFill(bucket)"
                                        type="button"
                                        v-tooltip="ctrans('Add to the draft orders from this bucket')"
                                        class="ml-auto inline-flex items-center gap-1 rounded-md bg-[--app-accent] px-2 py-0.5 text-[11px] font-medium text-[--app-accent-text] hover:bg-[--app-accent-strong]"
                                        @click="openAutoFill(bucket)"
                                    >
                                        <FontAwesomeIcon :icon="faMagic" fixed-width aria-hidden="true" />
                                        {{ ctrans("Fill") }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ctrans("Not for ordering") }}</div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            v-for="bucket in passiveBuckets"
                            :key="bucket.bucket"
                            class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5 transition hover:border-gray-300 hover:shadow-sm"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" :class="[tonePalette[bucket.tone].soft, tonePalette[bucket.tone].text]">
                                <FontAwesomeIcon :icon="passiveBucketIcons[bucket.bucket]" fixed-width aria-hidden="true" />
                            </span>
                            <div class="min-w-0 grow">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <Link :href="bucketItemsRoute(bucket.bucket)" class="text-xl font-semibold leading-none tabular-nums hover:underline" :class="tonePalette[bucket.tone].text">
                                        {{ locale.number(bucket.count) }}
                                    </Link>
                                    <Link :href="bucketItemsRoute(bucket.bucket)" class="text-xs font-medium text-gray-700 hover:underline">{{ bucket.label }}</Link>
                                    <span v-if="bucket.bucket === 'dead'" class="text-xs tabular-nums text-gray-400">
                                        {{ locale.currencyFormat(orgAgent.currency, bucket.stock_value) }}
                                    </span>
                                </div>
                                <div v-if="visibleRanks(bucket).length || shouldNotBeOrdered(bucket)" class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] tabular-nums text-gray-500">
                                    <Link
                                        v-for="rank in visibleRanks(bucket)"
                                        :key="rank.rank"
                                        :href="bucketItemsRoute(bucket.bucket, rank.rank)"
                                        class="hover:underline"
                                        :class="rankClasses[rank.rank]"
                                    >
                                        <b class="font-bold text-gray-700">{{ rank.rank }}</b> {{ rank.count }}
                                    </Link>
                                    <span
                                        v-if="shouldNotBeOrdered(bucket)"
                                        v-tooltip="ctrans('On the shopping list but not short of stock')"
                                        class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 font-medium text-red-700"
                                    >
                                        <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
                                        {{ bucket.on_list }} {{ ctrans("on list") }}
                                        <button v-if="bucket.bucket !== 'gone'" type="button" class="ml-0.5 inline-flex items-center gap-1 rounded border border-red-300 px-1 text-[10px] hover:bg-red-100" @click="removeMisplaced(bucket.bucket)">
                                            <FontAwesomeIcon :icon="faTrashAlt" fixed-width aria-hidden="true" />
                                            {{ ctrans("remove") }}
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <ModalAutoFillAgentShoppingList
        v-model="autoFillOpen"
        :orgAgentSlug="orgAgent.slug"
        :currency="orgAgent.currency"
        :bucket="autoFillScope?.bucket"
        :rank="autoFillScope?.rank"
        :supplierId="autoFillScope?.supplierId"
        :scopeLabel="autoFillScope?.label"
    />
</template>
