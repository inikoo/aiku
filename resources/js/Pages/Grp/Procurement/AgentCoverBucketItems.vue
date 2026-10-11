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
import NumberWithButtonSave from "@/Components/NumberWithButtonSave.vue"
import PartnerMiniShoppingList from "@/Components/Procurement/PartnerMiniShoppingList.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faThLarge, faList } from "@fal"

type BucketItem = {
    id: number
    slug: string
    code: string
    name: string
    image: object | null
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
    shopping_list_item_id: number | null
    ordered_quantity: number
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    orgAgent: { id: number, slug: string, currency: string }
    addRoute: { name: string, parameters: (string | number)[] }
    bucket: string
    bucketLabel: string
    rank: string | null
    items: { data: BucketItem[], total: number, prev_page_url: string | null, next_page_url: string | null, from: number | null, to: number | null }
    miniCart: InstanceType<typeof PartnerMiniShoppingList>["$props"]["miniCart"]
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

    const organisation = route().params["organisation"]
    const quantityUnits = quantity * unitsPerCarton(item)

    if (item.shopping_list_item_id) {
        if (quantity <= 0) {
            router.delete(route("grp.org.procurement.shopping_list.destroy", [organisation, item.shopping_list_item_id]), reloadOptions)
        } else {
            router.patch(
                route("grp.org.procurement.shopping_list.update", [organisation, item.shopping_list_item_id]),
                { quantity_units: quantityUnits },
                reloadOptions
            )
        }
    } else if (quantity > 0) {
        router.post(route(props.addRoute.name, [...props.addRoute.parameters, item.slug]), { quantity_units: quantityUnits }, reloadOptions)
    }
}

const amountOf = (item: BucketItem) => cartonsFor(item) * unitsPerCarton(item) * Number(item.cost ?? 0)
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-5 flex items-start gap-6">
        <div class="min-w-0 flex-1">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 text-sm text-gray-500">
                <span>
                    <b class="font-semibold text-gray-800">{{ bucketLabel }}</b>
                    <span v-if="rank"> · {{ rank }}</span>
                    — {{ locale.number(items.total) }} {{ ctrans("products") }}
                    <span v-if="items.from" class="ml-2 tabular-nums text-gray-400">{{ items.from }}–{{ items.to }}</span>
                </span>
                <SegmentedToggle v-model="view" :options="viewOptions" :ariaLabel="ctrans('Show products as cards or as a list')" />
            </div>

            <div v-if="view === 'cards'" class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                <div v-for="item in items.data" :key="item.id" class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="aspect-square w-full bg-gray-50">
                        <Image v-if="item.image" :src="item.image" imageCover />
                    </div>
                    <div class="flex flex-1 flex-col gap-1 p-2">
                        <p class="truncate text-sm font-medium text-gray-800" :title="item.name">{{ item.name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ item.code }}
                            <template v-if="item.supplier_code"> · {{ item.supplier_code }}</template>
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
                                    :class="item.recommended_quantity ? 'border-[--app-accent-muted] text-[--app-accent] hover:bg-[--app-accent-soft]' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
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

            <div v-else class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-2">{{ ctrans("Code") }}</th>
                            <th class="px-4 py-2">{{ ctrans("Supplier") }}</th>
                            <th class="px-4 py-2">{{ ctrans("Info") }}</th>
                            <th class="px-4 py-2 text-right">{{ ctrans("Cartons") }}</th>
                            <th class="px-4 py-2 text-right">{{ ctrans("Amount") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in items.data" :key="item.id">
                            <td class="whitespace-nowrap px-4 py-2 align-top font-medium text-gray-700">{{ item.code }}</td>
                            <td class="whitespace-nowrap px-4 py-2 align-top text-xs">
                                <div class="font-medium text-gray-700">{{ item.supplier_code ?? "—" }}</div>
                                <div v-if="item.lead_time_days" class="tabular-nums" :class="item.lead_time_measured ? 'text-gray-500' : 'text-gray-400'">
                                    {{ item.lead_time_days }}{{ ctrans("d") }} {{ item.lead_time_measured ? ctrans("measured") : ctrans("estimate") }}
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-start gap-3">
                                    <div class="h-12 w-12 shrink-0 rounded bg-gray-50">
                                        <Image v-if="item.image" :src="item.image" imageCover />
                                    </div>
                                    <div class="min-w-0 text-xs leading-5">
                                        <div class="truncate text-sm font-medium text-gray-800">{{ item.name }}</div>
                                        <div class="text-gray-500">
                                            {{ ctrans("our stock") }}
                                            <b v-if="item.our_stock !== null" class="font-medium tabular-nums text-gray-700">{{ locale.number(Math.floor(item.our_stock)) }}</b>
                                            <b v-else class="font-medium text-violet-600">{{ ctrans("never stocked") }}</b>
                                            <template v-if="item.our_days_of_cover !== null">
                                                ·
                                                <span :class="coverClass(item.our_days_of_cover)">
                                                    {{ item.our_days_of_cover === 0 ? ctrans("we run out now") : `${ctrans("Estimated: Would run out in")} ~${item.our_days_of_cover} ${ctrans("days")}` }}
                                                </span>
                                            </template>
                                            <template v-if="item.units_per_carton">
                                                · {{ locale.number(item.units_per_carton) }} {{ ctrans("per carton") }}
                                            </template>
                                            <template v-if="item.minimum_carton_order && item.minimum_carton_order > 1">
                                                · <span class="text-gray-600">{{ ctrans("min :n cartons", { n: item.minimum_carton_order }) }}</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center justify-end gap-2">
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
                                        :class="item.recommended_quantity ? 'border-[--app-accent-muted] text-[--app-accent] hover:bg-[--app-accent-soft]' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                        :title="ctrans('Order in whole cartons, click to fill')"
                                        @click="setCartons(item, suggestedCartons(item))"
                                    >
                                        {{ locale.number(suggestedCartons(item)) }}
                                        <span class="ml-0.5 font-normal text-gray-400">{{ suggestionLabel(item) }}</span>
                                    </button>
                                </div>
                                <div v-if="cartonsFor(item)" class="mt-1 text-right text-[10px] tabular-nums text-gray-400">
                                    {{ ctransChoice(":number unit|:number units", cartonsFor(item) * unitsPerCarton(item), { number: locale.number(cartonsFor(item) * unitsPerCarton(item)) }) }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right align-top tabular-nums">
                                <template v-if="item.cost !== null && cartonsFor(item)">
                                    {{ locale.currencyFormat(item.currency ?? orgAgent.currency, amountOf(item)) }}
                                </template>
                                <template v-else>-</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="!items.data.length" class="py-10 text-center text-sm text-gray-400">
                    {{ ctrans("No products in this bucket") }}
                </p>
            </div>

            <div v-if="items.prev_page_url || items.next_page_url" class="mt-3 flex justify-between text-sm">
                <Link v-if="items.prev_page_url" :href="items.prev_page_url" preserve-scroll class="secondaryLink">{{ ctrans("Previous") }}</Link>
                <span v-else />
                <Link v-if="items.next_page_url" :href="items.next_page_url" preserve-scroll class="secondaryLink">{{ ctrans("Next") }}</Link>
            </div>
        </div>

        <aside class="sticky top-4 hidden w-80 shrink-0 lg:block xl:w-96">
            <PartnerMiniShoppingList :miniCart="miniCart" />
        </aside>
    </div>
</template>
