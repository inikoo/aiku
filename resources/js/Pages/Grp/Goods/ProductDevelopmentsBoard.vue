<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-15m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"
import ProductDevelopmentViewSwitch from "@/Components/Goods/ProductDevelopment/ProductDevelopmentViewSwitch.vue"
import ProductDevelopmentFilterBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentFilterBar.vue"
import ProductDevelopmentMissingBadge from "@/Components/Goods/ProductDevelopment/ProductDevelopmentMissingBadge.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faList, faColumns, faClock } from "@fal"
library.add(faLightbulbOn, faList, faColumns, faClock)

interface Card {
    slug: string
    code: string
    name: string
    family: string | null
    brand: string | null
    supplier: string | null
    agent: string | null
    owner: string | null
    number_items: number
    number_required_missing: number
    target_online_date: string | null
    days_in_stage: number | null
    is_overdue: boolean
}

interface Column {
    position: number
    label: string
    cards: Card[]
}

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    viewSwitch: any[]
    columns: Column[]
    filterBar: any
    truncated: boolean
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <ProductDevelopmentViewSwitch :options="viewSwitch" />
        </template>
    </PageHeading>

    <ProductDevelopmentFilterBar :filterBar="filterBar" routeName="grp.product_developments.board" />

    <p v-if="truncated" class="px-4 pt-2 text-xs text-amber-700">
        {{ ctrans("Showing the first 500 developments, use the filters to narrow down") }}
    </p>

    <div class="flex gap-3 overflow-x-auto p-4">
        <section v-for="column in columns" :key="column.position" class="flex w-72 shrink-0 flex-col rounded-md bg-gray-50 ring-1 ring-gray-200">
            <header class="flex items-center justify-between border-b border-gray-200 px-3 py-2 text-sm font-medium text-gray-700">
                <span>{{ column.position <= 7 ? column.position + ". " : "" }}{{ column.label }}</span>
                <span class="tabular-nums text-gray-400">{{ column.cards.length }}</span>
            </header>

            <div class="flex flex-col gap-2 p-2">
                <Link
                    v-for="card in column.cards"
                    :key="card.slug"
                    :href="route('grp.product_developments.show', { productDevelopment: card.slug })"
                    class="block rounded-md bg-white p-3 text-sm shadow-sm ring-1 ring-gray-200 hover:ring-[--app-accent-muted]"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-medium text-gray-900">{{ card.name }}</span>
                        <span class="shrink-0 text-xs text-gray-400">{{ card.code }}</span>
                    </div>
                    <div v-if="card.brand || card.family" class="mt-0.5 text-xs text-gray-500">
                        {{ [card.brand, card.family].filter(Boolean).join(" · ") }}
                    </div>
                    <div v-if="card.supplier || card.agent" class="text-xs text-gray-500">
                        {{ [card.supplier, card.agent].filter(Boolean).join(" · ") }}
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <ProductDevelopmentMissingBadge :count="card.number_required_missing" />
                        <span class="flex items-center gap-1 text-xs tabular-nums" :class="card.is_overdue ? 'text-red-600' : 'text-gray-500'">
                            <FontAwesomeIcon icon="fal fa-clock" fixed-width aria-hidden="true" />
                            <span v-if="card.days_in_stage !== null">{{ ctrans(":days d", { days: String(card.days_in_stage) }) }}</span>
                            <span v-if="card.target_online_date">· {{ formatDate(card.target_online_date) }}</span>
                        </span>
                    </div>
                    <div v-if="card.owner" class="mt-1 text-xs text-gray-400">{{ card.owner }}</div>
                </Link>

                <p v-if="!column.cards.length" class="px-1 py-4 text-center text-xs text-gray-400">
                    {{ ctrans("Nothing here") }}
                </p>
            </div>
        </section>
    </div>
</template>
