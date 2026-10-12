<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 31 Aug 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { useFormatTime } from "@/Composables/useFormatTime"
import { computed, ref, watch } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Image from "@common/Components/Image.vue"
import { Checkbox, IconField, InputIcon, InputText, Select } from "primevue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import NumberWithButtonSave from "@/Components/NumberWithButtonSave.vue"
import PurchaseOrderItemStockInfo from "@/Components/Procurement/PurchaseOrderItemStockInfo.vue"
import PurchaseOrderSuggestButton from "@/Components/Procurement/PurchaseOrderSuggestButton.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"
import { notify } from "@kyvg/vue3-notification"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faThLarge, faList, faSearch } from "@fal"
import { faCheckCircle } from "@fas"

type BucketItem = {
    id: number
    slug: string
    code: string
    name: string
    image: object | null
    bucket: string | null
    supplier_code: string | null
    lead_time_days: number | null
    lead_time_measured: boolean
    our_stock: number | null
    our_days_of_cover: number | null
    recommended_quantity: number | null
    units_per_carton: number | null
    minimum_carton_order: number | null
    cost: number | null
    currency: string | null
    ordered_quantity: number
    units_per_pack: number
    org_exchange: number | null
    has_org_stock: boolean
    stock_in_locations: string | null
    other_organisations_drafts: { organisation_code: string, organisation_name: string, units: number }[]
    stock_deliveries?: { coming?: { slug: string, reference: string, state_label: string, quantity: number }[], recent_received?: { slug: string, reference: string, received_at: string, quantity: number }[] } | null
    other_open_purchase_orders?: { slug: string | null, reference: string, stage?: string | null, state: string, quantity_ordered: number }[]
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    orgAgent: { id: number, slug: string, currency: string }
    addRoute: { name: string, parameters: (string | number)[] }
    bucket: string | null
    bucketLabel: string
    bucketLabels: Record<string, { label: string, tone: string }>
    filterOptions: {
        cover: { value: string, label: string, count: number }[]
        rank: { value: string, label: string, count: number }[]
        supplier: { value: number, label: string }[]
    }
    active: { cover: string | null, rank: string | null, supplier: number | null, drafts: boolean, hide_ordered: boolean, search: string }
    rank: string | null
    items: { data: BucketItem[], links: { prev: string | null, next: string | null }, meta: { total: number, from: number | null, to: number | null } }
    miniCart: { count: number, total: number, currency: string, listRoute: { name: string, parameters: (string | number)[] } }
}>()

type ItemsView = "cards" | "list"

const VIEW_STORAGE_KEY = "agentShopping.itemsView"

const readStoredView = (): ItemsView => {
    try {
        return localStorage.getItem(VIEW_STORAGE_KEY) === "list" ? "list" : "cards"
    } catch {
        return "cards"
    }
}

const view = ref<ItemsView>(readStoredView())
const viewOptions = [
    { label: ctrans("Cards"), value: "cards" as ItemsView, icon: faThLarge },
    { label: ctrans("List"), value: "list" as ItemsView, icon: faList },
]

watch(view, (value) => {
    try {
        localStorage.setItem(VIEW_STORAGE_KEY, value)
    } catch {
        return
    }
})

const bucketChip: Record<string, string> = {
    "red-deep": "bg-red-100 text-red-800",
    red: "bg-red-100 text-red-700",
    orange: "bg-orange-100 text-orange-700",
    amber: "bg-amber-100 text-amber-700",
    yellow: "bg-yellow-100 text-yellow-700",
    green: "bg-green-100 text-green-700",
    slate: "bg-slate-100 text-slate-600",
    violet: "bg-violet-100 text-violet-700",
    gray: "bg-gray-100 text-gray-600",
}

const searchTerm = ref(props.active.search)
let searchTimer: ReturnType<typeof setTimeout> | null = null

type ActiveFilters = { cover: string | null, rank: string | null, supplier: number | null, drafts: boolean, hide_ordered: boolean, search: string }

const visit = (change: Partial<ActiveFilters>) => {
    const next = { ...props.active, search: searchTerm.value.trim(), ...change }
    const current = new URLSearchParams(window.location.search)
    const organisation = route().params["organisation"]

    router.get(
        next.cover
            ? route("grp.org.procurement.org_agents.show.shopping.items.index", [organisation, props.orgAgent.slug])
            : route("grp.org.procurement.org_agents.show.shopping.browse.index", [organisation, props.orgAgent.slug]),
        {
            ...(next.cover ? { cover: next.cover } : {}),
            ...(next.rank ? { rank: next.rank } : {}),
            ...(next.drafts ? { drafts: 1 } : {}),
            ...(next.hide_ordered ? { hide_ordered: 1 } : {}),
            ...(next.supplier ? { "filter[supplier]": next.supplier } : {}),
            ...(next.search ? { "filter[global]": next.search } : {}),
            ...(current.get("sort") ? { sort: current.get("sort") } : {}),
            ...(current.get("perPage") ? { perPage: current.get("perPage") } : {}),
        },
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

watch(searchTerm, () => {
    if (searchTimer) {
        clearTimeout(searchTimer)
    }
    searchTimer = setTimeout(() => visit({}), 350)
})


const locale = useLocaleStore()

const cartons = ref<Record<number, number>>({})
const commitTimers: Record<number, ReturnType<typeof setTimeout>> = {}

const unitsPerCarton = (item: BucketItem) => Math.max(1, item.units_per_carton ?? 1)

const orderedCartons = (item: BucketItem) => Math.round((item.ordered_quantity ?? 0) / unitsPerCarton(item))

function cartonsFor(item: BucketItem): number {
    return cartons.value[item.id] ?? orderedCartons(item)
}


function setCartons(item: BucketItem, quantity: number) {
    cartons.value[item.id] = Math.max(0, quantity)
    if (commitTimers[item.id]) {
        clearTimeout(commitTimers[item.id])
    }
    commitTimers[item.id] = setTimeout(() => commit(item), 600)
}

function commit(item: BucketItem) {
    const quantity = cartonsFor(item)
    const quantityUnits = quantity * unitsPerCarton(item)
    if (quantityUnits === (item.ordered_quantity ?? 0)) {
        return
    }

    router.post(
        route(props.addRoute.name, [...props.addRoute.parameters, item.id]),
        { quantity_units: quantityUnits },
        {
            preserveScroll: true,
            preserveState: true,
            only: ["items", "miniCart", "filterOptions"],
            onSuccess: () => {
                if (cartons.value[item.id] === quantity) {
                    delete cartons.value[item.id]
                }
            },
            onError: (errors) => {
                delete cartons.value[item.id]
                notify({
                    title: ctrans("Not saved"),
                    text: Object.values(errors)[0] ?? ctrans("The quantity could not be saved, it is back to what is on the draft"),
                    type: "error",
                })
            },
        }
    )
}

const isPartCarton = (item: BucketItem) => (item.ordered_quantity ?? 0) % unitsPerCarton(item) !== 0

const packOf = (item: BucketItem) => Number(item.units_per_pack) || 1

const typedSkos = computed<Record<number, number>>(() =>
    Object.fromEntries(props.items.data.map((item) => [item.id, (cartonsFor(item) * unitsPerCarton(item)) / packOf(item)]))
)

const otherCartons = (item: BucketItem) => (item.other_organisations_drafts ?? []).reduce((units, draft) => units + draft.units, 0) / unitsPerCarton(item)

const suggestedCartons = (item: BucketItem, skos: number) =>
    Math.max(Math.ceil((item.minimum_carton_order ?? 1) - otherCartons(item)), Math.ceil((skos * packOf(item)) / unitsPerCarton(item)))

const suggestionLabel = (item: BucketItem, skos: number) =>
    ctransChoice(":number carton|:number cartons", suggestedCartons(item, skos), { number: locale.number(suggestedCartons(item, skos)) })

const applySuggestion = (item: BucketItem, skos: number) => {
    setCartons(item, suggestedCartons(item, skos))
}

const moqLabel = (item: BucketItem) =>
    `${ctrans("MOQ")} ${item.minimum_carton_order ? ctransChoice(":number carton|:number cartons", item.minimum_carton_order, { number: locale.number(item.minimum_carton_order) }) : `(${ctrans("not set")})`}`

const draftCartonsLabel = (item: BucketItem, units: number) => {
    const cartons = Math.round((units / unitsPerCarton(item)) * 100) / 100
    return ctransChoice(":number carton|:number cartons", cartons, { number: locale.number(cartons) })
}

const groupCartons = (item: BucketItem) => cartonsFor(item) + otherCartons(item)

const isMoqMet = (item: BucketItem) => !item.minimum_carton_order || groupCartons(item) >= item.minimum_carton_order

const moqTooltip = (item: BucketItem) =>
    item.minimum_carton_order
        ? ctrans("The supplier's minimum of :moq is met: :ordered on drafts, every organisation counted", {
            moq: ctransChoice(":number carton|:number cartons", item.minimum_carton_order, { number: locale.number(item.minimum_carton_order) }),
            ordered: ctransChoice(":number carton|:number cartons", groupCartons(item), { number: locale.number(Math.round(groupCartons(item) * 100) / 100) }),
        })
        : ctrans("This product has no minimum order")

type OnTheWayRow = { key: string, label: string, tone: string, reference: string, tooltip: string, href: string | null, units: number }

const onTheWayRows = (item: BucketItem): OnTheWayRow[] => [
    ...(item.other_organisations_drafts ?? []).map((draft) => ({
        key: `draft-${draft.organisation_code}`,
        label: ctrans("Draft"),
        tone: "text-gray-500",
        reference: draft.organisation_code,
        tooltip: draft.organisation_name,
        href: null,
        units: draft.units,
    })),
    ...(item.stock_deliveries?.coming ?? []).map((delivery) => ({
        key: `delivery-${delivery.slug}`,
        label: delivery.state_label,
        tone: "text-emerald-700",
        reference: delivery.reference,
        tooltip: delivery.reference,
        href: stockDeliveryRoute(delivery.slug),
        units: Number(delivery.quantity) || 0,
    })),
    ...(item.other_open_purchase_orders ?? []).map((order) => ({
        key: `order-${order.slug ?? order.reference}`,
        label: order.stage ?? (order.state === "sent" ? ctrans("Requested") : ctrans("Ordered")),
        tone: "text-amber-700",
        reference: order.reference,
        tooltip: order.reference,
        href: order.slug ? route("grp.org.procurement.purchase_orders.show", [route().params.organisation, order.slug]) : null,
        units: Number(order.quantity_ordered) || 0,
    })),
]

const pastDeliveriesShown = ref<Record<number, boolean>>({})

const recentDeliveries = (item: BucketItem) => item.stock_deliveries?.recent_received ?? []

const stockDeliveryRoute = (slug: string) => route("grp.org.procurement.stock_deliveries.show", [route().params.organisation, slug])

const amountOf = (item: BucketItem) => cartonsFor(item) * unitsPerCarton(item) * Number(item.cost ?? 0)

const orgAmountOf = (item: BucketItem) =>
    item.org_exchange && item.currency && item.currency !== props.orgAgent.currency ? amountOf(item) * item.org_exchange : null

const quantityBreakdown = (item: BucketItem) => {
    const units = cartonsFor(item) * unitsPerCarton(item)
    const skos = units / packOf(item)
    const parts = [
        ctransChoice(":number carton|:number cartons", cartonsFor(item), { number: locale.number(cartonsFor(item)) }),
        ctransChoice(":number SKO|:number SKOs", skos, { number: locale.number(Math.round(skos * 100) / 100) }),
    ]
    if (packOf(item) !== 1) {
        parts.push(ctransChoice(":number unit|:number units", units, { number: locale.number(units) }))
    }
    return parts.join(" · ")
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-5 flex items-start gap-6">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <PillFilterBar :label="ctrans('Stock group')" :options="filterOptions.cover" :selected="active.cover" @select="(value) => visit({ cover: value })" />
                <PillFilterBar :label="ctrans('Rank')" :options="filterOptions.rank" :selected="active.rank" @select="(value) => visit({ rank: value })" />
            </div>

            <div class="mb-3 mt-2 flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ ctrans("Supplier") }}</span>
                    <Select
                        :modelValue="active.supplier"
                        :options="filterOptions.supplier"
                        optionLabel="label"
                        optionValue="value"
                        filter
                        showClear
                        size="small"
                        :placeholder="ctrans('All')"
                        class="filter-select min-w-[12rem] max-w-xs"
                        @update:modelValue="(value: number | null) => visit({ supplier: value })"
                    />
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1 text-xs" :class="active.drafts ? 'border-gray-400 bg-gray-100 text-gray-800' : 'border-gray-200 bg-white text-gray-600'">
                    <Checkbox :modelValue="active.drafts" binary size="small" @update:modelValue="(value: boolean) => visit({ drafts: value })" />
                    {{ ctrans("On my drafts only") }}
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1 text-xs" :class="active.hide_ordered ? 'border-gray-400 bg-gray-100 text-gray-800' : 'border-gray-200 bg-white text-gray-600'">
                    <Checkbox :modelValue="active.hide_ordered" binary size="small" @update:modelValue="(value: boolean) => visit({ hide_ordered: value })" />
                    {{ ctrans("Hide what is already on order") }}
                </label>
                <IconField class="min-w-[14rem] flex-1 sm:max-w-xs">
                    <InputIcon>
                        <FontAwesomeIcon :icon="faSearch" fixed-width aria-hidden="true" />
                    </InputIcon>
                    <InputText v-model="searchTerm" size="small" class="filter-search w-full" :placeholder="ctrans('Search by code or name')" :aria-label="ctrans('Search products')" />
                </IconField>

                <span class="ml-auto text-xs tabular-nums text-gray-500">
                    <b class="font-semibold text-gray-800">{{ locale.number(items.meta.total) }}</b> {{ ctrans("products") }}
                </span>
                <Link
                    :href="route(miniCart.listRoute.name, miniCart.listRoute.parameters)"
                    class="text-xs tabular-nums text-gray-600 underline decoration-dotted underline-offset-2 hover:decoration-solid"
                >
                    {{ ctrans("Draft orders: :count lines", { count: locale.number(miniCart.count) }) }}
                    <template v-if="miniCart.total"> · {{ locale.currencyFormat(miniCart.currency, miniCart.total) }}</template>
                </Link>
                <SegmentedToggle v-model="view" :options="viewOptions" :ariaLabel="ctrans('Show products as cards or as a list')" />
            </div>

            <div v-if="view === 'cards'" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                <div v-for="item in items.data" :key="item.id" class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="aspect-square w-full bg-gray-50">
                        <Image v-if="item.image" :src="item.image" imageCover />
                    </div>
                    <div class="flex flex-1 flex-col gap-1 p-2">
                        <p class="truncate text-sm font-medium text-gray-800" :title="item.name">{{ item.name }}</p>
                        <p class="flex items-center justify-between gap-2 text-xs text-gray-400">
                            <span class="truncate">
                                {{ item.code }}
                                <template v-if="item.supplier_code"> · {{ item.supplier_code }}</template>
                            </span>
                            <span
                                v-if="bucket === null && item.bucket && bucketLabels[item.bucket]"
                                v-tooltip="bucketLabels[item.bucket].label"
                                class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-medium"
                                :class="bucketChip[bucketLabels[item.bucket].tone] ?? 'bg-gray-100 text-gray-600'"
                            >{{ bucketLabels[item.bucket].label.split(":")[0] }}</span>
                        </p>
                        <div class="space-y-0.5">
                            <div class="text-xs text-gray-500">
                                {{ ctrans("Unit cost") }}: <span class="tabular-nums text-gray-700">{{ item.cost !== null ? locale.currencyFormat(item.currency ?? orgAgent.currency, item.cost) : "-" }}</span>
                                <template v-if="item.units_per_carton"> · {{ ctrans(":count per carton", { count: locale.number(item.units_per_carton) }) }}</template>
                            </div>
                            <div v-if="item.lead_time_days" class="text-xs text-gray-500">
                                {{ ctrans("Lead time") }}: <span class="tabular-nums" :class="item.lead_time_measured ? 'text-gray-700' : 'text-gray-400'">{{ item.lead_time_days }}{{ ctrans("d") }} {{ item.lead_time_measured ? ctrans("measured") : ctrans("estimate") }}</span>
                            </div>
                            <div v-if="!item.has_org_stock" class="text-xs font-medium text-violet-600">{{ ctrans("never stocked") }}</div>
                            <PurchaseOrderItemStockInfo :item="item" :typedSkosById="typedSkos" hideComing />
                        </div>

                        <div class="mt-auto flex items-end justify-between gap-3 pt-2">
                            <div class="text-xs">
                                <div v-if="cartonsFor(item)" class="space-y-0.5 tabular-nums">
                                    <div class="text-gray-500">
                                        {{ quantityBreakdown(item) }}
                                    </div>
                                    <div v-if="item.cost !== null" class="font-medium text-gray-800">
    {{ locale.currencyFormat(item.currency ?? orgAgent.currency, amountOf(item)) }}
    <span v-if="orgAmountOf(item) !== null" class="font-normal text-gray-500">({{ locale.currencyFormat(orgAgent.currency, orgAmountOf(item)!) }})</span>
</div>
                                <div v-if="isPartCarton(item) && cartons[item.id] === undefined" class="text-amber-600">{{ ctrans("The draft holds :units units, not whole cartons", { units: locale.number(item.ordered_quantity) }) }}</div>
                                </div>
<div v-if="cartonsFor(item) || item.minimum_carton_order" class="flex items-center gap-1 pt-1.5 tabular-nums" :class="cartonsFor(item) && !isMoqMet(item) ? 'text-amber-600' : 'text-gray-500'">
    <FontAwesomeIcon v-if="cartonsFor(item) && isMoqMet(item)" v-tooltip="moqTooltip(item)" :icon="faCheckCircle" class="text-green-600" aria-hidden="true" />
    <span>{{ moqLabel(item) }}</span>
</div>
                            </div>
                            <div class="flex flex-col items-end">
                                <NumberWithButtonSave
                                    :modelValue="cartonsFor(item)"
                                    :bindToTarget="{ min: 0 }"
                                    allowZero
                                    isWithRefreshModel
                                    noUndoButton
                                    noSaveButton
                                    @update:modelValue="(value: number) => setCartons(item, value)"
                                />
                                <PurchaseOrderSuggestButton :item="item" :typedSkosById="typedSkos" :quantityLabel="(skos: number) => suggestionLabel(item, skos)" @suggest="(skos: number) => applySuggestion(item, skos)" />
                            </div>
                        </div>
<table v-if="onTheWayRows(item).length" class="mt-2 w-full border-collapse border border-gray-200 text-left text-xs text-gray-600">
    <tr v-for="row in onTheWayRows(item)" :key="row.key" class="border-b border-gray-200 last:border-b-0">
        <td class="px-1.5 py-0.5 text-[10px] uppercase tracking-wide" :class="row.tone">{{ row.label }}</td>
        <td class="w-full px-1.5 py-0.5">
            <Link v-if="row.href" v-tooltip="row.tooltip" :href="row.href" class="block max-w-[9rem] truncate font-mono text-gray-700 hover:underline">{{ row.reference }}</Link>
            <span v-else v-tooltip="row.tooltip" class="font-medium text-gray-700">{{ row.reference }}</span>
        </td>
        <td class="whitespace-nowrap px-1.5 py-0.5 text-right tabular-nums">{{ draftCartonsLabel(item, row.units) }}</td>
    </tr>
</table>
<div v-if="recentDeliveries(item).length" class="mt-1 flex flex-col items-start text-xs text-gray-400">
    <button type="button" class="hover:text-gray-600" @click="pastDeliveriesShown[item.id] = !pastDeliveriesShown[item.id]">
        {{ ctrans("Last arrived :date", { date: useFormatTime(recentDeliveries(item)[0].received_at) }) }}
        <span class="text-[10px]">{{ pastDeliveriesShown[item.id] ? "▴" : "▾" }}</span>
    </button>
    <table v-if="pastDeliveriesShown[item.id]" class="mt-1 w-full border-collapse border border-gray-200 text-left">
        <tr v-for="delivery in recentDeliveries(item)" :key="delivery.slug" class="border-b border-gray-200 last:border-b-0">
            <td class="whitespace-nowrap px-1.5 py-0.5">{{ useFormatTime(delivery.received_at) }}</td>
            <td class="w-full px-1.5 py-0.5"><Link v-tooltip="delivery.reference" :href="stockDeliveryRoute(delivery.slug)" class="block max-w-[9rem] truncate font-mono hover:text-gray-600 hover:underline">{{ delivery.reference }}</Link></td>
            <td class="whitespace-nowrap px-1.5 py-0.5 text-right tabular-nums">{{ draftCartonsLabel(item, delivery.quantity) }}</td>
        </tr>
    </table>
</div>
                    </div>
                </div>
                <p v-if="!items.data.length" class="col-span-full py-10 text-center text-sm text-gray-400">
                    {{ ctrans("No products in this bucket") }}
                </p>
            </div>

            <Table v-else :resource="items" class="mt-0" rowAlignTop>
                <template #cell(code)="{ item }">
                    <span class="font-medium text-gray-800">{{ item.code }}</span>
                </template>
                <template #cell(image)="{ item }">
                    <div class="h-20 w-20 overflow-hidden rounded bg-gray-50">
                        <Image v-if="item.image" :src="item.image" imageCover />
                    </div>
                </template>
                <template #cell(name)="{ item }">
                    <div class="space-y-0.5">
                        <div class="text-gray-900">{{ item.name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ ctrans("Unit cost") }}: <span class="tabular-nums text-gray-700">{{ item.cost !== null ? locale.currencyFormat(item.currency ?? orgAgent.currency, item.cost) : "-" }}</span>
                            <template v-if="item.units_per_carton"> · {{ ctrans(":count per carton", { count: locale.number(item.units_per_carton) }) }}</template>
                        </div>
                        <div v-if="item.lead_time_days" class="text-xs text-gray-500">
                            {{ ctrans("Lead time") }}: <span class="tabular-nums" :class="item.lead_time_measured ? 'text-gray-700' : 'text-gray-400'">{{ item.lead_time_days }}{{ ctrans("d") }} {{ item.lead_time_measured ? ctrans("measured") : ctrans("estimate") }}</span>
                        </div>
                        <div v-if="!item.has_org_stock" class="text-xs font-medium text-violet-600">{{ ctrans("never stocked") }}</div>
                        <PurchaseOrderItemStockInfo :item="item" :typedSkosById="typedSkos" hideComing />
                    </div>
                </template>
                <template #cell(bucket)="{ item }">
                    <span
                        v-if="item.bucket && bucketLabels[item.bucket]"
                        v-tooltip="bucketLabels[item.bucket].label"
                        class="whitespace-nowrap rounded-full px-1.5 py-0.5 text-[10px] font-medium"
                        :class="bucketChip[bucketLabels[item.bucket].tone] ?? 'bg-gray-100 text-gray-600'"
                    >{{ bucketLabels[item.bucket].label.split(":")[0] }}</span>
                </template>
                <template #cell(subtotals)="{ item }">
                    <div v-if="cartonsFor(item)" class="space-y-0.5 tabular-nums">
                        <div class="text-gray-500">
                            {{ quantityBreakdown(item) }}
                        </div>
                        <div v-if="item.cost !== null" class="font-medium text-gray-800">
    {{ locale.currencyFormat(item.currency ?? orgAgent.currency, amountOf(item)) }}
    <span v-if="orgAmountOf(item) !== null" class="font-normal text-gray-500">({{ locale.currencyFormat(orgAgent.currency, orgAmountOf(item)!) }})</span>
</div>
                                <div v-if="isPartCarton(item) && cartons[item.id] === undefined" class="text-amber-600">{{ ctrans("The draft holds :units units, not whole cartons", { units: locale.number(item.ordered_quantity) }) }}</div>
                    </div>
<div v-if="cartonsFor(item) || item.minimum_carton_order" class="flex items-center gap-1 pt-1.5 tabular-nums" :class="cartonsFor(item) && !isMoqMet(item) ? 'text-amber-600' : 'text-gray-500'">
    <FontAwesomeIcon v-if="cartonsFor(item) && isMoqMet(item)" v-tooltip="moqTooltip(item)" :icon="faCheckCircle" class="text-green-600" aria-hidden="true" />
    <span>{{ moqLabel(item) }}</span>
</div>
                </template>
                <template #cell(cartons)="{ item }">
                    <div class="flex flex-col items-end">
                        <NumberWithButtonSave
                            :modelValue="cartonsFor(item)"
                            :bindToTarget="{ min: 0 }"
                            allowZero
                            isWithRefreshModel
                            noUndoButton
                            noSaveButton
                            @update:modelValue="(value: number) => setCartons(item, value)"
                        />
                        <PurchaseOrderSuggestButton :item="item" :typedSkosById="typedSkos" :quantityLabel="(skos: number) => suggestionLabel(item, skos)" @suggest="(skos: number) => applySuggestion(item, skos)" />
<table v-if="onTheWayRows(item).length" class="mt-1.5 border-collapse border border-gray-200 text-left text-xs text-gray-600">
    <tr v-for="row in onTheWayRows(item)" :key="row.key" class="border-b border-gray-200 last:border-b-0">
        <td class="px-1.5 py-0.5 text-[10px] uppercase tracking-wide" :class="row.tone">{{ row.label }}</td>
        <td class="px-1.5 py-0.5">
            <Link v-if="row.href" v-tooltip="row.tooltip" :href="row.href" class="block max-w-[9rem] truncate font-mono text-gray-700 hover:underline">{{ row.reference }}</Link>
            <span v-else v-tooltip="row.tooltip" class="font-medium text-gray-700">{{ row.reference }}</span>
        </td>
        <td class="whitespace-nowrap px-1.5 py-0.5 text-right tabular-nums">{{ draftCartonsLabel(item, row.units) }}</td>
    </tr>
</table>
<div v-if="recentDeliveries(item).length" class="mt-1 flex flex-col items-end text-xs text-gray-400">
    <button type="button" class="hover:text-gray-600" @click="pastDeliveriesShown[item.id] = !pastDeliveriesShown[item.id]">
        {{ ctrans("Last arrived :date", { date: useFormatTime(recentDeliveries(item)[0].received_at) }) }}
        <span class="text-[10px]">{{ pastDeliveriesShown[item.id] ? "▴" : "▾" }}</span>
    </button>
    <table v-if="pastDeliveriesShown[item.id]" class="mt-1 border-collapse border border-gray-200 text-left">
        <tr v-for="delivery in recentDeliveries(item)" :key="delivery.slug" class="border-b border-gray-200 last:border-b-0">
            <td class="whitespace-nowrap px-1.5 py-0.5">{{ useFormatTime(delivery.received_at) }}</td>
            <td class="px-1.5 py-0.5"><Link v-tooltip="delivery.reference" :href="stockDeliveryRoute(delivery.slug)" class="block max-w-[9rem] truncate font-mono hover:text-gray-600 hover:underline">{{ delivery.reference }}</Link></td>
            <td class="whitespace-nowrap px-1.5 py-0.5 text-right tabular-nums">{{ draftCartonsLabel(item, delivery.quantity) }}</td>
        </tr>
    </table>
</div>
                    </div>
                </template>
            </Table>

            <div v-if="view === 'cards' && (items.links.prev || items.links.next)" class="mt-3 flex justify-between text-sm">
                <Link v-if="items.links.prev" :href="items.links.prev" preserve-scroll class="secondaryLink">{{ ctrans("Previous") }}</Link>
                <span v-else />
                <Link v-if="items.links.next" :href="items.links.next" preserve-scroll class="secondaryLink">{{ ctrans("Next") }}</Link>
            </div>
        </div>

    </div>
</template>

<style scoped>
.filter-select,
.filter-search {
    font-size: 0.75rem;
}

.filter-select :deep(.p-select-label) {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
}
</style>
