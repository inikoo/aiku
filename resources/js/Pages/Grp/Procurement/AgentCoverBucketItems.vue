<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 31 Aug 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { ref, watch } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { Checkbox, IconField, InputIcon, InputText, Select } from "primevue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faThLarge, faList, faSearch } from "@fal"
import AgentOrderingItems from "@/Components/Procurement/AgentOrderingItems.vue"

type BucketItem = { id: number }

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

const askedView = new URLSearchParams(window.location.search).get("view")

const view = ref<ItemsView>(askedView === "list" || askedView === "cards" ? askedView : readStoredView())
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
}, { immediate: true })


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

            <AgentOrderingItems :items="items" :orgAgent="orgAgent" :addRoute="addRoute" :bucket="bucket" :bucketLabels="bucketLabels" :view="view" :reloadOnly="['items', 'miniCart', 'filterOptions']" />

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
