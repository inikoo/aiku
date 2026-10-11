<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 31 Aug 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { ref, watch } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Image from "@common/Components/Image.vue"
import { Checkbox, IconField, InputIcon, InputText, Select } from "primevue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import NumberWithButtonSave from "@/Components/NumberWithButtonSave.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faThLarge, faList, faSearch } from "@fal"

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
    active: { cover: string | null, rank: string | null, supplier: number | null, drafts: boolean, search: string }
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

type ActiveFilters = { cover: string | null, rank: string | null, supplier: number | null, drafts: boolean, search: string }

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

const coverClass = (days: number) => ({ "font-medium text-red-600": days <= 30, "text-amber-600": days > 30 && days <= 60 })

const locale = useLocaleStore()

const cartons = ref<Record<number, number>>({})
const commitTimers: Record<number, ReturnType<typeof setTimeout>> = {}

const unitsPerCarton = (item: BucketItem) => Math.max(1, item.units_per_carton ?? 1)

const orderedCartons = (item: BucketItem) => Math.round((item.ordered_quantity ?? 0) / unitsPerCarton(item))

function cartonsFor(item: BucketItem): number {
    return cartons.value[item.id] ?? orderedCartons(item)
}

function suggestedCartons(item: BucketItem): number {
    if (!item.recommended_quantity) {
        return Math.max(1, item.minimum_carton_order ?? 1)
    }

    return Math.max(item.minimum_carton_order ?? 1, Math.ceil(item.recommended_quantity / unitsPerCarton(item)))
}

const suggestionLabel = (item: BucketItem) => (item.recommended_quantity ? ctrans("suggested") : ctrans("smallest order"))

function setCartons(item: BucketItem, quantity: number) {
    cartons.value[item.id] = Math.max(0, quantity)
    if (commitTimers[item.id]) {
        clearTimeout(commitTimers[item.id])
    }
    commitTimers[item.id] = setTimeout(() => commit(item), 600)
}

function commit(item: BucketItem) {
    const quantity = cartonsFor(item)
    if (quantity === orderedCartons(item)) {
        return
    }

    const reloadOptions = {
        preserveScroll: true,
        preserveState: true,
        only: ["items", "miniCart"],
        onSuccess: () => { delete cartons.value[item.id] },
    }

    router.post(route(props.addRoute.name, [...props.addRoute.parameters, item.id]), { quantity_units: quantity * unitsPerCarton(item) }, reloadOptions)
}

const amountOf = (item: BucketItem) => cartonsFor(item) * unitsPerCarton(item) * Number(item.cost ?? 0)
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

            <div v-if="view === 'cards'" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
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
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold tabular-nums text-gray-900">
                                {{ item.cost !== null ? locale.currencyFormat(item.currency ?? orgAgent.currency, item.cost) : "-" }}
                            </span>
                            <span v-if="item.units_per_carton" class="whitespace-nowrap rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs text-gray-500">
                                <b class="font-medium tabular-nums text-gray-700">{{ locale.number(item.units_per_carton) }}</b> {{ ctrans("per carton") }}
                            </span>
                        </div>
                        <div class="grid grid-cols-[auto_1fr] gap-x-3 text-xs leading-5">
                            <span class="text-gray-400">{{ ctrans("Our stock") }}</span>
                            <span v-if="item.our_stock !== null" class="text-right font-medium tabular-nums">{{ locale.number(Math.floor(item.our_stock)) }}</span>
                            <span v-else class="text-right font-medium text-violet-600">{{ ctrans("never stocked") }}</span>
                            <template v-if="item.our_days_of_cover !== null">
                                <span class="text-gray-400">{{ ctrans("Estimated: Would run out in") }}</span>
                                <span class="text-right tabular-nums" :class="coverClass(item.our_days_of_cover)">
                                    {{ item.our_days_of_cover === 0 ? ctrans("now") : `~${item.our_days_of_cover} ${ctrans("days")}` }}
                                </span>
                            </template>
                            <template v-if="item.lead_time_days">
                                <span class="text-gray-400">{{ ctrans("Lead time") }}</span>
                                <span class="text-right tabular-nums" :class="item.lead_time_measured ? 'font-medium' : 'text-gray-400'">
                                    {{ item.lead_time_days }}{{ ctrans("d") }} {{ item.lead_time_measured ? ctrans("measured") : ctrans("estimate") }}
                                </span>
                            </template>
                            <template v-if="item.minimum_carton_order && item.minimum_carton_order > 1">
                                <span class="text-gray-400">{{ ctrans("Smallest order") }}</span>
                                <span class="text-right font-medium tabular-nums">{{ ctrans(":n cartons", { n: item.minimum_carton_order }) }}</span>
                            </template>
                        </div>

                        <div class="mt-auto pt-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <NumberWithButtonSave
                                    :modelValue="cartonsFor(item)"
                                    :bindToTarget="{ min: 0 }"
                                    allowZero
                                    isWithRefreshModel
                                    noUndoButton
                                    noSaveButton
                                    @update:modelValue="(value: number) => setCartons(item, value)"
                                />
                                <button
                                    type="button"
                                    class="cursor-pointer whitespace-nowrap rounded-md border border-dashed px-2 py-1 text-xs font-medium tabular-nums"
                                    :class="item.recommended_quantity ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                    :title="ctrans('Order in whole cartons, click to fill')"
                                    @click="setCartons(item, suggestedCartons(item))"
                                >
                                    {{ locale.number(suggestedCartons(item)) }}
                                    <span class="ml-0.5 font-normal text-gray-400">{{ suggestionLabel(item) }}</span>
                                </button>
                            </div>
                            <div v-if="cartonsFor(item)" class="mt-1 flex items-baseline justify-between text-xs tabular-nums text-gray-500">
                                <span>{{ ctransChoice(":number unit|:number units", cartonsFor(item) * unitsPerCarton(item), { number: locale.number(cartonsFor(item) * unitsPerCarton(item)) }) }}</span>
                                <b v-if="item.cost !== null" class="font-medium text-gray-800">{{ locale.currencyFormat(item.currency ?? orgAgent.currency, amountOf(item)) }}</b>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="!items.data.length" class="col-span-full py-10 text-center text-sm text-gray-400">
                    {{ ctrans("No products in this bucket") }}
                </p>
            </div>

            <Table v-else :resource="items" class="mt-0">
                <template #cell(image)="{ item }">
                    <div class="h-10 w-10 overflow-hidden rounded bg-gray-50">
                        <Image v-if="item.image" :src="item.image" imageCover />
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
                <template #cell(lead_time_days)="{ item }">
                    <span v-if="item.lead_time_days" v-tooltip="item.lead_time_measured ? ctrans('Measured from deliveries') : ctrans('An estimate, not measured yet')" class="tabular-nums" :class="item.lead_time_measured ? 'text-gray-700' : 'text-gray-400'">{{ item.lead_time_days }}{{ ctrans("d") }}</span>
                    <template v-else>—</template>
                </template>
                <template #cell(our_stock)="{ item }">
                    <span v-if="item.our_stock !== null" class="tabular-nums">{{ locale.number(Math.floor(item.our_stock)) }}</span>
                    <span v-else v-tooltip="ctrans('never stocked')" class="text-violet-600">—</span>
                </template>
                <template #cell(our_days_of_cover)="{ item }">
                    <span v-if="item.our_days_of_cover !== null" class="tabular-nums" :class="coverClass(item.our_days_of_cover)">
                        {{ item.our_days_of_cover === 0 ? ctrans("now") : `~${item.our_days_of_cover}${ctrans("d")}` }}
                    </span>
                    <template v-else>—</template>
                </template>
                <template #cell(units_per_carton)="{ item }">
                    <span class="tabular-nums">{{ item.units_per_carton ? locale.number(item.units_per_carton) : "—" }}</span>
                    <span v-if="item.minimum_carton_order && item.minimum_carton_order > 1" v-tooltip="ctrans('Smallest order: :n cartons', { n: item.minimum_carton_order })" class="ml-1 cursor-help text-[10px] text-amber-600">≥{{ item.minimum_carton_order }}</span>
                </template>
                <template #cell(cartons)="{ item }">
                    <div class="flex justify-center">
                        <NumberWithButtonSave
                            :modelValue="cartonsFor(item)"
                            :bindToTarget="{ min: 0 }"
                            allowZero
                            isWithRefreshModel
                            noUndoButton
                            noSaveButton
                            @update:modelValue="(value: number) => setCartons(item, value)"
                        />
                    </div>
                </template>
                <template #cell(suggested)="{ item }">
                    <button
                        type="button"
                        v-tooltip="item.recommended_quantity ? ctrans('Suggested cartons, click to fill') : ctrans('Smallest order, click to fill')"
                        class="cursor-pointer rounded-md border border-dashed px-2 py-0.5 text-xs font-medium tabular-nums"
                        :class="item.recommended_quantity ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-400 hover:bg-gray-50'"
                        @click="setCartons(item, suggestedCartons(item))"
                    >{{ locale.number(suggestedCartons(item)) }}</button>
                </template>
                <template #cell(units)="{ item }">
                    <span class="tabular-nums text-gray-600">{{ cartonsFor(item) ? locale.number(cartonsFor(item) * unitsPerCarton(item)) : "" }}</span>
                </template>
                <template #cell(amount)="{ item }">
                    <span class="font-medium tabular-nums text-gray-800">{{ item.cost !== null && cartonsFor(item) ? locale.currencyFormat(item.currency ?? orgAgent.currency, amountOf(item)) : "" }}</span>
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
